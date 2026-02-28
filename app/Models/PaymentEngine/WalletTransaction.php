<?php

namespace App\Models\PaymentEngine;

use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class WalletTransaction extends Model
{
    const UPDATED_AT = null;

    protected $table = 'wallet_transactions';

    protected $fillable = [
        'uuid',
        'wallet_id',
        'user_id',
        'type',
        'amount',
        'balance_after',
        'transaction_type',
        'reference_type',
        'reference_id',
        'gateway_transaction_id',
        'description',
        'metadata',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'balance_after' => 'decimal:2',
        'metadata' => 'array',
        'created_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });

        // Immutable — no updates or deletes
        static::updating(function () {
            throw new \RuntimeException('Wallet transactions are immutable.');
        });

        static::deleting(function () {
            throw new \RuntimeException('Wallet transactions are immutable.');
        });
    }

    // ── Constants ──

    const TYPE_CREDIT = 'credit';
    const TYPE_DEBIT = 'debit';

    const TXN_TOP_UP = 'top_up';
    const TXN_PURCHASE = 'purchase';
    const TXN_REFUND = 'refund';
    const TXN_ADMIN_CREDIT = 'admin_credit';
    const TXN_ADMIN_DEBIT = 'admin_debit';
    const TXN_WALLET_PAYMENT = 'wallet_payment';
    const TXN_GATEWAY_TOPUP = 'gateway_topup';
    const TXN_WALLET_PURCHASE = 'wallet_purchase';
    const TXN_OVERPAYMENT_REFUND = 'overpayment_refund';
    const TXN_COURSE_CHANGE_REFUND = 'course_change_refund';

    // ── Relationships ──

    public function wallet()
    {
        return $this->belongsTo(Wallet::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // ── Scopes ──

    public function scopeCredits($query)
    {
        return $query->where('type', self::TYPE_CREDIT);
    }

    public function scopeDebits($query)
    {
        return $query->where('type', self::TYPE_DEBIT);
    }

    public function scopeOfTransactionType($query, string $txnType)
    {
        return $query->where('transaction_type', $txnType);
    }

    // ── Helpers ──

    public function isCredit(): bool
    {
        return $this->type === self::TYPE_CREDIT;
    }

    public function isDebit(): bool
    {
        return $this->type === self::TYPE_DEBIT;
    }
}
