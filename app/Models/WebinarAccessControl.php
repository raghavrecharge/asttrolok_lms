<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WebinarAccessControl extends Model
{
    protected $table = "webinar_access_control";
    public $timestamps = true;
    protected $guarded = ['id'];

    protected $casts = [
        'expire' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(\App\User::class, 'user_id');
    }

    public function webinar()
    {
        return $this->belongsTo(Webinar::class, 'webinar_id');
    }

    public function supportRequest()
    {
        return $this->belongsTo(NewSupportForAsttrolok::class, 'support_request_id');
    }

    public function grantedByUser()
    {
        return $this->belongsTo(\App\User::class, 'granted_by');
    }

    public function replacedByAccess()
    {
        return $this->belongsTo(self::class, 'replaced_by');
    }

    public function isActive()
    {
        return $this->status === 'active' && ($this->expire === null || $this->expire->isFuture());
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
