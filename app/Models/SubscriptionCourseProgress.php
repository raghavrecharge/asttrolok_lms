<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubscriptionCourseProgress extends Model
{
    protected $table = 'subscription_course_progress';
    public $timestamps = false;
    protected $dateFormat = 'U';
    protected $guarded = ['id'];
}
