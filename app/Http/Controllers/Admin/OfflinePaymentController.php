<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Support\Facades\Log;
use Exception;

use App\Exports\OfflinePaymentsExport;
use App\Http\Controllers\Controller;
use App\Models\Accounting;
use App\Models\OfflineBank;
use App\Models\OfflinePayment;
use App\Models\Reward;
use App\Models\RewardAccounting;
use App\Models\Role;
use App\User;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Traits\BackgroundExportTrait;

class OfflinePaymentController extends Controller
{
    use BackgroundExportTrait;
    public function index(Request $request)
    {
        try {
            $this->authorize('admin_offline_payments_list');

            $pageType = $request->get('page_type', 'requests');

            $query = OfflinePayment::query();
            if ($pageType == 'requests') {
                $query->where('status', OfflinePayment::$waiting);
            } else {
                $query->where('status', '!=', OfflinePayment::$waiting);
            }

            $query = $this->filters($query, $request);

            $offlinePayments = $query->paginate(10);

            $offlinePayments->appends([
                'page_type' => $pageType
            ]);

            $roles = Role::all();

            $offlineBanks = OfflineBank::query()
                ->orderBy('created_at', 'desc')
                ->with([
                    'specifications'
                ])
                ->get();

            $data = [
                'pageTitle' => trans('admin/main.offline_payments_title') . (($pageType == 'requests') ? 'Requests' : 'History'),
                'offlinePayments' => $offlinePayments,
                'pageType' => $pageType,
                'roles' => $roles,
                'offlineBanks' => $offlineBanks,
            ];

            $user_ids = $request->get('user_ids', []);

            if (!empty($user_ids)) {
                $data['users'] = User::select('id', 'full_name')
                    ->whereIn('id', $user_ids)->get();
            }

            return view('admin.financial.offline_payments.lists', $data);
        } catch (\Exception $e) {
            \Log::error('index error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    private function filters($query, $request)
    {
        $from = $request->get('from', null);
        $to = $request->get('to', null);
        $search = $request->get('search', null);
        $user_ids = $request->get('user_ids', []);
        $role_id = $request->get('role_id', null);
        $account_type = $request->get('account_type', null);
        $sort = $request->get('sort', null);
        $status = $request->get('status', null);

        if (!empty($search)) {
            $ids = User::where('full_name', 'like', "%$search%")->pluck('id')->toArray();
            $user_ids = array_merge($user_ids, $ids);
        }

        if (!empty($role_id)) {
            $role = Role::where('id', $role_id)->first();

            if (!empty($role)) {
                $ids = $role->users()->pluck('id')->toArray();
                $user_ids = array_merge($user_ids, $ids);
            }
        }

        $query = fromAndToDateFilter($from, $to, $query, 'created_at');

        if (!empty($user_ids) and count($user_ids)) {
            $query->whereIn('user_id', $user_ids);
        }

        if (!empty($account_type)) {
            $query->where('offline_bank_id', $account_type);
        }

        if (!empty($status)) {
            $query->where('status', $status);
        }

        if (!empty($sort)) {
            switch ($sort) {
                case 'amount_asc':
                    $query->orderBy('amount', 'asc');
                    break;
                case 'amount_desc':
                    $query->orderBy('amount', 'desc');
                    break;
                case 'pay_date_asc':
                    $query->orderBy('pay_date', 'asc');
                    break;
                case 'pay_date_desc':
                    $query->orderBy('pay_date', 'desc');
                    break;
            }
        } else {
            $query->orderBy('created_at', 'desc');
        }

        return $query;
    }

    public function reject($id)
    {
        try {
            $this->authorize('admin_offline_payments_reject');

            $offlinePayment = OfflinePayment::findOrFail($id);
            $offlinePayment->update(['status' => OfflinePayment::$reject]);

            $notifyOptions = [
                '[amount]' => handlePrice($offlinePayment->amount),
            ];
            sendNotification('offline_payment_rejected', $notifyOptions, $offlinePayment->user_id);

            return back();
        } catch (\Exception $e) {
            \Log::error('reject error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    /**
     * V-11 NOTE: This method handles WALLET TOP-UP approvals only.
     * For course-access offline payments, use the support ticket workflow
     * via AdminSupportController@updateStatusSecure (scenario: offline_cash_payment).
     * Do NOT use this method to directly grant course access.
     */
    public function approved($id)
    {
        try {
            $this->authorize('admin_offline_payments_approved');

            $offlinePayment = OfflinePayment::where('id', $id)->lockForUpdate()->firstOrFail();

            if ($offlinePayment->status !== OfflinePayment::$waiting) {
                return back()->withErrors(['error' => 'This payment has already been processed and cannot be approved again.']);
            }

            Accounting::create([
                'creator_id' => auth()->user()->id,
                'user_id' => $offlinePayment->user_id,
                'amount' => $offlinePayment->amount,
                'type' => Accounting::$addiction,
                'type_account' => Accounting::$asset,
                'description' => trans('admin/pages/setting.notification_offline_payment_approved'),
                'created_at' => time(),
            ]);

            $offlinePayment->update(['status' => OfflinePayment::$approved]);

            $notifyOptions = [
                '[amount]' => handlePrice($offlinePayment->amount),
            ];
            sendNotification('offline_payment_approved', $notifyOptions, $offlinePayment->user_id);

            $accountChargeReward = RewardAccounting::calculateScore(Reward::ACCOUNT_CHARGE, $offlinePayment->amount);
            RewardAccounting::makeRewardAccounting($offlinePayment->user_id, $accountChargeReward, Reward::ACCOUNT_CHARGE);

            $chargeWalletReward = RewardAccounting::calculateScore(Reward::CHARGE_WALLET, $offlinePayment->amount);
            RewardAccounting::makeRewardAccounting($offlinePayment->user_id, $chargeWalletReward, Reward::CHARGE_WALLET);

            return back();
        } catch (\Exception $e) {
            \Log::error('approved error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function exportExcel(Request $request)
    {
        try {
            $pageType = $request->get('page_type', 'requests');

            $query = OfflinePayment::query();
            if ($pageType == 'requests') {
                $query->where('status', OfflinePayment::$waiting);
            } else {
                $query->where('status', '!=', OfflinePayment::$waiting);
            }

            $query = $this->filters($query, $request);

            $offlinePayments = $query->get();

            $export = new OfflinePaymentsExport($offlinePayments);

            return $this->dispatchBackgroundExport($export, 'offline_payment_' . $pageType . '_' . date('Y-m-d_H-i-s') . '.xlsx', 'Offline Payments Export (' . $pageType . ')');
        } catch (\Exception $e) {
            \Log::error('exportExcel error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }
}
