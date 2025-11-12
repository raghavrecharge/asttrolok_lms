<?php

namespace App\Http\Controllers\Api\Panel;

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
    // public function summary(Request $request)
    // {
    //     $user = apiAuth();
    //     $accountings = Accounting::where('user_id', $user->id)
    //         ->where('system', false)
    //         ->where('tax', false)
    //         ->orderBy('created_at', 'desc')
    //         ->get()
    //         ->map(function ($accounting) {
    //             return $accounting->details ;
    //         });


    //     return apiResponse2(1, 'retrieved', trans('api.public.retrieved'), [
    //         'balance'=> $user->getAccountingBalance() ,
    //         'history'=>$accountings
    //     ]);

    // }
    
//     public function summary(Request $request)
// {
//     $user = apiAuth();

//     $from = $request->input('from_date'); // expected format: Y-m-d
//     $to = $request->input('to_date');     // expected format: Y-m-d

//     $query = Accounting::where('user_id', $user->id)
//         ->where('system', false)
//         ->where('tax', false);

//     if ($from && $to) {
//         $fromTimestamp = strtotime($from . ' 00:00:00');
//         $toTimestamp = strtotime($to . ' 23:59:59');

//         $query->whereBetween('created_at', [$fromTimestamp, $toTimestamp]);
//     }

//     $accountings = $query->orderBy('created_at', 'desc')
//         ->get()
//         ->map(function ($accounting) {
//             return $accounting->details;
//         });

//     return apiResponse2(1, 'retrieved', trans('api.public.retrieved'), [
//         'balance' => $user->getAccountingBalance(),
//         'history' => $accountings
//     ]);
// }


// public function summary()
//     {
//         $userAuth  = apiAuth();
//         $accountings = Accounting::where('user_id', $userAuth->id)
//             ->where('system', false)
//             ->where('tax', false)
//             ->with([
//                 'webinar',
//                 'promotion',
//                 'subscribe',
//                 'meetingTime' => function ($query) {
//                     $query->with(['meeting' => function ($query) {
//                         $query->with(['creator' => function ($query) {
//                             $query->select('id', 'full_name');
//                         }]);
//                     }]);
//                 }
//             ])
//             ->orderBy('created_at', 'desc')
//             ->orderBy('id', 'desc')
//             ->get();
            
//             $sales1 = Sale::where(['buyer_id'=> $userAuth->id, 'status'=> null])->get();
//             // echo "<pre>";
//             // print_r($sales1);
//             $amount_paid=[];
//             foreach($sales1 as $sales2){
//                 if($sales2->webinar_id){
//                     $webinars1 = Webinar:: where('id', $sales2->webinar_id)
//             ->first();
//                     $amount_paid[]=[ $sales2->total_amount , $sales2->created_at , $webinars1->title ,$sales2->id,$sales2->webinar_id,'course',$sales2->type];
//                 }
//                 if($sales2->meeting_id){
//             //         $webinars1 = Webinar:: where('id', $sales2->webinar_id)
//             // ->frist();
//                     $amount_paid[]=[ $sales2->total_amount , $sales2->created_at , 'Meeting',$sales2->id,$sales2->meeting_id,'meeting',$sales2->type ];
//                 }
//             }
            
//             $WebinarPartPayment =  WebinarPartPayment :: where('user_id',$userAuth->id)->get();
                    
//             foreach ($WebinarPartPayment as $WebinarPartPayment1){
//                 $webinars1 = Webinar:: where('id', $WebinarPartPayment1->webinar_id)
//             ->first();
//                 $amount_paid[]=[ $WebinarPartPayment1->amount , strtotime($WebinarPartPayment1->created_at) , $webinars1->title,$WebinarPartPayment1->id,$WebinarPartPayment1->webinar_id,'part',''];
//             }
//             usort($amount_paid, function($a, $b) {
//                 return $b[1] <=> $a[1];
//             });
//             $data['amount_paid'] = $amount_paid;


//         $data = [
//             'pageTitle' => trans('financial.summary_page_title'),
//             'history' => $accountings,
//             'amount_paid' => $amount_paid,
//             'commission' => getFinancialSettings('commission') ?? 0
//         ];
// // print_r($data);
//   return apiResponse2(1, 'retrieved', trans('api.public.retrieved'), $data);   
//   }

// public function summarssssy(Request $request)
// {
//     $user = apiAuth();

//     $month = $request->input('month'); // e.g., 5
//     $year = $request->input('year');   // e.g., 2023

//     $accountingsQuery = Accounting::where('user_id', $user->id)
//         ->where('system', false)
//         ->where('tax', false)
//         ->with([
//             'webinar',
//             'promotion',
//             'subscribe',
//             'meetingTime' => function ($query) {
//                 $query->with([
//                     'meeting' => function ($query) {
//                         $query->with([
//                             'creator' => function ($query) {
//                                 $query->select('id', 'full_name');
//                             }
//                         ]);
//                     }
//                 ]);
//             }
//         ]);

//     if ($month && $year) {
//         $startOfMonth = strtotime("{$year}-{$month}-01 00:00:00");
//         $endOfMonth = strtotime("last day of {$year}-{$month} 23:59:59");
//         $accountingsQuery->whereBetween('created_at', [$startOfMonth, $endOfMonth]);
//     }

//     // Fetch all matching accountings (no pagination)
//     $accountings = $accountingsQuery->orderBy('created_at', 'desc')->orderBy('id', 'desc')->get();

//     $amount_paid = [];

//     // Fetch Sales
//     $sales = Sale::where('buyer_id', $user->id)
//         ->whereNull('status')
//         ->when($month && $year, function ($query) use ($year, $month) {
//             $startOfMonth = strtotime("{$year}-{$month}-01 00:00:00");
//         $endOfMonth = strtotime("last day of {$year}-{$month} 23:59:59");
//             $query->whereBetween('created_at', [$startOfMonth, $endOfMonth]);
//         })
//         ->get();

//     foreach ($sales as $sale) {
//         if ($sale->webinar_id) {
//             $webinar = Webinar::find($sale->webinar_id);
//             $amount_paid[] = [
//                 $sale->total_amount,
//                 $sale->created_at,
//                 $webinar->title ?? '',
//                 $sale->id,
//                 $sale->webinar_id,
//                 'course',
//                 $sale->type
//             ];
//         }

//         if ($sale->meeting_id) {
//             $amount_paid[] = [
//                 $sale->total_amount,
//                 $sale->created_at,
//                 'Meeting',
//                 $sale->id,
//                 $sale->meeting_id,
//                 'meeting',
//                 $sale->type
//             ];
//         }
//     }

//     // Fetch Webinar Part Payments
//     $WebinarPartPayments = WebinarPartPayment::where('user_id', $user->id)
//         ->when($month && $year, function ($query) use ($year, $month) {
//             $startOfMonth = strtotime("{$year}-{$month}-01 00:00:00");
//           $endOfMonth = strtotime("last day of {$year}-{$month} 23:59:59");
//             $query->whereBetween('created_at', [$startOfMonth, $endOfMonth]);
//         })
//         ->get();

//     foreach ($WebinarPartPayments as $partPayment) {
//         $webinar = Webinar::find($partPayment->webinar_id);
//         $amount_paid[] = [
//             $partPayment->amount,
//             strtotime($partPayment->created_at),
//             $webinar->title ?? '',
//             $partPayment->id,
//             $partPayment->webinar_id,
//             'part',
//             ''
//         ];
//     }

//     // Sort amount_paid by date descending
//     usort($amount_paid, function ($a, $b) {
//         return $b[1] <=> $a[1];
//     });

//     return apiResponse2(1, 'retrieved', trans('api.public.retrieved'), [
//         'balance' => $user->getAccountingBalance(),
//         'history' => $accountings,
//         'amount_paid' => $amount_paid,
//     ]);
// }

public function summarysaas()
{
    $user = apiAuth();

    // Fetch accountings
    $accountings = Accounting::where('user_id', $user->id)
        ->where('system', false)
        ->where('tax', false)
        ->where(DB::raw('`type`'), '!=', 'addiction')
        ->with([
            'webinar',
            // 'promotion',
            'subscribe',
            'meetingTime.meeting.creator:id,full_name'
        ])
        ->orderBy('created_at', 'desc')
        ->orderBy('id', 'desc')
        ->get();

    // Fetch unpaid sales
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

    // Add part payments
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

    // Sort by date descending
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
}

public function summary(Request $request)
{
    $user = apiAuth();

    // Get filters
    $month = $request->get('month'); // format: 1 to 12
    $year = $request->get('year');   // format: 2025



    // Build query with optional month/year filter
    $accountingsQuery = Accounting::where('user_id', $user->id)
        ->where('system', false)
        ->where('tax', false)
        ->where(DB::raw('`type`'), '!=', 'deduction')
        ->with([
            'webinar',
            // 'promotion',
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

    // Sales filter by month/year
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

    // Webinar Part Payments filter
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
            // print_r($salesByMeeting);die;
            // Step 3: Add sale_id to each accounting
            foreach ($accountings as $accounting) {
                $saleId = null;
            
                // 1. Match by webinar_id
                if ($accounting->webinar_id && $accounting->webinar_id) {
                    $saleId = $salesByWebinar[$accounting->webinar_id]->id;
                }
                 if ($accounting->meeting_time_id && $accounting->meeting_time_id) {
                    $saleId = $salesByMeeting[$accounting->meeting_time_id]->id;
                }
            
                $accounting->sale_id = $saleId;
            }


    // Sort by date descending
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
}


public function summaryss(Request $request)
{
    $user = apiAuth();

    $month = $request->input('month'); // e.g., 5
    $year = $request->input('year');   // e.g., 2023

    $query = Accounting::where('user_id', $user->id)
        ->where('system', false)
        ->where('tax', false);

    if ($month && $year) {
        // Get start and end timestamps of that month
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
}

   public function monthSpend(Request $request)
{
    $user = apiAuth();

    // $monthInput = $request->get('month'); // format: YYYY-MM
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
            // 'day' => $dateStr,
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
        $user = apiAuth();
    
        $end = Carbon::now()->startOfMonth();
        $start = $end->copy()->subMonths(5); // 6 months including current
    
        $rawSales = Sale::where('buyer_id', $user->id)
            ->whereNull('refund_at')
            ->whereNull('status')
            ->whereBetween('created_at', [$start->timestamp, $end->endOfMonth()->timestamp])
            ->selectRaw("DATE_FORMAT(FROM_UNIXTIME(created_at), '%Y-%b') as month, SUM(total_amount) as total_spent")
            ->groupBy(DB::raw("YEAR(FROM_UNIXTIME(created_at)), MONTH(FROM_UNIXTIME(created_at))"))
            ->pluck('total_spent', 'month'); // key = month, value = total_spent
    
        $result = [];
        for ($i = 0; $i < 6; $i++) {
            $monthLabel = $start->copy()->addMonths($i)->format('Y-M'); // e.g., 2025-Jan
            $result[$monthLabel] = isset($rawSales[$monthLabel]) ? (float) $rawSales[$monthLabel] : 0.0;
        }
    
        return apiResponse2(1, 'retrieved', trans('public.retrieved'), $result);
    }
    

public function fundOverviewMonth(Request $request)
{
    $user = apiAuth();

    // Set current month start and end timestamps
    $startOfMonth = Carbon::now()->startOfMonth()->timestamp;
    $endOfMonth = Carbon::now()->endOfMonth()->timestamp;

    // Fetch current month sales for user
    $sales = Sale::where('buyer_id', $user->id)
        ->whereNull('refund_at')
        ->whereNull('status')
        ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
        ->get();
            // Fetch current month sales for user
            
    $sales1 = Sale::where('buyer_id', $user->id)
        ->whereNull('refund_at')
        ->whereNull('status')
        ->get();
        
        $order = InstallmentOrder::query()
            // ->where('id', $orderId)
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
            // $totalParts = $order->installment->steps->count();
            // $remainedParts = $getRemainedInstallments['total'];
            $remainedAmount = $getRemainedInstallments['amount'];
            // $overdueAmount = $getOverdueOrderInstallments['amount'];
            
        }
        
        
        
        

    // Sum of spent amount in this month
    $spendAmount = $sales->sum(function ($sale) {
        return (float) $sale->total_amount;
    });

    // Define total monthly fund from settings or fixed value
    $totalAmount = $sales1->sum(function ($sale1) {
        return (float) $sale1->total_amount;
    });; // or fetch from user profile, settings etc.

    // Calculate remaining
    $remainingAmount = $totalAmount - $spendAmount;

    return apiResponse2(1, 'retrieved', 'Current month fund overview', [
        'total_amount' => round($totalAmount, 2),
        'spend_amount' => round($spendAmount, 2),
        'remaining_amount' => (int )round($remainingAmount, 2),
    ]);
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

    }

    public function platformBankAccounts(){

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

    }

    public function accountTypes(){

        return apiResponse2(1, 'retrieved', trans('api.public.retrieved'),[
            'accounts_type'=> getOfflineBanksTitle()
        ]);
    }







}
