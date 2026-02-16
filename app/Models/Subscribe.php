<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;

class Subscribe extends Model implements TranslatableContract
{
    use Translatable;

    protected $table = 'subscribes';
    public $timestamps = false;
    protected $dateFormat = 'U';
    protected $guarded = ['id'];

    public $translatedAttributes = ['title', 'description'];

    public function getTitleAttribute()
    {
        return getTranslateAttributeValue($this, 'title');
    }

    public function getDescriptionAttribute()
    {
        return getTranslateAttributeValue($this, 'description');
    }

    public function sales()
    {
        return $this->hasMany('App\Models\Sale', 'subscribe_id', 'id');
    }

    public function uses()
    {
        return $this->hasMany('App\Models\SubscribeUse', 'subscribe_id', 'id');
    }

    public static function getActiveSubscribe($userId)
    {
        // Check UPE subscriptions for active access
        $upeSubscription = \App\Models\PaymentEngine\UpeSubscription::where('user_id', $userId)
            ->whereIn('status', ['active', 'trial', 'grace'])
            ->with('product')
            ->orderByDesc('id')
            ->first();

        if ($upeSubscription && $upeSubscription->product) {
            // Find the matching legacy subscribe model by external_id
            $subscribe = self::find($upeSubscription->product->external_id);

            if ($subscribe) {
                $subscribe->used_count = 0; // UPE handles usage tracking
                return $subscribe;
            }
        }

        return null;
    }

    public static function getDayOfUse($userId)
    {
        $upeSubscription = \App\Models\PaymentEngine\UpeSubscription::where('user_id', $userId)
            ->whereIn('status', ['active', 'trial', 'grace'])
            ->orderByDesc('id')
            ->first();

        if ($upeSubscription && $upeSubscription->current_period_start) {
            return (int)diffTimestampDay(time(), $upeSubscription->current_period_start->timestamp);
        }

        return 0;
    }

    public function activeSpecialOffer()
    {
        $activeSpecialOffer = SpecialOffer::where('subscribe_id', $this->id)
            ->where('status', SpecialOffer::$active)
            ->where('from_date', '<', time())
            ->where('to_date', '>', time())
            ->first();

        return $activeSpecialOffer ?? false;
    }

    public function getPrice()
    {
        $price = $this->price;

        $specialOffer = $this->activeSpecialOffer();
        if (!empty($specialOffer)) {
            $price = $price - ($price * $specialOffer->percent / 100);
        }

        return $price;
    }
}
