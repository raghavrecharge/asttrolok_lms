<?php

namespace App\Http\Controllers\Api\Panel;

use Illuminate\Support\Facades\Log;
use Exception;

use App\Http\Controllers\Api\Controller;
use App\Http\Controllers\Api\UploadFileManager;
use App\Models\OfflinePayment;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class OfflinePayments extends Controller
{
    public function index()
    {
        try {
            $user = apiAuth();
            $offlinePayments = OfflinePayment::where('user_id', $user->id)->orderBy('created_at', 'desc')->get()
                ->map(function ($offlinePayment) {
                    return [
                        'id' => $offlinePayment->id,
                        'amount' => $offlinePayment->amount,
                        'bank' => $offlinePayment->bank,
                        'reference_number' => $offlinePayment->reference_number,
                        'status' => $offlinePayment->status,
                        'created_at' => $offlinePayment->created_at,
                        'pay_date' => $offlinePayment->pay_date,
                        'attachment' => $offlinePayment->getAttachmentPath(),
                    ];

                });
            return apiResponse2(1, 'retrieved', trans('api.public.retrieved'), $offlinePayments);
        } catch (\Exception $e) {
            \Log::error('index error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $user = apiAuth();
            $offline = OfflinePayment::where('id', $id)
                ->where('user_id', $user->id)
                ->where('status', '!=', 'approved')
                ->first();

            if (!$offline) {
                abort(404);
            }

            $data = $request->all();

            validateParam($request->all(), [
                'amount' => 'required|integer',
                'account' => ['required',
                    Rule::in(getOfflineBanksTitle())
                ],
                'referral_code' => 'required',
                'date' => 'required|date',

            ]);
            $attach = $this->handleUploadAttachment();

            $offline->update([
                'amount' => $data['amount'],
                'bank' => $data['account'],
                'reference_number' => $data['referral_code'],
                'status' => OfflinePayment::$waiting,
                'pay_date' => $data['date'],
                'attachment' => $attach
            ]);

            return apiResponse2(1, 'updated', trans('api.public.updated'));
        } catch (\Exception $e) {
            \Log::error('update error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function destroy($id)
    {
        try {
            $user = apiAuth();
            $offline = OfflinePayment::where('id', $id)
                ->where('user_id', $user->id)
                ->where('status', '!=', 'approved')
                ->first();

            if (!$offline) {
                abort(404);
            }
            $offline->delete();
            return apiResponse2(1, 'deleted', trans('api.public.deleted'));
        } catch (\Exception $e) {
            \Log::error('destroy error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function store(Request $request)
    {
        try {
            $array = [];
            foreach (getOfflineBankSettings() as $account) {
                if (isset($account['title'])) {
                    $array[] = $account['title'];
                }

            }

            validateParam($request->all(), [
                'amount' => 'required|numeric',
                'bank' => ['required', Rule::in($array)],
                'reference_number' => 'required',
                'pay_date' => 'required',
            ]);

            $amount = $request->input('amount');
            $account = $request->input('bank');
            $referenceNumber = $request->input('reference_number');
            $date = $request->input('pay_date');

            $attach = $this->handleUploadAttachment();

            $userAuth = apiAuth();

            OfflinePayment::create([
                'user_id' => $userAuth->id,
                'amount' => $amount,
                'bank' => $account,
                'reference_number' => $referenceNumber,
                'status' => OfflinePayment::$waiting,
                'attachment' => $attach,
                'pay_date' => $date,
                'created_at' => time(),
            ]);

            $notifyOptions = [
                '[amount]' => handlePrice($amount),
            ];
            sendNotification('offline_payment_request', $notifyOptions, $userAuth->id);
            return apiResponse2(1, 'stored',
                trans('financial.offline_payment_request_success_store')

            );
        } catch (\Exception $e) {
            \Log::error('store error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    private function handleUploadAttachment()
    {
        $request = request();
        $attach = null;
        if ($request->file('attachment', null)) {
            $storage = new UploadFileManager($request->file('attachment'), 'offlinePayments');
            $attach = $storage->storage_path;
        }
        return $attach;

    }

}
