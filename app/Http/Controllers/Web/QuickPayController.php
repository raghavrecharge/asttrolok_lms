<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Webinar;
use App\Models\Installment;
use App\Models\PaymentEngine\UpeProduct;
use App\Models\PaymentEngine\UpeSale;
use App\Models\PaymentEngine\UpeInstallmentPlan;
use App\Services\PaymentEngine\PaymentLedgerService;
use Illuminate\Http\Request;

class QuickPayController extends Controller
{
    public function show(string $slug)
    {
        $webinar = Webinar::where('slug', $slug)->where('status', 'active')->firstOrFail();
        $coursePrice = $webinar->getPrice() ?? $webinar->price;

        $existingSale = null;
        $existingPlan = null;
        $totalPaid = 0;
        $remaining = $coursePrice;
        $installmentConfig = null;
        $installmentBreakdown = null;

        $user = auth()->user();
        if ($user) {
            $upeProduct = UpeProduct::where('external_id', $webinar->id)
                ->whereIn('product_type', ['course_video', 'webinar'])
                ->first();

            if ($upeProduct) {
                $existingSale = UpeSale::where('user_id', $user->id)
                    ->where('product_id', $upeProduct->id)
                    ->where('pricing_mode', 'installment')
                    ->whereIn('status', ['active', 'pending_payment', 'partially_refunded'])
                    ->first();

                if ($existingSale) {
                    $existingPlan = UpeInstallmentPlan::with('schedules')
                        ->where('sale_id', $existingSale->id)
                        ->first();

                    if ($existingPlan) {
                        $totalPaid = $existingPlan->schedules->where('status', '!=', 'waived')->sum('amount_paid');
                        $remaining = max(0, $existingPlan->total_amount - $totalPaid);
                    }
                }
            }
        }

        // If no existing plan, check if the course has an installment config
        if (!$existingPlan) {
            $installmentId = \DB::table('installment_specification_items')
                ->where('webinar_id', $webinar->id)
                ->value('installment_id');

            if ($installmentId) {
                $installmentConfig = \App\Models\Installment::where('id', $installmentId)
                    ->where('enable', true)
                    ->first();
            }

            if ($installmentConfig) {
                $breakdown = [];
                $upfrontAmount = round($coursePrice * ($installmentConfig->upfront / 100), 2);
                $breakdown[] = [
                    'label' => 'Upfront Payment',
                    'amount' => $upfrontAmount,
                    'deadline_days' => 0,
                ];

                $steps = $installmentConfig->steps()->orderBy('id')->get();
                $cumulativeDays = 0;
                foreach ($steps as $i => $step) {
                    $stepAmount = round($step->getPrice($coursePrice), 2);
                    $cumulativeDays += (int) $step->deadline;
                    $breakdown[] = [
                        'label' => 'Installment ' . ($i + 2),
                        'amount' => $stepAmount,
                        'deadline_days' => $cumulativeDays,
                    ];
                }

                $installmentBreakdown = $breakdown;
                $remaining = array_sum(array_column($breakdown, 'amount'));
            }
        }

        $pageTitle = 'Quick Pay - ' . $webinar->title;

        return view(getTemplate() . '.quick_pay.index', compact(
            'webinar', 'coursePrice',
            'existingSale', 'existingPlan', 'totalPaid', 'remaining',
            'installmentConfig', 'installmentBreakdown',
            'pageTitle'
        ));
    }
}
