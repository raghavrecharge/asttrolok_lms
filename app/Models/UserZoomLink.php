<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserZoomLink extends Model
{
    protected $table = 'zoom_id_pwd';
    public $timestamps = false;
    protected $dateFormat = 'U';
    protected $guarded = ['id'];

    public function user()
    {
        return $this->belongsTo('App\User', 'user_id', 'id');
    }
}
