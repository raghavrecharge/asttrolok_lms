<?php

namespace App\Models\PaymentEngine;

use App\User;
use Illuminate\Database\Eloquent\Model;

class UpeReferral extends Model
{
    public $timestamps = false;

    protected $table = 'upe_referrals';

    protected $fillable = [
        'referrer_user_id',
        'referral_code',
        'referred_user_id',
        'referred_sale_id',
        'bonus_type',
        'bonus_amount',
        'bonus_status',
        'bonus_ledger_entry_id',
        'credited_at',
    ];

    protected static function boot()
    {
        parent::boot();

        static::updating(function ($model) {
            if ($model->isDirty('bonus_amount')) {
                throw new \RuntimeException(
                    'Referral bonus_amount is immutable after creation. Corrections must go through the ledger.'
                );
            }
            if ($model->isDirty('referrer_user_id') || $model->isDirty('referral_code')) {
                throw new \RuntimeException(
                    'Referral identity fields (referrer_user_id, referral_code) are immutable.'
                );
            }
        });

        static::deleting(function () {
            throw new \RuntimeException('Referral records are immutable. Deletes are not allowed.');
        });
    }

    protected $casts = [
        'bonus_amount' => 'decimal:2',
        'credited_at' => 'datetime',
        'created_at' => 'datetime',
    ];

    // ── Relationships ──

    public function referrer()
    {
        return $this->belongsTo(User::class, 'referrer_user_id');
    }

    public function referredUser()
    {
        return $this->belongsTo(User::class, 'referred_user_id');
    }

    public function referredSale()
    {
        return $this->belongsTo(UpeSale::class, 'referred_sale_id');
    }

    public function bonusLedgerEntry()
    {
        return $this->belongsTo(UpeLedgerEntry::class, 'bonus_ledger_entry_id');
    }

    // ── Scopes ──

    public function scopePending($query)
    {
        return $query->where('bonus_status', 'pending');
    }

    public function scopeByCode($query, string $code)
    {
        return $query->where('referral_code', $code);
    }

    // ── Helpers ──

    public function isPending(): bool
    {
        return $this->bonus_status === 'pending';
    }

    public function isCredited(): bool
    {
        return $this->bonus_status === 'credited';
    }

    public function isSelfReferral(int $userId): bool
    {
        return $this->referrer_user_id === $userId;
    }
}
