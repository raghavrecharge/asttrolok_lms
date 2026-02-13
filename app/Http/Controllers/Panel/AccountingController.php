<?php

namespace App\Http\Controllers\Panel;

use Illuminate\Support\Facades\Log;
use Exception;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\PaymentController;
use App\Models\Accounting;
use App\Models\OfflineBank;
use App\Models\OfflinePayment;
use App\Models\Order;
use App\Models\Webinar;
use App\Models\Subscription;
use App\Models\OrderItem;
use App\Models\PaymentChannel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;
use App\Models\Sale;
use App\Models\WebinarPartPayment;

class AccountingController extends Controller
{

       public function index()
{
    try {
        $userAuth = auth()->user();

        if (!$userAuth) {
            return redirect()->route('login')->with('error', 'Please login first');
        }

        $accountings = Accounting::where('user_id', $userAuth->id)
            ->where('system', false)
            ->where('tax', false)
            ->with([
                'webinar',
                'subscribe',
                'meetingTime' => function ($query) {
                    $query->with(['meeting' => function ($query) {
                        $query->with(['creator' => function ($query) {
                            $query->select('id', 'full_name');
                        }]);
                    }]);
                }
            ])
            ->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(10);

        $sales = Sale::where(['buyer_id' => $userAuth->id, 'status' => null])->get();
        $amount_paid = [];

        foreach ($sales as $sale) {
            try {
                if ($sale->webinar_id) {
                    $webinar = Webinar::find($sale->webinar_id);
                    if ($webinar) {
                        $amount_paid[] = [
                            $sale->total_amount,
                            $sale->created_at,
                            $webinar->title,
                            $sale->id,
                            $sale->webinar_id,
                            'course',
                            $sale->type
                        ];
                    }
                }

                if ($sale->meeting_id) {
                    $amount_paid[] = [
                        $sale->total_amount,
                        $sale->created_at,
                        'Meeting',
                        $sale->id,
                        $sale->meeting_id,
                        'meeting',
                        $sale->type
                    ];
                }

                if ($sale->bundle_id) {
                    $amount_paid[] = [
                        $sale->total_amount,
                        $sale->created_at,
                        'Bundle Course',
                        $sale->id,
                        $sale->bundle_id,
                        'bundle',
                        $sale->type
                    ];
                }

                if ($sale->subscription_id) {
                    $subscription = Subscription::find($sale->subscription_id);
                    if ($subscription) {
                        $amount_paid[] = [
                            $sale->total_amount,
                            $sale->created_at,
                            $subscription->title,
                            $sale->id,
                            $sale->subscription_id,
                            'subscription',
                            $sale->type
                        ];
                    }
                }

                if ($sale->product_order_id) {
                    $amount_paid[] = [
                        $sale->total_amount,
                        $sale->created_at,
                        'Product',
                        $sale->id,
                        $sale->product_order_id,
                        'product',
                        $sale->type
                    ];
                }
            } catch (\Exception $e) {
                \Log::error('Error processing sale: ' . $e->getMessage(), [
                    'sale_id' => $sale->id ?? null
                ]);
                continue;
            }
        }

        try {
            $webinarPartPayments = WebinarPartPayment::where('user_id', $userAuth->id)->get();

            foreach ($webinarPartPayments as $payment) {
                try {
                    $webinar = Webinar::find($payment->webinar_id);
                    if ($webinar) {
                        $amount_paid[] = [
                            $payment->amount,
                            strtotime($payment->created_at),
                            $webinar->title,
                            $payment->id,
                            $payment->webinar_id,
                            'part',
                            ''
                        ];
                    }
                } catch (\Exception $e) {
                    \Log::error('Error processing part payment: ' . $e->getMessage(), [
                        'payment_id' => $payment->id ?? null
                    ]);
                    continue;
                }
            }
        } catch (\Exception $e) {
            \Log::error('Error fetching part payments: ' . $e->getMessage());
        }

        usort($amount_paid, function ($a, $b) {
            return $b[1] <=> $a[1];
        });

        $data = [
            'pageTitle' => trans('financial.summary_page_title'),
            'accountings' => $accountings,
            'amount_paid' => $amount_paid,
            'commission' => getFinancialSettings('commission') ?? 0
        ];

        return view(getTemplate() . '.panel.financial.summary', $data);

    } catch (\Exception $e) {
        \Log::error('Financial summary error: ' . $e->getMessage(), [
            'user_id' => auth()->id() ?? null,
            'trace' => $e->getTraceAsString()
        ]);

        return redirect()->back()->with('error', 'Something went wrong. Please try again later.');
    }
}

    public function account($id = null)
    {
        try {
            $userAuth = auth()->user();

            $editOfflinePayment = null;
            if (!empty($id)) {
                $editOfflinePayment = OfflinePayment::where('id', $id)
                    ->where('user_id', $userAuth->id)
                    ->first();
            }

            $paymentChannels = PaymentChannel::where('status', 'active')->get();
            $offlinePayments = OfflinePayment::where('user_id', $userAuth->id)->orderBy('created_at', 'desc')->get();

            $offlineBanks = OfflineBank::query()
                ->orderBy('created_at', 'desc')
                ->with([
                    'specifications'
                ])
                ->get();

            $razorpay = false;
            foreach ($paymentChannels as $paymentChannel) {
                if ($paymentChannel->class_name == 'Razorpay') {
                    $razorpay = true;
                }
            }

            $registrationBonusSettings = getRegistrationBonusSettings();
            $registrationBonusAccounting = Accounting::query()
                ->where('user_id', $userAuth->id)
                ->where('is_registration_bonus', true)
                ->where('system', false)
                ->first();
            $registrationBonusAmount = (empty($registrationBonusAccounting) and !empty($registrationBonusSettings['status']) and !empty($registrationBonusSettings['registration_bonus_amount'])) ? $registrationBonusSettings['registration_bonus_amount'] : null;

            $data = [
                'pageTitle' => trans('financial.charge_account_page_title'),
                'offlinePayments' => $offlinePayments,
                'paymentChannels' => $paymentChannels,
                'offlineBanks' => $offlineBanks,
                'accountCharge' => $userAuth->getAccountingCharge(),
                'readyPayout' => $userAuth->getPayout(),
                'totalIncome' => $userAuth->getIncome(),
                'editOfflinePayment' => $editOfflinePayment,
                'razorpay' => $razorpay,
                'registrationBonusAmount' => $registrationBonusAmount,
            ];

            return view('web.default.panel.financial.account', $data);
        } catch (\Exception $e) {
            \Log::error('account error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function charge(Request $request)
    {
        try {
            $rules = [
                'amount' => 'required|numeric|min:0',
                'gateway' => 'required',
                'account' => 'required_if:gateway,offline',
                'referral_code' => 'required_if:gateway,offline',
                'date' => 'required_if:gateway,offline',
            ];

            if (!empty($request->file('attachment'))) {
                $rules['attachment'] = 'image|mimes:jpeg,png,jpg|max:10240';
            }

            $this->validate($request, $rules);

            $gateway = $request->input('gateway');
            $amount = $request->input('amount');
            $account = $request->input('account');
            $referenceNumber = $request->input('referral_code');
            $date = $request->input('date');

            if ($amount <= 0) {
                return back()->withErrors([
                    'amount' => trans('update.the_amount_must_be_greater_than_0')
                ]);
            }

            $amount = convertPriceToDefaultCurrency($amount);
            $userAuth = auth()->user();

            if ($gateway === 'offline') {

                $attachment = null;

                if (!empty($request->file('attachment'))) {
                    $attachment = $this->handleUploadAttachment($userAuth, $request->file('attachment'));
                }

                $date = convertTimeToUTCzone($date, getTimezone());

                OfflinePayment::create([
                    'user_id' => $userAuth->id,
                    'amount' => $amount,
                    'offline_bank_id' => $account,
                    'reference_number' => $referenceNumber,
                    'status' => OfflinePayment::$waiting,
                    'pay_date' => $date->getTimestamp(),
                    'attachment' => $attachment,
                    'created_at' => time(),
                ]);

                $notifyOptions = [
                    '[amount]' => handlePrice($amount),
                    '[u.name]' => $userAuth->full_name
                ];
                sendNotification('offline_payment_request', $notifyOptions, $userAuth->id);
                sendNotification('new_offline_payment_request', $notifyOptions, 1);

                $sweetAlertData = [
                    'msg' => trans('financial.offline_payment_request_success_store'),
                    'status' => 'success'
                ];
                return back()->with(['sweetalert' => $sweetAlertData]);
            }

            $paymentChannel = PaymentChannel::where('class_name', $gateway)->where('status', 'active')->first();

            if (!$paymentChannel) {
                $toastData = [
                    'title' => trans('public.request_failed'),
                    'msg' => trans('public.payment_dont_access'),
                    'status' => 'error'
                ];
                return back()->with(['toast' => $toastData]);
            }

            $order = Order::create([
                'user_id' => $userAuth->id,
                'status' => Order::$pending,
                'payment_method' => Order::$paymentChannel,
                'is_charge_account' => true,
                'total_amount' => $amount,
                'amount' => $amount,
                'created_at' => time(),
                'type' => Order::$charge,
            ]);

            OrderItem::updateOrCreate([
                'user_id' => $userAuth->id,
                'order_id' => $order->id,
            ], [
                'amount' => $amount,
                'total_amount' => $amount,
                'tax' => 0,
                'tax_price' => 0,
                'commission' => 0,
                'commission_price' => 0,
                'created_at' => time(),
            ]);

            if ($paymentChannel->class_name == 'Razorpay') {
                return $this->echoRozerpayForm($order);
            } else {
                $paymentController = new PaymentController();

                $paymentRequest = new Request();
                $paymentRequest->merge([
                    'gateway' => $paymentChannel->id,
                    'order_id' => $order->id
                ]);

                return $paymentController->paymentRequest($paymentRequest);
            }
        } catch (\Exception $e) {
            \Log::error('charge error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    private function handleUploadAttachment($user, $file)
    {
        $storage = Storage::disk('public');

        $path = '/' . $user->id . '/offlinePayments';

        if (!$storage->exists($path)) {
            $storage->makeDirectory($path);
        }

        $img = Image::make($file);
        $name = time() . '.' . $file->getClientOriginalExtension();

        $path = $path . '/' . $name;

        $storage->put($path, (string)$img->encode());

        return $name;
    }

    private function echoRozerpayForm($order)
    {
        $generalSettings = getGeneralSettings();

        echo '<form action="/payments/verify/Razorpay" method="get">
            <input type="hidden" name="order_id" value="' . $order->id . '">

            <script src="/assets/default/js/app.js"></script>
            <script src="https://checkout.razorpay.com/v1/checkout.js"
                    data-key="' . env('RAZORPAY_API_KEY') . '"
                    data-amount="' . (int)($order->total_amount * 100) . '"
                    data-buttontext="product_price"
                    data-description="Rozerpay"
                    data-currency="' . currency() . '"
                    data-image="' . $generalSettings['logo'] . '"
                    data-prefill.name="' . $order->user->full_name . '"
                    data-prefill.email="' . $order->user->email . '"
                    data-theme.color="#43d477">
            </script>

            <style>
                .razorpay-payment-button {
                    opacity: 0;
                    visibility: hidden;
                }
            </style>

            <script>
                $(document).ready(function() {
                    $(".razorpay-payment-button").trigger("click");
                })
            </script>
        </form>';

        $userAuth = auth()->user();

        $editOfflinePayment = null;
        $id='';
        if (!empty($id)) {
            $editOfflinePayment = OfflinePayment::where('id', $id)
                ->where('user_id', $userAuth->id)
                ->first();
        }

        $paymentChannels = PaymentChannel::where('status', 'active')->get();
        $offlinePayments = OfflinePayment::where('user_id', $userAuth->id)->orderBy('created_at', 'desc')->get();

        $offlineBanks = OfflineBank::query()
            ->orderBy('created_at', 'desc')
            ->with([
                'specifications'
            ])
            ->get();

        $razorpay = false;
        foreach ($paymentChannels as $paymentChannel) {
            if ($paymentChannel->class_name == 'Razorpay') {
                $razorpay = true;
            }
        }

        $registrationBonusSettings = getRegistrationBonusSettings();
        $registrationBonusAccounting = Accounting::query()
            ->where('user_id', $userAuth->id)
            ->where('is_registration_bonus', true)
            ->where('system', false)
            ->first();
        $registrationBonusAmount = (empty($registrationBonusAccounting) and !empty($registrationBonusSettings['status']) and !empty($registrationBonusSettings['registration_bonus_amount'])) ? $registrationBonusSettings['registration_bonus_amount'] : null;

        $data = [
            'pageTitle' => trans('financial.charge_account_page_title'),
            'offlinePayments' => $offlinePayments,
            'paymentChannels' => $paymentChannels,
            'offlineBanks' => $offlineBanks,
            'accountCharge' => $userAuth->getAccountingCharge(),
            'readyPayout' => $userAuth->getPayout(),
            'totalIncome' => $userAuth->getIncome(),
            'editOfflinePayment' => $editOfflinePayment,
            'razorpay' => $razorpay,
            'registrationBonusAmount' => $registrationBonusAmount,
        ];

        return view('web.default.panel.financial.account', $data);

    }

    public function updateOfflinePayment(Request $request, $id)
    {
        try {
            $user = auth()->user();
            $offline = OfflinePayment::where('id', $id)
                ->where('user_id', $user->id)
                ->first();

            if (!empty($offline)) {
                $data = $request->all();

                $rules = [
                    'amount' => 'required|numeric',
                    'gateway' => 'required',
                    'account' => 'required_if:gateway,offline',
                    'referral_code' => 'required_if:gateway,offline',
                    'date' => 'required_if:gateway,offline',
                ];

                if (!empty($request->file('attachment'))) {
                    $rules['attachment'] = 'image|mimes:jpeg,png,jpg|max:10240';
                }

                $this->validate($request, $rules);

                $attachment = $offline->attachment;

                if (!empty($request->file('attachment'))) {
                    $attachment = $this->handleUploadAttachment($user, $request->file('attachment'));
                }

                $date = convertTimeToUTCzone($data['date'], getTimezone());

                $offline->update([
                    'amount' => $data['amount'],
                    'bank' => $data['account'],
                    'reference_number' => $data['referral_code'],
                    'status' => OfflinePayment::$waiting,
                    'attachment' => $attachment,
                    'pay_date' => $date->getTimestamp(),
                ]);

                return redirect('/panel/financial/account');
            }

            abort(404);
        } catch (\Exception $e) {
            \Log::error('updateOfflinePayment error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function deleteOfflinePayment($id)
    {
        try {
            $user = auth()->user();
            $offline = OfflinePayment::where('id', $id)
                ->where('user_id', $user->id)
                ->first();

            if (!empty($offline)) {
                $offline->delete();

                return response()->json([
                    'code' => 200
                ], 200);
            }

            return response()->json([], 422);
        } catch (\Exception $e) {
            \Log::error('deleteOfflinePayment error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }
}
