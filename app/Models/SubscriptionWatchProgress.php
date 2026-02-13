<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubscriptionWatchProgress extends Model
{
    protected $table = "subscription_watch_progress";
    public $timestamps = false;
    protected $guarded = ['id'];

}
