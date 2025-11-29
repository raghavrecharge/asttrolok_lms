<?php

namespace App\Http\Controllers\Panel;

use Illuminate\Support\Facades\Log;
use Exception;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\traits\InstallmentsTrait;
use App\Mixins\Installment\InstallmentPlans;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PaymentChannel;
use App\Models\Sale;
use App\Models\Setting;
use App\Models\Subscribe;
use App\User;
use Illuminate\Http\Request;

class SubscribesController extends Controller
{
    use InstallmentsTrait;

    public function index()
    {
        try {
            $user = auth()->user();
            $subscribes = Subscribe::all();

            $installmentPlans = new InstallmentPlans($user);
            foreach ($subscribes as $subscribe) {
                if (getInstallmentsSettings('status') and $user->enable_installments and $subscribe->price > 0) {
                    $installments = $installmentPlans->getPlans('subscription_packages', $subscribe->id);

                    $subscribe->has_installment = (!empty($installments) and count($installments));
                }
            }

            $data = [
                'pageTitle' => trans('financial.subscribes'),
                'subscribes' => $subscribes,
                'activeSubscribe' => Subscribe::getActiveSubscribe($user->id),
                'dayOfUse' => Subscribe::getDayOfUse($user->id),
            ];

            return view(getTemplate() . '.panel.financial.subscribes', $data);
        } catch (\Exception $e) {
            \Log::error('index error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function pay(Request $request)
    {
        try {
            $paymentChannels = PaymentChannel::where('status', 'active')->get();

            $subscribe = Subscribe::where('id', $request->input('id'))->first();

            if (empty($subscribe)) {
                $toastData = [
                    'msg' => trans('site.subscribe_not_valid'),
                    'status' => 'error'
                ];
                return back()->with(['toast' => $toastData]);
            }

            $user = auth()->user();
            $activeSubscribe = Subscribe::getActiveSubscribe($user->id);

            if ($activeSubscribe) {
                $toastData = [
                    'title' => trans('public.request_failed'),
                    'msg' => trans('site.you_have_active_subscribe'),
                    'status' => 'error'
                ];
                return back()->with(['toast' => $toastData]);
            }

            $financialSettings = getFinancialSettings();
            $tax = $financialSettings['tax'] ?? 0;

            $amount = $subscribe->getPrice();
            $amount = $amount > 0 ? $amount : 0;

            $taxPrice = $tax ? $amount * $tax / 100 : 0;

            $order = Order::create([
                "user_id" => $user->id,
                "status" => Order::$pending,
                'tax' => $taxPrice,
                'commission' => 0,
                "amount" => $amount,
                "total_amount" => $amount + $taxPrice,
                "created_at" => time(),
            ]);

            $orderItem = OrderItem::updateOrCreate([
                'user_id' => $user->id,
                'order_id' => $order->id,
                'subscribe_id' => $subscribe->id,
            ], [
                'amount' => $order->amount,
                'total_amount' => $amount + $taxPrice,
                'tax' => $tax,
                'tax_price' => $taxPrice,
                'commission' => 0,
                'commission_price' => 0,
                'created_at' => time(),
            ]);

            if ($amount > 0) {

                $razorpay = false;
                foreach ($paymentChannels as $paymentChannel) {
                    if ($paymentChannel->class_name == 'Razorpay') {
                        $razorpay = true;
                    }
                }

                $data = [
                    'pageTitle' => trans('public.checkout_page_title'),
                    'paymentChannels' => $paymentChannels,
                    'total' => $order->total_amount,
                    'order' => $order,
                    'count' => 1,
                    'userCharge' => $user->getAccountingCharge(),
                    'razorpay' => $razorpay
                ];

                return view(getTemplate() . '.cart.payment', $data);
            }

            Sale::createSales($orderItem, Sale::$credit);

            $toastData = [
                'title' => 'public.request_success',
                'msg' => trans('update.success_pay_msg_for_free_subscribe'),
                'status' => 'success'
            ];
            return back()->with(['toast' => $toastData]);
        } catch (\Exception $e) {
            \Log::error('pay error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }
}
