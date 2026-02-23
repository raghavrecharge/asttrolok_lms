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

        // Get available installment plans
        $installments = Installment::where('enable', true)
            ->where(function ($q) {
                $q->where('capacity', '>', 0)->orWhereNull('capacity');
            })
            ->get();

        // Check if user already has a purchase
        $existingSale = null;
        $existingPlan = null;
        $totalPaid = 0;
        $remaining = $coursePrice;

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
                        $totalPaid = $existingPlan->schedules->sum('amount_paid');
                        $remaining = max(0, $existingPlan->total_amount - $totalPaid);
                    }
                }
            }
        }

        $pageTitle = 'Quick Pay - ' . $webinar->title;

        return view(getTemplate() . '.quick_pay.index', compact(
            'webinar', 'coursePrice', 'installments',
            'existingSale', 'existingPlan', 'totalPaid', 'remaining',
            'pageTitle'
        ));
    }
}
