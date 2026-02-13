<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class EventPaymentLink extends Model
{
    protected $fillable = [
        'event_id',
        'link_token',
        'payment_link',
        'status',
        'expires_at',
        'click_count'
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function isActive()
    {
        return $this->status === 'active' && $this->expires_at > now();
    }

    public function isExpired()
    {
        return $this->expires_at <= now();
    }

    public function generatePaymentLink()
    {
        $token = Str::random(32);
        $this->link_token = $token;
        $this->payment_link = url("/events/pay/{$this->event_id}/{$token}");
        $this->save();
        
        return $this->payment_link;
    }

    public function incrementClickCount()
    {
        $this->increment('click_count');
    }

    public function getFormattedStatusAttribute()
    {
        return match($this->status) {
            'active' => '✅ Active',
            'expired' => '❌ Expired',
            'disabled' => '🚫 Disabled',
            default => $this->status
        };
    }
}
