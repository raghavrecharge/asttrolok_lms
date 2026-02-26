<?php

namespace App\Http\Controllers\Panel;

use Illuminate\Support\Facades\Log;
use Exception;

use App\Http\Controllers\Controller;
use App\Mixins\Installment\InstallmentAccounting;
use App\Models\Cart;
use App\Models\WebinarPartPayment;
use App\Models\Sale;
use App\Models\Webinar;
use App\Models\InstallmentOrder;
use App\Models\InstallmentOrderPayment;
use App\Models\InstallmentStep;
use App\Models\SubStepInstallment;
use App\Models\PaymentEngine\UpeSale;
use App\Models\PaymentEngine\UpeInstallmentPlan;
use App\Models\PaymentEngine\UpePaymentRequest;
use App\Services\PaymentEngine\PaymentLedgerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InstallmentsController extends Controller
{

    public function index(PaymentLedgerService $ledger)
    {
        try {
            $user = auth()->user();

            // UPE logic for installments
            $sales = UpeSale::where('user_id', $user->id)
                ->where('pricing_mode', 'installment')
                ->with(['product', 'installmentPlan.schedules'])
                ->orderByDesc('id')
                ->paginate(10);

            // Fetch items for paginated sales
            $webinarIds = $sales->where('product.product_type', '!=', 'bundle')->pluck('product.external_id');
            $bundleIds = $sales->where('product.product_type', 'bundle')->pluck('product.external_id');
            
            $webinars = \App\Models\Webinar::whereIn('id', $webinarIds)->with(['teacher', 'category'])->get()->keyBy('id');
            $bundles = \App\Models\Bundle::whereIn('id', $bundleIds)->with(['teacher', 'category'])->get()->keyBy('id');

            foreach ($sales as $sale) {
                if ($sale->product->product_type === 'bundle') {
                    $sale->item = $bundles->get($sale->product->external_id);
                } else {
                    $sale->item = $webinars->get($sale->product->external_id);
                }

                // Helper stats for each sale/plan
                if ($sale->installmentPlan) {
                    $plan = $sale->installmentPlan;
                    $sale->remained_installments_count = $plan->schedules->whereIn('status', ['due', 'partial', 'overdue', 'upcoming'])->count();
                    $sale->remained_installments_amount = $plan->totalRemaining();
                    $sale->upcoming_installment = $plan->nextDueSchedule();
                    $sale->has_overdue = $plan->schedules->where('status', 'overdue')->isNotEmpty();
                    $sale->overdue_count = $plan->schedules->where('status', 'overdue')->count();
                    $sale->overdue_amount = $plan->schedules->where('status', 'overdue')->sum('amount_due') - $plan->schedules->where('status', 'overdue')->sum('amount_paid');
                }
            }

            // Summary statistics
            $openInstallmentsCount = UpeInstallmentPlan::where('status', 'active')
                ->whereHas('sale', function($q) use ($user) {
                    $q->where('user_id', $user->id);
                })->count();

            $pendingVerificationCount = UpePaymentRequest::where('user_id', $user->id)
                ->whereIn('status', ['pending', 'verified', 'approved'])
                ->count();

            $finishedInstallmentsCount = UpeInstallmentPlan::where('status', 'completed')
                ->whereHas('sale', function($q) use ($user) {
                    $q->where('user_id', $user->id);
                })->count();

            $overdueInstallmentsCount = UpeInstallmentPlan::where('status', 'active')
                ->whereHas('sale', function($q) use ($user) {
                    $q->where('user_id', $user->id);
                })
                ->whereHas('schedules', function($q) {
                    $q->where('status', 'overdue');
                })->count();

            $data = [
                'pageTitle' => trans('update.installments'),
                'openInstallmentsCount' => $openInstallmentsCount,
                'pendingVerificationCount' => $pendingVerificationCount,
                'finishedInstallmentsCount' => $finishedInstallmentsCount,
                'overdueInstallmentsCount' => $overdueInstallmentsCount,
                'orders' => $sales, // Passing $sales as $orders for template compatibility
                'ledger' => $ledger,
            ];

            return view('web.default.panel.financial.installments.lists', $data);
        } catch (\Exception $e) {
            \Log::error('index error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    private function getRemainedInstallments($order)
    {
        $total = 0;
        $amount = 0;

        $itemPrice = $order->getItemPrice();

        

        foreach ($order->installment->steps as $step) {
        
            $payment = InstallmentOrderPayment::query()
                ->where('installment_order_id', $order->id)
                ->where('step_id', $step->id)
                ->where('status', 'paid')
                // ->whereHas('sale', function ($query) {
                //     $query->whereNull('refund_at');
                // })
                ->first();

            if (empty($payment)) {
                $total += 1;
                $amount += $step->getPrice($itemPrice);
            }
        }

        return [
            'total' => $total,
            'amount' => $amount,
        ];
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

    private function getOverdueInstallments($user)
    {
        $orders = InstallmentOrder::query()
            ->where('user_id', $user->id)
            ->where('installment_orders.status', 'open')
            ->get();

        $count = 0;

        foreach ($orders as $order) {
            if ($order->checkOrderHasOverdue()) {
                $count += 1;
            }
        }

        return $count;
    }

    private function getFinishedInstallments($user)
    {
        $orders = InstallmentOrder::query()
            ->where('user_id', $user->id)
            ->where('installment_orders.status', 'open')
            ->get();

        $count = 0;

        foreach ($orders as $order) {
            $steps = $order->installment->steps;
            $paidAllSteps = true;

            foreach ($steps as $step) {
                $payment = InstallmentOrderPayment::query()
                    ->where('installment_order_id', $order->id)
                    ->where('step_id', $step->id)
                    ->where('status', 'paid')
                    ->whereHas('sale', function ($query) {
                        $query->whereNull('refund_at');
                    })
                    ->first();

                if (empty($payment)) {
                    $paidAllSteps = false;
                }
            }

            if ($paidAllSteps) {
                $count += 1;
            }
        }

        return $count;
    }

    public function show($orderId)
    {
        try {
            $user = auth()->user();

            $order = InstallmentOrder::query()
                ->where('id', $orderId)
                ->where('user_id', $user->id)
                ->with([
                    'installment' => function ($query) {
                        $query->with([
                            'steps' => function ($query) {
                                $query->orderBy('deadline', 'asc');
                            }
                        ]);
                    }
                ])
                ->first();

                
                $webinar= Webinar::where('id',$order->webinar_id)->first();
                $webinar_title= $webinar->slug;

                $WebinarPartPayment = WebinarPartPayment::select('user_id', 'webinar_id', 'installment_id', DB::raw('sum(amount) as total_amount'))
            ->where('user_id',$user->id)
            ->where('webinar_id',$order->webinar_id)
            ->groupBy('user_id', 'webinar_id')
            ->first();
                        
            $orderPayments = InstallmentOrderPayment::where('installment_order_id', $order->id)
                ->get();

            //   echo "<pre>";
            //   print_r( $orderPayments);die;

                    $totalSaleAmount=0;

                    foreach($orderPayments as $orderPayment){
                        $saleId = $orderPayment->sale_id;
                        if($saleId){
                        $sale = Sale::where(['id' => $saleId ,
                        'status' => null,])
                        ->first();
                        if($sale)
                    $totalSaleAmount+=$sale->total_amount;
                        }

                    }
                  $paidAmount = $totalSaleAmount  + (isset($WebinarPartPayment)?$WebinarPartPayment->total_amount:0);

                // LMS-037 FIX: Also include UPE ledger balance for this course
                // UPE ledger may have credits not reflected in legacy Sale.total_amount
                try {
                    $upeProduct = \App\Models\PaymentEngine\UpeProduct::where('external_id', $order->webinar_id)
                        ->where('external_type', 'webinar')
                        ->first();
                    if ($upeProduct) {
                        $upeSale = \App\Models\PaymentEngine\UpeSale::where('user_id', $user->id)
                            ->where('product_id', $upeProduct->id)
                            ->whereIn('status', ['active', 'pending_payment', 'completed'])
                            ->first();
                        if ($upeSale) {
                            $ledgerBalance = app(\App\Services\PaymentEngine\PaymentLedgerService::class)->balance($upeSale->id);
                            // Use the higher of legacy vs UPE amount to avoid double-counting
                            $paidAmount = max($paidAmount, (int) $ledgerBalance);
                        }
                    }
                } catch (\Exception $e) {
                    \Log::warning('LMS-037: Could not fetch UPE ledger balance for installment reconciliation', [
                        'order_id' => $order->id,
                        'error' => $e->getMessage(),
                    ]);
                }

                // LMS-037 FIX: Reconcile ALL payment statuses from actual paid amount
                $itemPrice = $order->getItemPrice();
                $runningPaid = $paidAmount;

                // Reconcile upfront payment
                $upfrontPayment = $orderPayments->where('type', 'upfront')->first();
                if ($upfrontPayment && $order->installment) {
                    $upfrontAmount = $order->installment->getUpfront($itemPrice);
                    if ($upfrontPayment->status !== 'paid' && $runningPaid >= $upfrontAmount && $upfrontAmount > 0) {
                        $upfrontPayment->update(['status' => 'paid']);
                    }
                    if ($upfrontPayment->status === 'paid' || $runningPaid >= $upfrontAmount) {
                        $runningPaid -= $upfrontAmount;
                    }
                }

                // Reconcile step payments sequentially
                if ($order->installment && $order->installment->steps) {
                    foreach ($order->installment->steps as $step) {
                        $stepPaymentRecord = $orderPayments->where('step_id', $step->id)->first();
                        $stepPrice = $step->getPrice($itemPrice);
                        if ($stepPaymentRecord && $stepPaymentRecord->status !== 'paid' && $runningPaid >= $stepPrice && $stepPrice > 0) {
                            $stepPaymentRecord->update(['status' => 'paid']);
                            $runningPaid -= $stepPrice;
                        } elseif ($stepPaymentRecord && $stepPaymentRecord->status === 'paid') {
                            $runningPaid -= $stepPrice;
                        }
                    }
                }

                // Refresh payments collection after reconciliation
                $orderPayments = InstallmentOrderPayment::where('installment_order_id', $order->id)->get();
                  
            if (!empty($order) and !in_array($order->status, ['refunded', 'canceled'])) {

                $getRemainedInstallments = $this->getRemainedInstallments($order);
                
                $getOverdueOrderInstallments = $this->getOverdueOrderInstallments($order);

                $totalParts = 0;
                if ($order->installment && $order->installment->steps) {
                    $totalParts = $order->installment->steps->count();
                }
                
                $remainedParts = $getRemainedInstallments['total'];
                $remainedAmount = $getRemainedInstallments['amount'];

               
                $overdueAmount = $getOverdueOrderInstallments['amount'];

                $subSteps = SubStepInstallment::query()
                ->where('user_id', auth()->id())
                ->where('webinar_id', $order->webinar_id)
                ->where('status', 'approved')
                ->whereHas('installmentStep', function ($query) use ($order) {
                    $query->where('installment_id', $order->installment_id);
                })
                ->with('installmentStep')
                ->orderBy('installment_step_id', 'asc')
                ->orderBy('sub_step_number', 'asc')
                ->get();



                $data = [
                    'pageTitle' => trans('update.installments'),
                    'totalParts' => $totalParts,
                    'remainedParts' => $remainedParts,
                    'remainedAmount' => $remainedAmount,
                    'overdueAmount' => $overdueAmount,
                    'order' => $order,
                    'payments' => $order->payments,
                    'installment' => $order->installment,
                    'itemPrice' => $order->getItemPrice(),
                    'paidAmount' => $paidAmount,
                    'webinar_title' => $webinar_title,
                    'subSteps' => $subSteps,
                ];

                return view('web.default2.panel.financial.installments.details', $data);
            }

            abort(404);
        } catch (\Exception $e) {
            \Log::error('show error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }


    public function cancelVerification($orderId)
    {
        try {
            if (getInstallmentsSettings("allow_cancel_verification")) {
                $user = auth()->user();

                $order = InstallmentOrder::query()
                    ->where('id', $orderId)
                    ->where('user_id', $user->id)
                    ->where('status', "pending_verification")
                    ->first();

                if (!empty($order)) {
                    $installmentRefund = new InstallmentAccounting();
                    $installmentRefund->refundOrder($order);

                    return response()->json([
                        'code' => 200,
                        'title' => trans('public.request_success'),
                        'text' => trans('update.order_status_changes_to_canceled'),
                    ]);
                }
            }

            abort(404);
        } catch (\Exception $e) {
            \Log::error('cancelVerification error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function payUpcomingPart($orderId)
    {
        try {
            $user = auth()->user();

            $order = InstallmentOrder::query()
                ->where('id', $orderId)
                ->where('user_id', $user->id)
                ->first();

            if (!empty($order)) {
                $upcomingStep = $this->getUpcomingInstallment($order);

                if (!empty($upcomingStep)) {
                    return $this->handlePayStep($order, $upcomingStep);
                }
            }

            abort(404);
        } catch (\Exception $e) {
            \Log::error('payUpcomingPart error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function payStep($orderId, $stepId)
    {
        try {
            $user = auth()->user();

            $order = InstallmentOrder::query()
                ->where('id', $orderId)
                ->where('user_id', $user->id)
                ->first();

            if (!empty($order)) {
                $step = InstallmentStep::query()
                    ->where('installment_id', $order->installment_id)
                    ->where('id', $stepId)
                    ->first();

                if (!empty($step)) {
                    return $this->handlePayStep($order, $step);
                }
            }

            abort(404);
        } catch (\Exception $e) {
            \Log::error('payStep error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    private function handlePayStep($order, $step)
    {$user = auth()->user();
                    $WebinarPartPayment = WebinarPartPayment :: select('user_id', 'webinar_id', 'installment_id', DB::raw('sum(amount) as total_amount'))
        ->where('user_id',$user->id)
        ->where('webinar_id',$order->webinar_id)
    ->groupBy('user_id', 'webinar_id')
    ->first();
    $paidAmount=null;
    if($WebinarPartPayment){

    $orderPayments = InstallmentOrderPayment :: where('installment_order_id', $order->id)
            ->get();

                    $totalSaleAmount=0;

                foreach($orderPayments as $orderPayment){
                    $saleId = $orderPayment->sale_id;
                    if($saleId){
                    $sale = Sale :: where(['id' => $saleId ,
                    'status' => null,])
                    ->first();
                    if($sale)
                $totalSaleAmount+=$sale->total_amount;
                    }

                }

             $orderPaymentsTotalPaidAmount = InstallmentOrderPayment :: select('*', DB::raw('sum(amount) as total_amount'))
        ->where('installment_order_id',$order->id)
        ->where('status','paid')
    ->groupBy('installment_order_id')
    ->first();

    $paidAmount = $totalSaleAmount  +  (isset($WebinarPartPayment)?$WebinarPartPayment->total_amount:0) - (isset($orderPaymentsTotalPaidAmount)?$orderPaymentsTotalPaidAmount->total_amount:0);
    }

        $installmentPayment = InstallmentOrderPayment::query()->updateOrCreate([
            'installment_order_id' => $order->id,
            'sale_id' => null,
            'type' => 'step',
            'step_id' => $step->id,
            'amount' => $step->getPrice($order->getItemPrice()),
            'status' => 'paying',
        ], [
            'created_at' => time(),
        ]);

        Cart::updateOrCreate([
            'creator_id' => $order->user_id,
            'installment_payment_id' => $installmentPayment->id,
            'extra_amount' => $paidAmount,
        ], [
            'created_at' => time()
        ]);

        return redirect('/cart');
    }
}
