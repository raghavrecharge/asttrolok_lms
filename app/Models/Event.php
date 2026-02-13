<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    protected $fillable = [
        'title',
        'description',
        'price',
        'category',
        'max_participants',
        'event_date',
        'registration_deadline',
        'status',
        'location',
        'image'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'event_date' => 'datetime',
        'registration_deadline' => 'datetime',
    ];

    public function paymentLink()
    {
        return $this->hasOne(EventPaymentLink::class);
    }

    public function payments()
    {
        return $this->hasMany(EventPayment::class);
    }

    public function registrations()
    {
        return $this->hasMany(EventRegistration::class);
    }

    public function paidRegistrations()
    {
        return $this->registrations()->where('payment_status', 'paid');
    }

    public function getTotalRevenueAttribute()
    {
        return $this->payments()->where('status', 'paid')->sum('amount');
    }

    public function getRegisteredCountAttribute()
    {
        return $this->paidRegistrations()->count();
    }

    public function getRemainingSlotsAttribute()
    {
        return $this->max_participants - $this->registered_count;
    }

    public function isActive()
    {
        return $this->status === 'active' && $this->registration_deadline > now();
    }

    public function isExpired()
    {
        return $this->registration_deadline <= now();
    }
}
