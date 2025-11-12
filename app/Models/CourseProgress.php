<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CourseProgress extends Model
{
    protected $table = 'course_progress';
    public $timestamps = false;
    protected $dateFormat = 'U';
    protected $guarded = ['id'];
}
