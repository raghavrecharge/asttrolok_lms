<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubscriptionFilterOption extends Model
{
    protected $table = 'subscription_filter_option';
    public $timestamps = false;

    protected $guarded = ['id'];
}
