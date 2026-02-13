<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceAccess extends Model
{
    protected $table = 'service_access';

    protected $guarded = ['id'];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(\App\User::class, 'user_id');
    }

    public function supportRequest()
    {
        return $this->belongsTo(NewSupportForAsttrolok::class, 'support_request_id');
    }

    public function grantedByUser()
    {
        return $this->belongsTo(\App\User::class, 'granted_by');
    }

    public function isActive()
    {
        return $this->status === 'active' && $this->end_date->isFuture();
    }
}
