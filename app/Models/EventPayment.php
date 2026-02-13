<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EventPayment extends Model
{
    protected $fillable = [
        'event_id',
        'user_id',
        'razorpay_payment_id',
        'razorpay_order_id',
        'razorpay_signature',
        'amount',
        'status',
        'payment_method',
        'payment_response',
        'email',
        'phone',
        'name'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_response' => 'array',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function user()
    {
        return $this->belongsTo(\App\User::class);
    }

    public function registration()
    {
        return $this->hasOne(EventRegistration::class, 'payment_id');
    }

    public function isPaid()
    {
        return $this->status === 'paid';
    }

    public function isPending()
    {
        return $this->status === 'pending';
    }

    public function isFailed()
    {
        return $this->status === 'failed';
    }

    public function getFormattedStatusAttribute()
    {
        return match($this->status) {
            'paid' => '✅ Paid',
            'pending' => '⏳ Pending',
            'failed' => '❌ Failed',
            'refunded' => '💰 Refunded',
            default => $this->status
        };
    }

    public function getFormattedAmountAttribute()
    {
        return '₹' . number_format($this->amount, 2);
    }
}
