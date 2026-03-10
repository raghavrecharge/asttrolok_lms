<?php

namespace App\Http\Controllers\Admin\traits;

use Illuminate\Support\Facades\Log;
use Exception;

use App\Exports\InstallmentPurchasesExport;
use App\Models\InstallmentOrder;
use App\Models\InstallmentOrderPayment;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

trait InstallmentPurchasesTrait
{
    public function purchases()
    {
        try {
            $this->authorize('admin_installments_purchases');

            $orders = InstallmentOrder::query()
                ->where('status', '!=', 'paying')
                ->orderBy('created_at', 'desc')
                ->with([
                'installment' => function ($query) {
                $query->with(['steps']);
                $query->withCount([
                        'steps'
                    ]);
            }
            ])
                ->paginate(10);

            $orders = $this->handlePurchasedOrders($orders);

            $data = [
                'pageTitle' => trans('update.purchases'),
                'orders' => $orders
            ];

            return view('admin.financial.installments.purchases.index', $data);
        }
        catch (\Exception $e) {
            \Log::error('purchases error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            throw $e;
        }
    }

    private function handlePurchasedOrders($orders)
    {
        foreach ($orders as $order) {
            $overdueOrderInstallments = $this->getOverdueOrderInstallments($order);
            $getUpcomingInstallment = $this->getUpcomingInstallment($order);

            $order->overdue_count = $overdueOrderInstallments['total'];
            $order->overdue_amount = $overdueOrderInstallments['amount'];
            $order->upcoming_date = !empty($getUpcomingInstallment) ? dateTimeFormat((($getUpcomingInstallment->deadline * 86400) + $order->created_at), 'j M Y') : '';

            $lastStep = $order->installment->steps()->orderBy('deadline', 'desc')->first();

            $order->days_left = 0;

            if (!empty($lastStep)) {
                $dueAt = (($lastStep->deadline * 86400) + $order->created_at);
                $daysLeft = ($dueAt - time()) / 86400;

                if ($daysLeft > 0) {
                    $order->days_left = (int)$daysLeft;
                }
            }
        }

        return $orders;
    }

    private function getOverdueOrderInstallments($order)
    {
        $total = 0;
        $amount = 0;

        $time = time();
        $itemPrice = $order->getItemPrice();

        foreach ($order->installment->steps as $step) {
            $dueAt = ($step->deadline * 86400) + $order->created_at;

            if ($dueAt < $time) {
                $payment = InstallmentOrderPayment::query()
                    ->where('installment_order_id', $order->id)
                    ->where('step_id', $step->id)
                    ->where('status', 'paid')
                    ->first();

                if (empty($payment)) {
                    $total += 1;
                    $amount += $step->getPrice($itemPrice);
                }
            }
        }

        return [
            'total' => $total,
            'amount' => $amount,
        ];
    }

    private function getUpcomingInstallment($order)
    {
        $result = null;
        $deadline = 0;

        foreach ($order->installment->steps as $step) {
            $payment = InstallmentOrderPayment::query()
                ->where('installment_order_id', $order->id)
                ->where('step_id', $step->id)
                ->where('status', 'paid')
                ->first();

            if (empty($payment) and ($deadline == 0 or $deadline > $step->deadline)) {
                $deadline = $step->deadline;
                $result = $step;
            }
        }

        return $result;
    }

    public function purchasesExportExcel(Request $request)
    {
        try {
            $this->authorize('admin_installments_purchases');

            $orders = InstallmentOrder::query()
                ->where('status', '!=', 'paying')
                ->orderBy('created_at', 'desc')
                ->with([
                'installment' => function ($query) {
                $query->with(['steps']);
                $query->withCount([
                        'steps'
                    ]);
            }
            ])
                ->get();

            $orders = $this->handlePurchasedOrders($orders);

            $export = new InstallmentPurchasesExport($orders);
            return $this->dispatchBackgroundExport($export, 'installment_purchases_' . date('Y-m-d_H-i-s') . '.xlsx', 'Installment Purchases Export');
        }
        catch (\Exception $e) {
            \Log::error('purchasesExportExcel error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            throw $e;
        }
    }

    public function storePurchase(Request $request)
    {
        try {
            $this->authorize('admin_installments_purchases');

            $data = $request->all();

            $user_id = $data['user_id'] ?? null;
            $webinar_id = $data['webinar_id'] ?? null;
            $upfront = $data['upfront'] ?? 0;
            $count = $data['count'] ?? 1;
            $interval = $data['interval'] ?? 30;
            $notes = $data['notes'] ?? '';

            if (!$user_id || !$webinar_id) {
                $toastData = [
                    'title' => 'Error',
                    'msg' => 'Student and Course are required.',
                    'status' => 'error'
                ];
                return back()->with(['toast' => $toastData]);
            }

            $webinar = \App\Models\Webinar::find($webinar_id);
            if (!$webinar) {
                $toastData = [
                    'title' => 'Error',
                    'msg' => 'Course not found.',
                    'status' => 'error'
                ];
                return back()->with(['toast' => $toastData]);
            }

            $totalPrice = $webinar->price;

            // Create a custom installment plan for this purchase
            $installment = \App\Models\Installment::create([
                'target_type' => 'specific_courses',
                'target' => 'specific_courses',
                'capacity' => 1,
                'upfront' => $upfront,
                'upfront_type' => 'fixed_amount',
                'enable' => 1,
                'created_at' => time(),
            ]);

            // Add translations
            \App\Models\Translation\InstallmentTranslation::create([
                'installment_id' => $installment->id,
                'locale' => 'en',
                'title' => 'Custom Plan - ' . $webinar->title,
                'main_title' => 'Custom Plan - ' . $webinar->title,
                'description' => $notes ?: 'Custom installment plan created by admin.',
            ]);

            // Add specification mapping
            \App\Models\InstallmentSpecificationItem::create([
                'installment_id' => $installment->id,
                'webinar_id' => $webinar->id,
            ]);

            // Calculate remaining
            $remainingPrice = max(0, $totalPrice - $upfront);
            $stepAmount = $count > 0 ? ($remainingPrice / $count) : 0;

            // Create steps
            for ($i = 1; $i <= $count; $i++) {
                $step = \App\Models\InstallmentStep::create([
                    'installment_id' => $installment->id,
                    'deadline' => $i * $interval,
                    'amount' => $stepAmount,
                    'amount_type' => 'fixed_amount',
                    'order' => $i,
                ]);

                \App\Models\Translation\InstallmentStepTranslation::create([
                    'installment_step_id' => $step->id,
                    'locale' => 'en',
                    'title' => 'Installment ' . $i,
                ]);
            }

            // Create the order
            $order = \App\Models\InstallmentOrder::create([
                'installment_id' => $installment->id,
                'user_id' => $user_id,
                'webinar_id' => $webinar->id,
                'status' => 'open',
                'created_at' => time(),
            ]);

            // Apply upfront
            if ($upfront > 0) {
                \App\Models\InstallmentOrderPayment::create([
                    'installment_order_id' => $order->id,
                    'type' => 'upfront',
                    'amount' => $upfront,
                    'status' => 'paid',
                    'created_at' => time(),
                ]);

                // Also add an accounting log
                \App\Models\Accounting::create([
                    'user_id' => $user_id,
                    'creator_id' => auth()->user()->id,
                    'amount' => $upfront,
                    'type' => \App\Models\Accounting::$addiction,
                    'type_account' => \App\Models\Accounting::$asset,
                    'store_type' => \App\Models\Accounting::$storeManual,
                    'description' => 'Manual Down Payment for plan #' . $order->id,
                    'created_at' => time(),
                ]);
            }

            $toastData = [
                'title' => trans('public.request_success'),
                'msg' => 'Custom Installment Plan created successfully.',
                'status' => 'success'
            ];

            return back()->with(['toast' => $toastData]);
        }
        catch (\Exception $e) {
            \Log::error('storePurchase error: ' . $e->getMessage());
            $toastData = [
                'title' => 'Error',
                'msg' => 'Failed to create plan: ' . $e->getMessage(),
                'status' => 'error'
            ];
            return back()->with(['toast' => $toastData]);
        }
    }
}
