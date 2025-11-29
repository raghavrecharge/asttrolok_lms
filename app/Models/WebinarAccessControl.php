<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WebinarAccessControl extends Model
{
    protected $table = "webinar_access_control";
    public $timestamps = false;
    protected $guarded = ['id'];

}
