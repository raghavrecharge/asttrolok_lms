<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EventRegistration extends Model
{
    protected $fillable = [
        'event_id',
        'user_id',
        'payment_id',
        'name',
        'email',
        'phone',
        'payment_status',
        'notes'
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function user()
    {
        return $this->belongsTo(\App\User::class);
    }

    public function payment()
    {
        return $this->belongsTo(EventPayment::class, 'payment_id');
    }

    public function isPaid()
    {
        return $this->payment_status === 'paid';
    }

    public function isPending()
    {
        return $this->payment_status === 'pending';
    }

    public function isFailed()
    {
        return $this->payment_status === 'failed';
    }

    public function getFormattedStatusAttribute()
    {
        return match($this->payment_status) {
            'paid' => '✅ Paid',
            'pending' => '⏳ Pending',
            'failed' => '❌ Failed',
            default => $this->payment_status
        };
    }
}
