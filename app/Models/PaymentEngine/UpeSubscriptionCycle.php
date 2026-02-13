<?php

namespace App\Models\PaymentEngine;

use Illuminate\Database\Eloquent\Model;

class UpeSubscriptionCycle extends Model
{
    protected $table = 'upe_subscription_cycles';

    protected $fillable = [
        'subscription_id',
        'cycle_number',
        'period_start',
        'period_end',
        'amount',
        'status',
        'ledger_entry_id',
        'attempts',
        'last_attempt_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'period_start' => 'datetime',
        'period_end' => 'datetime',
        'last_attempt_at' => 'datetime',
    ];

    // ── Relationships ──

    public function subscription()
    {
        return $this->belongsTo(UpeSubscription::class, 'subscription_id');
    }

    public function ledgerEntry()
    {
        return $this->belongsTo(UpeLedgerEntry::class, 'ledger_entry_id');
    }

    // ── Helpers ──

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    public function canRetry(int $maxRetries = 3): bool
    {
        return $this->status === 'failed' && $this->attempts < $maxRetries;
    }
}
