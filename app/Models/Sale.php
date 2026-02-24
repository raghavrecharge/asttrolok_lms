<?php

namespace App\Models;

use App\Mixins\RegistrationBonus\RegistrationBonusAccounting;
use Illuminate\Database\Eloquent\Model;
use App\Vbout\VboutService;
use App\Models\OrderAddress;

class Sale extends Model
{
    /**
     * Check if the user has bought this sale item.
     *
     * @param \App\User|null $user
     * @return bool
     */
    public function checkUserHasBought($user = null)
    {
        if (empty($user) && auth()->check()) {
            $user = auth()->user();
        }
        if (!empty($user)) {
            // Check if this sale belongs to the user, is not refunded, and access is allowed
            return $this->buyer_id == $user->id && is_null($this->refund_at) && ($this->access_to_purchased_item ?? true);
        }
        return false;
    }
     protected $vboutService;
    public static $webinar = 'webinar';
    public static $meeting = 'meeting';
    public static $subscribe = 'subscribe';
    public static $promotion = 'promotion';
    public static $registrationPackage = 'registration_package';
    public static $product = 'product';
    public static $bundle = 'bundle';
    public static $gift = 'gift';
    public static $installmentPayment = 'installment_payment';

    public static $credit = 'credit';
    public static $paymentChannel = 'payment_channel';

    public $timestamps = false;

    protected $guarded = ['id'];

    public function webinar()
    {
        return $this->belongsTo('App\Models\Webinar', 'webinar_id', 'id');
    }

    public function installment()
    {
        return $this->belongsTo('App\Models\InstallmentOrderPayment', 'installment_payment_id', 'id');
    }
    public function installmentorder()
    {
        return $this->belongsTo('App\Models\InstallmentOrder', 'installment_order_id', 'id');
    }

    public function supportRequest()
    {
        return $this->belongsTo('App\Models\NewSupportForAsttrolok', 'support_request_id', 'id');
    }

    public function grantedByAdmin()
    {
        return $this->belongsTo('App\User', 'granted_by_admin_id', 'id');
    }

    public function bundle()
    {
        return $this->belongsTo('App\Models\Bundle', 'bundle_id', 'id');
    }

    public function buyer()
    {
        return $this->belongsTo('App\User', 'buyer_id', 'id');
    }

    public function seller()
    {
        return $this->belongsTo('App\User', 'seller_id', 'id');
    }

    public function meeting()
    {
        return $this->belongsTo('App\Models\Meeting', 'meeting_id', 'id');
    }

    public function subscribe()
    {
        return $this->belongsTo('App\Models\Subscribe', 'subscribe_id', 'id');
    }
    public function subscription()
    {
        return $this->belongsTo('App\Models\Subscription', 'subscription_id', 'id');
    }

    public function promotion()
    {
        return $this->belongsTo('App\Models\Promotion', 'promotion_id', 'id');
    }

    public function registrationPackage()
    {
        return $this->belongsTo('App\Models\RegistrationPackage', 'registration_package_id', 'id');
    }

    public function order()
    {
        return $this->belongsTo('App\Models\Order', 'order_id', 'id');
    }

    public function ticket()
    {
        return $this->belongsTo('App\Models\Ticket', 'ticket_id', 'id');
    }

    public function saleLog()
    {
        return $this->hasOne('App\Models\SaleLog', 'sale_id', 'id');
    }

    public function productOrder()
    {
        return $this->belongsTo('App\Models\ProductOrder', 'product_order_id', 'id');
    }

    public function gift()
    {
        return $this->belongsTo('App\Models\Gift', 'gift_id', 'id');
    }

    public function installmentOrderPayment()
    {
        return $this->belongsTo('App\Models\InstallmentOrderPayment', 'installment_payment_id', 'id');
    }

    public static function createSales($orderItem, $payment_method)
    {
        $orderType = Order::$webinar;
        if (!empty($orderItem->reserve_meeting_id)) {
            $orderType = Order::$meeting;
        } elseif (!empty($orderItem->subscribe_id)) {
            $orderType = Order::$subscribe;
        } elseif (!empty($orderItem->promotion_id)) {
            $orderType = Order::$promotion;
        } elseif (!empty($orderItem->registration_package_id)) {
            $orderType = Order::$registrationPackage;
        } elseif (!empty($orderItem->product_id)) {
            $orderType = Order::$product;
        } elseif (!empty($orderItem->bundle_id)) {
            $orderType = Order::$bundle;
        } elseif (!empty($orderItem->installment_payment_id)) {
            $orderType = Order::$installmentPayment;
        }elseif (!empty($orderItem->subscription_id)) {
            $orderType = Order::$subscription;
        }

        if (!empty($orderItem->gift_id)) {
            $orderType = Order::$gift;
        }

        $seller_id = OrderItem::getSeller($orderItem);

        $sale = Sale::create([
            'buyer_id' => $orderItem->user_id,
            'seller_id' => $seller_id,
            'order_id' => $orderItem->order_id,
            'webinar_id' => (empty($orderItem->gift_id) and !empty($orderItem->webinar_id)) ? $orderItem->webinar_id : null,
            'bundle_id' => (empty($orderItem->gift_id) and !empty($orderItem->bundle_id)) ? $orderItem->bundle_id : null,
            'meeting_id' => !empty($orderItem->reserve_meeting_id) ? $orderItem->reserveMeeting->meeting_id : null,
            'meeting_time_id' => !empty($orderItem->reserveMeeting) ? $orderItem->reserveMeeting->meeting_time_id : null,
            'subscribe_id' => $orderItem->subscribe_id,
            'subscription_id' => $orderItem->subscription_id ?? null,
            'promotion_id' => $orderItem->promotion_id,
            'registration_package_id' => $orderItem->registration_package_id,
            'product_order_id' => (!empty($orderItem->product_order_id)) ? $orderItem->product_order_id : null,
            'installment_payment_id' => $orderItem->installment_payment_id ?? null,
            'status' => $orderItem->installment_type ?? null,
            'gift_id' => $orderItem->gift_id ?? null,
            'type' => $orderType,
            'payment_method' => $payment_method,
            'amount' => $orderItem->amount,
            'tax' => $orderItem->tax_price,
            'via_payment' => $orderItem->via_payment ?? null,
            'commission' => $orderItem->commission_price,
            'discount' => $orderItem->discount,
            'total_amount' => $orderItem->total_amount,
            'product_delivery_fee' => $orderItem->product_delivery_fee,
            'created_at' => time(),
        ]);

        // UPE dual-write: create UPE records for course-access purchases
        try {
            static::createUpeRecordsForSale($sale, $orderItem);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning('UPE dual-write failed in createSales, legacy sale preserved', [
                'sale_id' => $sale->id, 'error' => $e->getMessage(),
            ]);
        }

if(!empty($orderItem->webinar_id)){
    if($orderItem->webinar_id=='2033'){
        $u_id=$orderItem->user_id;
        $u_name = $orderItem->user->full_name;
        $u_mobile = $orderItem->user->mobile;
        $u_email = $orderItem->user->email;
        $webinar_title = $orderItem->webinar->title;
        $price=$orderItem->total_amount;
        $date=time();
	 date_default_timezone_set('Asia/Kolkata');
	  $webhookurl='https://connect.pabbly.com/workflow/sendwebhookdata/IjU3NjUwNTZmMDYzMDA0MzE1MjZmNTUzMzUxMzci_pc';

$webhookdata = [
  'u_id' => $u_id,
  'u_name' => $u_name,
  'u_mobile' => $u_mobile,
  'u_email' => $u_email,
  'webinar_title' => $webinar_title,
  'price' => $price,
  'dateTime' => date("Y/m/d H:i")

];

$webhookcurl = curl_init($webhookurl);

curl_setopt($webhookcurl, CURLOPT_RETURNTRANSFER, true);

curl_setopt($webhookcurl, CURLOPT_POST, true);

curl_setopt($webhookcurl, CURLOPT_POSTFIELDS,  json_encode($webhookdata));

$webhookresponse = curl_exec($webhookcurl);

curl_close($webhookcurl);

    }else{
        $u_id=$orderItem->user_id;
        $u_name = $orderItem->user->full_name;
        $u_mobile = $orderItem->user->mobile;
        $u_email = $orderItem->user->email;
        $webinar_title = $orderItem->webinar->title;
        $price=$orderItem->total_amount;
        $date=time();
	 date_default_timezone_set('Asia/Kolkata');
	  $webhookurl='https://connect.pabbly.com/workflow/sendwebhookdata/IjU3NjUwNTZmMDYzZTA0MzU1MjZhNTUzYzUxMzYi_pc';

$webhookdata = [
  'u_id' => $u_id,
  'u_name' => $u_name,
  'u_mobile' => $u_mobile,
  'u_email' => $u_email,
  'webinar_title' => $webinar_title,
  'price' => $price,
  'dateTime' => date("Y/m/d H:i")

];

$webhookcurl = curl_init($webhookurl);

curl_setopt($webhookcurl, CURLOPT_RETURNTRANSFER, true);

curl_setopt($webhookcurl, CURLOPT_POST, true);

curl_setopt($webhookcurl, CURLOPT_POSTFIELDS,  json_encode($webhookdata));

$webhookresponse = curl_exec($webhookcurl);

curl_close($webhookcurl);

    }
}

        self::handleSaleNotifications($orderItem, $seller_id);

        if (!empty($orderItem->product_id)) {
            $buyStoreReward = RewardAccounting::calculateScore(Reward::BUY_STORE_PRODUCT, $orderItem->total_amount);
            RewardAccounting::makeRewardAccounting($orderItem->user_id, $buyStoreReward, Reward::BUY_STORE_PRODUCT, $orderItem->product_id);
        }

        $buyReward = RewardAccounting::calculateScore(Reward::BUY, $orderItem->total_amount);
        RewardAccounting::makeRewardAccounting($orderItem->user_id, $buyReward, Reward::BUY);

        $registrationBonusAccounting = new RegistrationBonusAccounting();
        $registrationBonusAccounting->checkBonusAfterSale($orderItem->user_id);

        return $sale;
    }

    private static function handleSaleNotifications($orderItem, $seller_id)
    {
        $title = '';
        if (!empty($orderItem->webinar_id)) {
            $title = $orderItem->webinar->title;
        } elseif (!empty($orderItem->bundle_id)) {
            $title = $orderItem->bundle->title;
        } else if (!empty($orderItem->meeting_id)) {
            $title = trans('meeting.reservation_appointment');
        } else if (!empty($orderItem->subscribe_id)) {
            $title = $orderItem->subscribe->title . ' ' . trans('financial.subscribe');
        } else if (!empty($orderItem->promotion_id)) {
            $title = $orderItem->promotion->title . ' ' . trans('panel.promotion');
        } else if (!empty($orderItem->registration_package_id)) {
            $title = $orderItem->registrationPackage->title . ' ' . trans('update.registration_package');
        } else if (!empty($orderItem->product_id)) {
            $title = $orderItem->product->title;
        } else if (!empty($orderItem->installment_payment_id)) {
            $title = ($orderItem->installmentPayment->type == 'upfront') ? trans('update.installment_upfront') : trans('update.installment');
        }

        if (!empty($orderItem->gift_id) and !empty($orderItem->gift)) {
            $title .= ' (' . trans('update.a_gift_for_name_on_date_without_bold', ['name' => $orderItem->gift->name, 'date' => dateTimeFormat($orderItem->gift->date, 'j M Y H:i')]) . ')';
        }

        if ($orderItem->reserve_meeting_id) {
            $reserveMeeting = $orderItem->reserveMeeting;

            $notifyOptions = [
                '[amount]' => handlePrice($orderItem->amount),
                '[u.name]' => $orderItem->user->full_name,
                '[time.date]' => $reserveMeeting->day . ' ' . $reserveMeeting->time,
            ];
            sendNotification('new_appointment', $notifyOptions, $orderItem->user_id);
            sendNotification('new_appointment', $notifyOptions, $reserveMeeting->meeting->creator_id);
        } elseif (!empty($orderItem->product_id)) {
            $notifyOptions = [
                '[p.title]' => $title,
                '[amount]' => handlePrice($orderItem->total_amount),
                '[u.name]' => $orderItem->user->full_name,
            ];

            sendNotification('product_new_sale', $notifyOptions, $seller_id);
            sendNotification('product_new_purchase', $notifyOptions, $orderItem->user_id);
            sendNotification('new_store_order', $notifyOptions, 1);
        } elseif (!empty($orderItem->installment_payment_id)) {

        } else {
            $notifyOptions = [
                '[c.title]' => $title,
            ];

            sendNotification('new_sales', $notifyOptions, $seller_id);
            sendNotification('new_purchase', $notifyOptions, $orderItem->user_id);
        }

        if (!empty($orderItem->webinar_id)) {
            $notifyOptions = [
                '[u.name]' => $orderItem->user->full_name,
                '[c.title]' => $title,
                '[amount]' => handlePrice($orderItem->total_amount),
                '[time.date]' => dateTimeFormat(time(), 'j M Y H:i'),
            ];
            sendNotification("new_course_enrollment", $notifyOptions, 1);

        }

        if (!empty($orderItem->subscribe_id)) {
            $notifyOptions = [
                '[u.name]' => $orderItem->user->full_name,
                '[item_title]' => $orderItem->subscribe->title,
                '[amount]' => handlePrice($orderItem->total_amount),
            ];
            sendNotification("subscription_plan_activated", $notifyOptions, 1);
        }
    }

    public function getIncomeItem()
    {
        if ($this->payment_method == self::$subscribe) {
            $used = SubscribeUse::where('webinar_id', $this->webinar_id)
                ->where('sale_id', $this->id)
                ->first();

            if (!empty($used)) {
                $subscribe = $used->subscribe;

                $financialSettings = getFinancialSettings();
                $commission = $financialSettings['commission'] ?? 0;

                $pricePerSubscribe = $subscribe->price / $subscribe->usable_count;
                $commissionPrice = $commission ? $pricePerSubscribe * $commission / 100 : 0;

                return round($pricePerSubscribe - $commissionPrice, 2);
            }
        }

        $income = $this->total_amount - $this->tax - $this->commission;
        return round($income, 2);
    }

    public function getUsedSubscribe($user_id, $itemId, $itemName = 'webinar_id')
    {
        $subscribe = null;
        $use = SubscribeUse::where('sale_id', $this->id)
            ->where($itemName, $itemId)
            ->where('user_id', $user_id)
            ->first();

        if (!empty($use)) {
            $subscribe = Subscribe::where('id', $use->subscribe_id)->first();

            if (!empty($subscribe)) {
                $subscribe->installment_order_id = $use->installment_order_id;
            }
        }

        return $subscribe;
    }

    public function checkExpiredPurchaseWithSubscribe($user_id, $itemId, $itemName = 'webinar_id')
    {
        $result = true;

        $subscribe = $this->getUsedSubscribe($user_id, $itemId, $itemName);

        if (!empty($subscribe)) {
            $subscribeSale = self::where('buyer_id', $user_id)
                ->where('type', self::$subscribe)
                ->where('subscribe_id', $subscribe->id)
                ->whereNull('refund_at')
                ->latest('created_at')
                ->first();

            if (!empty($subscribeSale)) {
                $usedDays = (int)diffTimestampDay(time(), $subscribeSale->created_at);

                if ($usedDays <= $subscribe->days) {
                    $result = false;
                }
            }
        }

        return $result;
    }
    
    /**
     * Get webinar title safely
     */
    public function getWebinarTitle()
    {
        return $this->webinar ? $this->webinar->title : 'Course not available';
    }
    
    /**
     * Get formatted total amount
     */
    public function getFormattedTotalAmount()
    {
        return handlePrice($this->total_amount);
    }
    
    /**
     * Get formatted amount
     */
    public function getFormattedAmount()
    {
        return handlePrice($this->amount);
    }
    
    /**
     * Get formatted tax
     */
    public function getFormattedTax()
    {
        return handlePrice($this->tax);
    }
    
    /**
     * Get formatted discount
     */
    public function getFormattedDiscount()
    {
        return handlePrice($this->discount);
    }
    
    /**
     * Get formatted commission
     */
    public function getFormattedCommission()
    {
        return handlePrice($this->commission);
    }
    
    /**
     * Check if sale is refunded
     */
    public function isRefunded()
    {
        return !is_null($this->refund_at);
    }
    
    /**
     * Get refund status label
     */
    public function getRefundStatusLabel()
    {
        if ($this->isRefunded()) {
            return 'Refunded';
        }
        return 'Active';
    }
    
    /**
     * Get refund status badge class
     */
    public function getRefundStatusBadgeClass()
    {
        if ($this->isRefunded()) {
            return 'danger';
        }
        return 'success';
    }
    
    /**
     * Get created at formatted date
     */
    public function getCreatedAtFormatted($format = 'j M Y H:i')
    {
        return dateTimeFormat($this->created_at, $format);
    }
    
    /**
     * Get refund at formatted date
     */
    public function getRefundAtFormatted($format = 'j M Y H:i')
    {
        return $this->refund_at ? dateTimeFormat($this->refund_at, $format) : null;
    }
    
    public function orderAddress()
    {
        return $this->hasOne(OrderAddress::class, 'order_id', 'order_id');
    }

    /**
     * UPE dual-write: create UPE records when a legacy Sale is created via createSales().
     * Covers webinar, bundle, subscription, and installment purchases.
     * Meetings, products, promotions, registration packages are NOT UPE-managed.
     */
    private static function createUpeRecordsForSale($sale, $orderItem)
    {
        $userId = $orderItem->user_id;
        $amount = $orderItem->total_amount;

        // Determine what kind of purchase this is
        if (!empty($orderItem->webinar_id) && empty($orderItem->installment_payment_id) && empty($orderItem->gift_id)) {
            // Direct webinar purchase
            $webinar = \App\Models\Webinar::find($orderItem->webinar_id);
            if (!$webinar) return;

            $productType = match ($webinar->type ?? 'course') {
                'webinar' => 'webinar',
                default => 'course_video',
            };

            $upeProduct = \App\Models\PaymentEngine\UpeProduct::firstOrCreate(
                ['external_id' => $webinar->id, 'product_type' => $productType],
                ['name' => $webinar->slug ?? "webinar-{$webinar->id}", 'base_fee' => $amount, 'validity_days' => $webinar->access_days, 'status' => 'active']
            );

            $existingUpeSale = \App\Models\PaymentEngine\UpeSale::where('user_id', $userId)
                ->where('product_id', $upeProduct->id)
                ->whereIn('status', ['active', 'partially_refunded'])
                ->first();

            if ($existingUpeSale) return;

            $validFrom = now();
            $validUntil = $webinar->access_days ? $validFrom->copy()->addDays($webinar->access_days) : null;

            $upeSale = \App\Models\PaymentEngine\UpeSale::create([
                'uuid' => (string) \Illuminate\Support\Str::uuid(),
                'user_id' => $userId,
                'product_id' => $upeProduct->id,
                'sale_type' => 'paid',
                'pricing_mode' => 'full',
                'base_fee_snapshot' => $amount,
                'status' => 'active',
                'valid_from' => $validFrom,
                'valid_until' => $validUntil,
                'metadata' => json_encode(['legacy_sale_id' => $sale->id, 'source' => 'createSales_hook']),
            ]);

            app(\App\Services\PaymentEngine\PaymentLedgerService::class)->append(
                $upeSale->id,
                \App\Models\PaymentEngine\UpeLedgerEntry::TYPE_PAYMENT,
                \App\Models\PaymentEngine\UpeLedgerEntry::DIR_CREDIT,
                $amount,
                $sale->payment_method ?? 'razorpay',
                null, null, null, null,
                "Payment via createSales for webinar {$webinar->id}",
                null,
                "legacy_sale_{$sale->id}"
            );

            \Illuminate\Support\Facades\Cache::forget(\App\Services\PaymentEngine\AccessEngine::CACHE_PREFIX . "{$userId}_{$upeProduct->id}");

        } elseif (!empty($orderItem->bundle_id) && empty($orderItem->gift_id)) {
            // Bundle purchase
            $bundle = \App\Models\Bundle::find($orderItem->bundle_id);
            if (!$bundle) return;

            $upeProduct = \App\Models\PaymentEngine\UpeProduct::firstOrCreate(
                ['external_id' => $bundle->id, 'product_type' => 'bundle'],
                ['name' => $bundle->slug ?? "bundle-{$bundle->id}", 'base_fee' => $amount, 'validity_days' => $bundle->access_days, 'status' => 'active']
            );

            $existingUpeSale = \App\Models\PaymentEngine\UpeSale::where('user_id', $userId)
                ->where('product_id', $upeProduct->id)
                ->whereIn('status', ['active', 'partially_refunded'])
                ->first();

            if ($existingUpeSale) return;

            $validFrom = now();
            $validUntil = $bundle->access_days ? $validFrom->copy()->addDays($bundle->access_days) : null;

            $upeSale = \App\Models\PaymentEngine\UpeSale::create([
                'uuid' => (string) \Illuminate\Support\Str::uuid(),
                'user_id' => $userId,
                'product_id' => $upeProduct->id,
                'sale_type' => 'paid',
                'pricing_mode' => 'full',
                'base_fee_snapshot' => $amount,
                'status' => 'active',
                'valid_from' => $validFrom,
                'valid_until' => $validUntil,
                'metadata' => json_encode(['legacy_sale_id' => $sale->id, 'source' => 'createSales_hook']),
            ]);

            app(\App\Services\PaymentEngine\PaymentLedgerService::class)->append(
                $upeSale->id,
                \App\Models\PaymentEngine\UpeLedgerEntry::TYPE_PAYMENT,
                \App\Models\PaymentEngine\UpeLedgerEntry::DIR_CREDIT,
                $amount,
                $sale->payment_method ?? 'razorpay',
                null, null, null, null,
                "Payment via createSales for bundle {$bundle->id}",
                null,
                "legacy_sale_{$sale->id}"
            );

            \Illuminate\Support\Facades\Cache::forget(\App\Services\PaymentEngine\AccessEngine::CACHE_PREFIX . "{$userId}_{$upeProduct->id}");

        } elseif (!empty($orderItem->subscription_id)) {
            // Subscription purchase
            $subscription = \App\Models\Subscription::find($orderItem->subscription_id);
            if (!$subscription) return;

            $upeProduct = \App\Models\PaymentEngine\UpeProduct::firstOrCreate(
                ['external_id' => $subscription->id, 'product_type' => 'subscription'],
                ['name' => $subscription->slug ?? "subscription-{$subscription->id}", 'base_fee' => $amount, 'validity_days' => $subscription->access_days, 'status' => 'active']
            );

            $validFrom = now();
            $validUntil = $subscription->access_days ? $validFrom->copy()->addDays($subscription->access_days) : $validFrom->copy()->addDays(30);

            $upeSale = \App\Models\PaymentEngine\UpeSale::create([
                'uuid' => (string) \Illuminate\Support\Str::uuid(),
                'user_id' => $userId,
                'product_id' => $upeProduct->id,
                'sale_type' => 'paid',
                'pricing_mode' => 'subscription',
                'base_fee_snapshot' => $amount,
                'status' => 'active',
                'valid_from' => $validFrom,
                'valid_until' => $validUntil,
                'metadata' => json_encode(['legacy_sale_id' => $sale->id, 'source' => 'createSales_hook']),
            ]);

            // Create or update UPE subscription
            $existingSub = \App\Models\PaymentEngine\UpeSubscription::where('user_id', $userId)
                ->where('product_id', $upeProduct->id)
                ->whereIn('status', ['active', 'trial', 'grace'])
                ->first();

            if ($existingSub) {
                $newEnd = $existingSub->current_period_end->copy()->addDays($subscription->access_days ?? 30);
                $existingSub->update(['current_period_end' => $newEnd, 'status' => 'active']);
            } else {
                \App\Models\PaymentEngine\UpeSubscription::create([
                    'user_id' => $userId,
                    'product_id' => $upeProduct->id,
                    'sale_id' => $upeSale->id,
                    'status' => 'active',
                    'billing_interval' => 'monthly',
                    'billing_amount' => $amount,
                    'current_period_start' => $validFrom,
                    'current_period_end' => $validUntil,
                    'grace_period_days' => 3,
                ]);
            }

            app(\App\Services\PaymentEngine\PaymentLedgerService::class)->append(
                $upeSale->id,
                \App\Models\PaymentEngine\UpeLedgerEntry::TYPE_PAYMENT,
                \App\Models\PaymentEngine\UpeLedgerEntry::DIR_CREDIT,
                $amount,
                $sale->payment_method ?? 'razorpay',
                null, null, null, null,
                "Subscription payment via createSales for {$subscription->id}",
                null,
                "legacy_sale_{$sale->id}"
            );

            \Illuminate\Support\Facades\Cache::forget(\App\Services\PaymentEngine\AccessEngine::CACHE_PREFIX . "{$userId}_{$upeProduct->id}");

        } elseif (!empty($orderItem->installment_payment_id)) {
            // Installment payment
            $installmentPayment = \App\Models\InstallmentOrderPayment::find($orderItem->installment_payment_id);
            if (!$installmentPayment || !$installmentPayment->installmentOrder) return;

            $installmentOrder = $installmentPayment->installmentOrder;
            $webinarId = $installmentOrder->webinar_id;
            $webinar = \App\Models\Webinar::find($webinarId);
            if (!$webinar) return;

            $productType = match ($webinar->type ?? 'course') {
                'webinar' => 'webinar',
                default => 'course_video',
            };

            $upeProduct = \App\Models\PaymentEngine\UpeProduct::firstOrCreate(
                ['external_id' => $webinar->id, 'product_type' => $productType],
                ['name' => $webinar->slug ?? "webinar-{$webinar->id}", 'base_fee' => $webinar->price ?? $amount, 'validity_days' => $webinar->access_days, 'status' => 'active']
            );

            // Find existing UPE installment sale or create new one
            $existingSale = \App\Models\PaymentEngine\UpeSale::where('user_id', $userId)
                ->where('product_id', $upeProduct->id)
                ->where('pricing_mode', 'installment')
                ->whereIn('status', ['active', 'pending_payment', 'partially_refunded'])
                ->first();

            if ($existingSale) {
                // If a UPE plan with schedules exists, InstallmentEngine handles the ledger entry
                // with proper schedule references — skip duplicate unlinked entry here.
                $upePlan = \App\Models\PaymentEngine\UpeInstallmentPlan::where('sale_id', $existingSale->id)
                    ->whereIn('status', ['active', 'completed'])
                    ->whereHas('schedules')
                    ->first();

                if (!$upePlan) {
                    // No UPE plan/schedules — add raw ledger entry as fallback
                    app(\App\Services\PaymentEngine\PaymentLedgerService::class)->append(
                        $existingSale->id,
                        \App\Models\PaymentEngine\UpeLedgerEntry::TYPE_INSTALLMENT_PAYMENT,
                        \App\Models\PaymentEngine\UpeLedgerEntry::DIR_CREDIT,
                        $amount,
                        $sale->payment_method ?? 'razorpay',
                        null, null, null, null,
                        "Installment payment via createSales",
                        null,
                        "legacy_sale_{$sale->id}"
                    );
                }
            } else {
                // New installment purchase
                $validFrom = now();
                $validUntil = $webinar->access_days ? $validFrom->copy()->addDays($webinar->access_days) : null;

                $upeSale = \App\Models\PaymentEngine\UpeSale::create([
                    'uuid' => (string) \Illuminate\Support\Str::uuid(),
                    'user_id' => $userId,
                    'product_id' => $upeProduct->id,
                    'sale_type' => 'paid',
                    'pricing_mode' => 'installment',
                    'base_fee_snapshot' => $webinar->price ?? $amount,
                    'status' => 'pending_payment',
                    'valid_from' => $validFrom,
                    'valid_until' => $validUntil,
                    'metadata' => json_encode(['legacy_sale_id' => $sale->id, 'source' => 'createSales_hook']),
                ]);

                $plan = \App\Models\PaymentEngine\UpeInstallmentPlan::create([
                    'sale_id' => $upeSale->id,
                    'total_amount' => $installmentOrder->item_price ?? $webinar->price ?? $amount,
                    'num_installments' => ($installmentOrder->installment->steps_count ?? 1) + 1,
                    'status' => 'active',
                ]);

                \App\Models\PaymentEngine\UpeInstallmentSchedule::create([
                    'plan_id' => $plan->id,
                    'sequence' => 1,
                    'due_date' => now(),
                    'amount_due' => $amount,
                    'status' => 'paid',
                ]);

                app(\App\Services\PaymentEngine\PaymentLedgerService::class)->append(
                    $upeSale->id,
                    \App\Models\PaymentEngine\UpeLedgerEntry::TYPE_INSTALLMENT_PAYMENT,
                    \App\Models\PaymentEngine\UpeLedgerEntry::DIR_CREDIT,
                    $amount,
                    $sale->payment_method ?? 'razorpay',
                    null, null, null, null,
                    "Installment upfront via createSales",
                    null,
                    "legacy_sale_{$sale->id}"
                );
            }

            \Illuminate\Support\Facades\Cache::forget(\App\Services\PaymentEngine\AccessEngine::CACHE_PREFIX . "{$userId}_{$upeProduct->id}");
        }
    }
}
