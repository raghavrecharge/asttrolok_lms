<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WebinarAccessControl extends Model
{
    protected $table = "webinar_access_control";
    public $timestamps = false;
    protected $guarded = ['id'];

    // public function webinar()
    // {
    //     return $this->belongsTo('App\Models\Webinar', 'webinar_id', 'id');
    // }

    // public function user()
    // {
    //     return $this->belongsTo('App\User', 'user_id', 'id');
    // }
}
