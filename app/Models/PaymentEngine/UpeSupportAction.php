<?php

namespace App\Models\PaymentEngine;

use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class UpeSupportAction extends Model
{
    protected $table = 'upe_support_actions';

    // Action type constants
    const ACTION_EXTENSION         = 'extension';
    const ACTION_TEMPORARY_ACCESS  = 'temporary_access';
    const ACTION_MENTOR_ACCESS     = 'mentor_access';
    const ACTION_RELATIVE_ACCESS   = 'relative_access';
    const ACTION_OFFLINE_PAYMENT   = 'offline_payment';
    const ACTION_REFUND            = 'refund';
    const ACTION_PAYMENT_MIGRATION = 'payment_migration';
    const ACTION_COUPON_APPLY      = 'coupon_apply';

    // Status constants
    const STATUS_PENDING  = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_EXECUTED = 'executed';
    const STATUS_REJECTED = 'rejected';
    const STATUS_EXPIRED  = 'expired';

    protected $fillable = [
        'uuid',
        'action_type',
        'status',
        'user_id',
        'beneficiary_id',
        'product_id',
        'source_sale_id',
        'target_sale_id',
        'amount',
        'payment_method',
        'expires_at',
        'coupon_code',
        'discount_id',
        'source_product_id',
        'metadata',
        'requested_by',
        'approved_by',
        'executed_by',
        'requested_at',
        'approved_at',
        'executed_at',
        'rejection_reason',
        'idempotency_key',
    ];

    protected $casts = [
        'amount'       => 'decimal:2',
        'metadata'     => 'array',
        'expires_at'   => 'datetime',
        'requested_at' => 'datetime',
        'approved_at'  => 'datetime',
        'executed_at'  => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });
    }

    // ── Relationships ──

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function beneficiary()
    {
        return $this->belongsTo(User::class, 'beneficiary_id');
    }

    public function product()
    {
        return $this->belongsTo(UpeProduct::class, 'product_id');
    }

    public function sourceProduct()
    {
        return $this->belongsTo(UpeProduct::class, 'source_product_id');
    }

    public function sourceSale()
    {
        return $this->belongsTo(UpeSale::class, 'source_sale_id');
    }

    public function targetSale()
    {
        return $this->belongsTo(UpeSale::class, 'target_sale_id');
    }

    public function requestedByUser()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approvedByUser()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function executedByUser()
    {
        return $this->belongsTo(User::class, 'executed_by');
    }

    // ── State Checks ──

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public function isExecuted(): bool
    {
        return $this->status === self::STATUS_EXECUTED;
    }

    public function isExpired(): bool
    {
        if ($this->status === self::STATUS_EXPIRED) {
            return true;
        }
        if ($this->expires_at && $this->expires_at->isPast() && $this->status === self::STATUS_EXECUTED) {
            return true;
        }
        return false;
    }

    public function canBeApproved(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function canBeExecuted(): bool
    {
        return in_array($this->status, [self::STATUS_PENDING, self::STATUS_APPROVED]);
    }

    // ── Scopes ──

    public function scopeOfType($query, string $type)
    {
        return $query->where('action_type', $type);
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeForBeneficiary($query, int $userId)
    {
        return $query->where(function ($q) use ($userId) {
            $q->where('beneficiary_id', $userId)->orWhere('user_id', $userId);
        });
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', [self::STATUS_PENDING, self::STATUS_APPROVED, self::STATUS_EXECUTED]);
    }

    public function scopeTemporaryAccessActive($query)
    {
        return $query->where('action_type', self::ACTION_TEMPORARY_ACCESS)
            ->where('status', self::STATUS_EXECUTED)
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            });
    }

    // ── Helpers ──

    public function getEffectiveBeneficiaryId(): int
    {
        return $this->beneficiary_id ?? $this->user_id;
    }
}
