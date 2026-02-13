<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubscriptionWebinar extends Model
{
    protected $table = 'subscription_webinars';
    public $timestamps = false;
    protected $dateFormat = 'U';
    protected $guarded = ['id'];

    public function product()
    {
        return $this->belongsTo('App\Models\Product', 'product_id', 'id');
    }

    public function webinar()
    {
        return $this->belongsTo('App\Models\Webinar', 'webinar_id', 'id');
    }

    public function bundle()
    {
        return $this->belongsTo('App\Models\Subscription', 'subscription_id', 'id');
    }

}
