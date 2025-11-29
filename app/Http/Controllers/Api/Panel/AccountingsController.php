<?php

namespace App\Http\Controllers\Api\Panel;

use Illuminate\Support\Facades\Log;
use Exception;

use App\Http\Controllers\Api\Controller;
use App\Http\Controllers\Api\Web\WebinarController;
use App\Http\Controllers\Api\Panel\PaymentsController;
use App\Models\Api\Accounting;
use App\Models\OfflinePayment;
use App\Models\Order;
use App\Models\Webinar;
use App\Models\OrderItem;
use App\Models\PaymentChannel;
use App\PaymentChannels\ChannelManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use App\Models\Sale;
use App\Models\InstallmentOrderPayment;
use App\Models\WebinarPartPayment;

use Illuminate\Support\Facades\DB;
     use App\Models\InstallmentOrder;

class AccountingsController extends Controller
{

public function summarysaas()
{
        try {
            $user = apiAuth();

            $accountings = Accounting::where('user_id', $user->id)
            ->where('system', false)
            ->where('tax', false)
            ->where(DB::raw('`type`'), '!=', 'addiction')
            ->with([
                'webinar',

                'subscribe',
                'meetingTime.meeting.creator:id,full_name'
            ])
            ->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc')
            ->get();

            $sales = Sale::where([
            'buyer_id' => $user->id,
            'status' => null
            ])->get();

            $amountPaid = [];

            foreach ($sales as $sale) {
            if ($sale->webinar_id) {
                $webinar = Webinar::find($sale->webinar_id);

                $amountPaid[] = [
                    'amount' => $sale->total_amount,
                    'date' => $sale->created_at,
                    'title' => $webinar->title ?? 'Course',
                    'sale_id' => $sale->id,
                    'item_id' => $sale->webinar_id,
                    'type' => 'course',
                    'sub_type' => $sale->type,
                ];
            }

            if ($sale->meeting_id) {
                $amountPaid[] = [
                    'amount' => $sale->total_amount,
                    'date' => $sale->created_at,
                    'title' => 'Meeting',
                    'sale_id' => $sale->id,
                    'item_id' => $sale->meeting_id,
                    'type' => 'meeting',
                    'sub_type' => $sale->type,
                ];
            }
            }

            $partPayments = WebinarPartPayment::where('user_id', $user->id)->get();

            foreach ($partPayments as $payment) {
            $webinar = Webinar::find($payment->webinar_id);

            $amountPaid[] = [
                'amount' => $payment->amount,
                'date' => strtotime($payment->created_at),
                'title' => $webinar->title ?? 'Part Payment',
                'payment_id' => $payment->id,
                'item_id' => $payment->webinar_id,
                'type' => 'part',
                'sub_type' => '',
            ];
            }

            usort($amountPaid, fn ($a, $b) => $b['date'] <=> $a['date']);

            return response()->json([
            'status' => true,
            'message' => 'Financial summary retrieved successfully',
            'data' => [
                'accountings' => $accountings,
                'amount_paid' => $amountPaid,
                'commission' => getFinancialSettings('commission') ?? 0,
            ]
            ], 200);
        } catch (\Exception $e) {
            \Log::error('summarysaas error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

public function summary(Request $request)
{
        try {
            $user = apiAuth();

            $month = $request->get('month');
            $year = $request->get('year');

            $accountingsQuery = Accounting::where('user_id', $user->id)
            ->where('system', false)
            ->where('tax', false)
            ->where(DB::raw('`type`'), '!=', 'deduction')
            ->with([
                'webinar',

                'subscribe',
                'meetingTime' => function ($query) {
                    $query->with([
                        'meeting' => function ($query) {
                            $query->with([
                                'creator' => function ($query) {
                                    $query->select('id', 'full_name','avatar');
                                }
                            ]);
                        }
                    ]);
                }
            ]);

            if ($month && $year) {
            $startOfMonth = strtotime("{$year}-{$month}-01 00:00:00");
            $endOfMonth = strtotime("last day of {$year}-{$month} 23:59:59");
            $accountingsQuery->whereBetween('created_at', [$startOfMonth, $endOfMonth]);
            }

            $accountings = $accountingsQuery
            ->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc')
            ->get();

            $salesQuery = Sale::where('buyer_id', $user->id)
                          ->whereNull('status');

            if ($month && $year) {
            $salesQuery->whereMonth('created_at', $month)
                       ->whereYear('created_at', $year);
            } elseif ($year) {
            $salesQuery->whereYear('created_at', $year);
            }

            $sales = $salesQuery->get();

            $amountPaid = [];

            foreach ($sales as $sale) {
            if ($sale->webinar_id) {
                $webinar = Webinar::find($sale->webinar_id);
                $amountPaid[] = [
                    'amount' => $sale->total_amount,
                    'date' => $sale->created_at,
                    'title' => $webinar->title ?? 'Course',
                    'sale_id' => $sale->id,
                    'item_id' => $sale->webinar_id,
                    'type' => 'course',
                    'sub_type' => $sale->type,
                ];
            }

            if ($sale->meeting_id) {
                $amountPaid[] = [
                    'amount' => $sale->total_amount,
                    'date' => $sale->created_at,
                    'title' => 'Meeting',
                    'sale_id' => $sale->id,
                    'item_id' => $sale->meeting_id,
                    'type' => 'meeting',
                    'sub_type' => $sale->type,
                ];
            }
            }

            $partPaymentsQuery = WebinarPartPayment::where('user_id', $user->id);

            if ($month && $year) {
            $partPaymentsQuery->whereMonth('created_at', $month)
                              ->whereYear('created_at', $year);
            } elseif ($year) {
            $partPaymentsQuery->whereYear('created_at', $year);
            }

            $partPayments = $partPaymentsQuery->get();

            foreach ($partPayments as $payment) {
            $webinar = Webinar::find($payment->webinar_id);
            $amountPaid[] = [
                'amount' => $payment->amount,
                'date' => strtotime($payment->created_at),
                'title' => $webinar->title ?? 'Part Payment',
                'payment_id' => $payment->id,
                'item_id' => $payment->webinar_id,
                'type' => 'part',
                'sub_type' => '',
            ];
            }
                $sales = Sale::where('buyer_id', $user->id)
                    ->whereNull('status')
                    ->get();

                $salesByWebinar = $sales->whereNotNull('webinar_id')->keyBy('webinar_id');
                $salesByMeeting = $sales->whereNotNull('meeting_time_id')->keyBy('meeting_time_id');

                foreach ($accountings as $accounting) {
                    $saleId = null;

                    if ($accounting->webinar_id && $accounting->webinar_id) {
                        $saleId = $salesByWebinar[$accounting->webinar_id]->id;
                    }
                     if ($accounting->meeting_time_id && $accounting->meeting_time_id) {
                        $saleId = $salesByMeeting[$accounting->meeting_time_id]->id;
                    }

                    $accounting->sale_id = $saleId;
                }

            usort($amountPaid, fn($a, $b) => $b['date'] <=> $a['date']);

            return response()->json([
            'status' => true,
            'message' => 'Financial summary retrieved successfully',
            'data' => [
                'accountings' => $accountings,
                'amount_paid' => $amountPaid,
                'commission' => getFinancialSettings('commission') ?? 0,
                'filters' => [
                    'month' => $month,
                    'year' => $year
                ]
            ]
            ]);
        } catch (\Exception $e) {
            \Log::error('summary error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

public function summaryss(Request $request)
{
        try {
            $user = apiAuth();

            $month = $request->input('month');
            $year = $request->input('year');

            $query = Accounting::where('user_id', $user->id)
            ->where('system', false)
            ->where('tax', false);

            if ($month && $year) {

            $startOfMonth = strtotime("{$year}-{$month}-01 00:00:00");
            $endOfMonth = strtotime("last day of {$year}-{$month} 23:59:59");

            $query->whereBetween('created_at', [$startOfMonth, $endOfMonth]);
            }

            $accountings = $query->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($accounting) {
                return $accounting->details;
            });

            return apiResponse2(1, 'retrieved', trans('api.public.retrieved'), [
            'balance' => $user->getAccountingBalance(),
            'history' => $accountings
            ]);
        } catch (\Exception $e) {
            \Log::error('summaryss error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

   public function monthSpend(Request $request)
{
    $user = apiAuth();

    $monthInput = $request->get('month', Carbon::now()->format('Y-m'));
    if (!$monthInput) {
        return response()->json(['error' => 'Month is required in YYYY-MM format'], 400);
    }

    try {
        $startOfMonth = Carbon::createFromFormat('Y-m', $monthInput)->startOfMonth();
        $endOfMonth = Carbon::createFromFormat('Y-m', $monthInput)->endOfMonth();
    } catch (\Exception $e) {
        return response()->json(['error' => 'Invalid month format. Use YYYY-MM'], 422);
    }

    $salesData = Sale::where('buyer_id', $user->id)
        ->whereNull('refund_at')
        ->whereNull('status')
        ->selectRaw("DATE(FROM_UNIXTIME(created_at)) as day, total_amount as amount")
        ->groupBy(DB::raw("DATE(FROM_UNIXTIME(created_at))"))
        ->pluck('amount', 'day');

    $daysInMonth = [];
    $currentDay = $startOfMonth->copy();

    while ($currentDay <= $endOfMonth) {
        $dateStr = $currentDay->format('Y-m-d');
        $daysInMonth[] = [

            'value' => isset($salesData[$dateStr]) ? (float)$salesData[$dateStr] : 0.0
        ];
        $currentDay->addDay();
    }

   return apiResponse2(1, 'retrieved', trans('public.retrieved'),[
        'month' => $monthInput,
        'chart_data' => $daysInMonth
    ]);

}
        public function fundOverview(Request $request)
    {
        try {
            $user = apiAuth();

            $end = Carbon::now()->startOfMonth();
            $start = $end->copy()->subMonths(5);

            $rawSales = Sale::where('buyer_id', $user->id)
                ->whereNull('refund_at')
                ->whereNull('status')
                ->whereBetween('created_at', [$start->timestamp, $end->endOfMonth()->timestamp])
                ->selectRaw("DATE_FORMAT(FROM_UNIXTIME(created_at), '%Y-%b') as month, SUM(total_amount) as total_spent")
                ->groupBy(DB::raw("YEAR(FROM_UNIXTIME(created_at)), MONTH(FROM_UNIXTIME(created_at))"))
                ->pluck('total_spent', 'month');

            $result = [];
            for ($i = 0; $i < 6; $i++) {
                $monthLabel = $start->copy()->addMonths($i)->format('Y-M');
                $result[$monthLabel] = isset($rawSales[$monthLabel]) ? (float) $rawSales[$monthLabel] : 0.0;
            }

            return apiResponse2(1, 'retrieved', trans('public.retrieved'), $result);
        } catch (\Exception $e) {
            \Log::error('fundOverview error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

public function fundOverviewMonth(Request $request)
{
        try {
            $user = apiAuth();

            $startOfMonth = Carbon::now()->startOfMonth()->timestamp;
            $endOfMonth = Carbon::now()->endOfMonth()->timestamp;

            $sales = Sale::where('buyer_id', $user->id)
            ->whereNull('refund_at')
            ->whereNull('status')
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->get();

            $sales1 = Sale::where('buyer_id', $user->id)
            ->whereNull('refund_at')
            ->whereNull('status')
            ->get();

            $order = InstallmentOrder::query()

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
            $remainedAmount =0;
            if (!empty($order) and !in_array($order->status, ['refunded', 'canceled'])) {

                $getRemainedInstallments = $this->getRemainedInstallments($order);

                $remainedAmount = $getRemainedInstallments['amount'];

            }

            $spendAmount = $sales->sum(function ($sale) {
            return (float) $sale->total_amount;
            });

            $totalAmount = $sales1->sum(function ($sale1) {
            return (float) $sale1->total_amount;
            });;

            $remainingAmount = $totalAmount - $spendAmount;

            return apiResponse2(1, 'retrieved', 'Current month fund overview', [
            'total_amount' => round($totalAmount, 2),
            'spend_amount' => round($spendAmount, 2),
            'remaining_amount' => (int )round($remainingAmount, 2),
            ]);
        } catch (\Exception $e) {
            \Log::error('fundOverviewMonth error: ' . $e->getMessage(), [
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
                ->whereHas('sale', function ($query) {
                    $query->whereNull('refund_at');
                })
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

    public function listOfflinePayment()
    {
        try {
            $user = apiAuth();
            $offlinePayments = OfflinePayment::where('user_id', $user->id)->orderBy('created_at', 'desc')->get()
                ->map(function ($offlinePayment) {
                    return [
                        'amount' => $offlinePayment->amount,
                        'bank' => $offlinePayment->bank,
                        'reference_number' => $offlinePayment->reference_number,
                        'status' => $offlinePayment->status,
                        'created_at' => $offlinePayment->created_at,
                        'pay_date' => $offlinePayment->pay_date,
                    ];

                });
            return apiResponse2(1, 'retrieved', trans('api.public.retrieved'), $offlinePayments);
        } catch (\Exception $e) {
            \Log::error('listOfflinePayment error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function platformBankAccounts(){
        try {
            $accounts=[] ;
            foreach(getOfflineBankSettings() as $account){

                if(isset($account['image'])){
                    $account['image']=url( $account['image']) ;
                }
                $accounts[]=$account ;
            }

            return apiResponse2(1, 'retrieved', trans('api.public.retrieved'),[
                'accounts'=>$accounts
            ]);
        } catch (\Exception $e) {
            \Log::error('platformBankAccounts error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function accountTypes(){
        try {
            return apiResponse2(1, 'retrieved', trans('api.public.retrieved'),[
                'accounts_type'=> getOfflineBanksTitle()
            ]);
        } catch (\Exception $e) {
            \Log::error('accountTypes error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

}
