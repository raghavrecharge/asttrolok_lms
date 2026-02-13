<?php

namespace App\Models\PaymentEngine;

use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class UpeLedgerEntry extends Model
{
    const UPDATED_AT = null;

    protected $table = 'upe_ledger_entries';

    protected $fillable = [
        'uuid',
        'sale_id',
        'entry_type',
        'direction',
        'amount',
        'currency',
        'payment_method',
        'gateway_transaction_id',
        'gateway_response',
        'reference_type',
        'reference_id',
        'description',
        'processed_by',
        'idempotency_key',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'gateway_response' => 'array',
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

        // Prevent updates — ledger is immutable
        static::updating(function () {
            throw new \RuntimeException('Ledger entries are immutable. Updates are not allowed.');
        });

        // Prevent deletes — ledger is immutable
        static::deleting(function () {
            throw new \RuntimeException('Ledger entries are immutable. Deletes are not allowed.');
        });
    }

    // ── Constants ──

    const TYPE_PAYMENT = 'payment';
    const TYPE_REFUND = 'refund';
    const TYPE_DISCOUNT = 'discount';
    const TYPE_ADJUSTMENT_IN = 'adjustment_in';
    const TYPE_ADJUSTMENT_OUT = 'adjustment_out';
    const TYPE_REFERRAL_BONUS = 'referral_bonus';
    const TYPE_INSTALLMENT_PAYMENT = 'installment_payment';
    const TYPE_SUBSCRIPTION_CHARGE = 'subscription_charge';
    const TYPE_PENALTY = 'penalty';
    const TYPE_WRITE_OFF = 'write_off';

    const DIR_CREDIT = 'credit';
    const DIR_DEBIT = 'debit';

    // ── Relationships ──

    public function sale()
    {
        return $this->belongsTo(UpeSale::class, 'sale_id');
    }

    public function processedByUser()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    // ── Scopes ──

    public function scopeForSale($query, int $saleId)
    {
        return $query->where('sale_id', $saleId);
    }

    public function scopeCredits($query)
    {
        return $query->where('direction', self::DIR_CREDIT);
    }

    public function scopeDebits($query)
    {
        return $query->where('direction', self::DIR_DEBIT);
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('entry_type', $type);
    }

    // ── Helpers ──

    public function isCredit(): bool
    {
        return $this->direction === self::DIR_CREDIT;
    }

    public function isDebit(): bool
    {
        return $this->direction === self::DIR_DEBIT;
    }

    public function signedAmount(): float
    {
        return $this->isCredit() ? (float) $this->amount : -1 * (float) $this->amount;
    }
}
