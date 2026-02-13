<?php

namespace App\Models\PaymentEngine;

use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class UpeSale extends Model
{
    protected $table = 'upe_sales';

    protected $fillable = [
        'uuid',
        'user_id',
        'product_id',
        'sale_type',
        'pricing_mode',
        'base_fee_snapshot',
        'currency',
        'status',
        'valid_from',
        'valid_until',
        'parent_sale_id',
        'referral_id',
        'support_request_id',
        'approved_by',
        'executed_at',
        'metadata',
    ];

    protected $casts = [
        'base_fee_snapshot' => 'decimal:2',
        'valid_from' => 'datetime',
        'valid_until' => 'datetime',
        'executed_at' => 'datetime',
        'metadata' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });

        static::updating(function ($model) {
            if ($model->isDirty('base_fee_snapshot')) {
                throw new \RuntimeException(
                    'Sale base_fee_snapshot is immutable after creation. It records the price at time of sale.'
                );
            }
        });
    }

    // ── Relationships ──

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function product()
    {
        return $this->belongsTo(UpeProduct::class, 'product_id');
    }

    public function parentSale()
    {
        return $this->belongsTo(self::class, 'parent_sale_id');
    }

    public function childSales()
    {
        return $this->hasMany(self::class, 'parent_sale_id');
    }

    public function ledgerEntries()
    {
        return $this->hasMany(UpeLedgerEntry::class, 'sale_id');
    }

    public function installmentPlan()
    {
        return $this->hasOne(UpeInstallmentPlan::class, 'sale_id');
    }

    public function subscription()
    {
        return $this->hasOne(UpeSubscription::class, 'sale_id');
    }

    public function referral()
    {
        return $this->belongsTo(UpeReferral::class, 'referral_id');
    }

    public function adjustmentsAsSource()
    {
        return $this->hasMany(UpeAdjustment::class, 'source_sale_id');
    }

    public function adjustmentsAsTarget()
    {
        return $this->hasMany(UpeAdjustment::class, 'target_sale_id');
    }

    public function approvedByUser()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // ── Scopes ──

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeForProduct($query, int $productId)
    {
        return $query->where('product_id', $productId);
    }

    public function scopeAccessible($query)
    {
        return $query->whereIn('status', ['active', 'partially_refunded']);
    }

    // ── State Checks ──

    public function isPendingPayment(): bool
    {
        return $this->status === 'pending_payment';
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isRefunded(): bool
    {
        return in_array($this->status, ['refunded', 'partially_refunded']);
    }

    public function isTerminal(): bool
    {
        return in_array($this->status, ['refunded', 'expired', 'cancelled', 'completed']);
    }

    public function isExecuted(): bool
    {
        return $this->executed_at !== null;
    }

    public function hasValidAccess(): bool
    {
        if (!$this->isActive() && $this->status !== 'partially_refunded') {
            return false;
        }

        if ($this->valid_until !== null && $this->valid_until->isPast()) {
            return false;
        }

        return true;
    }
}
