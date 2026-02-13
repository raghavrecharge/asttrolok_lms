<?php

namespace App\Models\PaymentEngine;

use App\User;
use Illuminate\Database\Eloquent\Model;

class UpeSubscription extends Model
{
    protected $table = 'upe_subscriptions';

    protected $fillable = [
        'sale_id',
        'user_id',
        'product_id',
        'billing_amount',
        'billing_interval',
        'trial_ends_at',
        'current_period_start',
        'current_period_end',
        'grace_period_days',
        'status',
        'cancelled_at',
        'gateway_subscription_id',
    ];

    protected $casts = [
        'billing_amount' => 'decimal:2',
        'trial_ends_at' => 'datetime',
        'current_period_start' => 'datetime',
        'current_period_end' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    // ── Relationships ──

    public function sale()
    {
        return $this->belongsTo(UpeSale::class, 'sale_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function product()
    {
        return $this->belongsTo(UpeProduct::class, 'product_id');
    }

    public function cycles()
    {
        return $this->hasMany(UpeSubscriptionCycle::class, 'subscription_id')->orderBy('cycle_number');
    }

    // ── Scopes ──

    public function scopeActive($query)
    {
        return $query->whereIn('status', ['trial', 'active']);
    }

    public function scopeDueBilling($query)
    {
        return $query->whereIn('status', ['trial', 'active', 'past_due', 'grace'])
            ->where('current_period_end', '<=', now());
    }

    // ── State Checks ──

    public function isInTrial(): bool
    {
        return $this->status === 'trial' && $this->trial_ends_at && $this->trial_ends_at->isFuture();
    }

    public function isActive(): bool
    {
        return in_array($this->status, ['trial', 'active']);
    }

    public function isInGrace(): bool
    {
        if ($this->status !== 'grace') {
            return false;
        }
        $graceEnd = $this->current_period_end->addDays($this->grace_period_days);
        return $graceEnd->isFuture();
    }

    public function hasAccess(): bool
    {
        return $this->isActive() || $this->isInGrace();
    }

    public function graceExpiresAt(): ?\Carbon\Carbon
    {
        if ($this->current_period_end === null) {
            return null;
        }
        return $this->current_period_end->copy()->addDays($this->grace_period_days);
    }

    public function intervalDays(): int
    {
        return match ($this->billing_interval) {
            'monthly' => 30,
            'quarterly' => 90,
            'yearly' => 365,
            default => 30,
        };
    }
}
