<?php

namespace App\Models\PaymentEngine;

use App\User;
use Illuminate\Database\Eloquent\Model;
use App\Models\PaymentEngine\UpeLedgerEntry;

class UpeDiscount extends Model
{
    protected $table = 'upe_discounts';

    protected $fillable = [
        'code',
        'discount_type',
        'value',
        'max_discount_amount',
        'min_order_amount',
        'scope',
        'scope_ids',
        'allowed_roles',
        'max_uses_total',
        'max_uses_per_user',
        'used_count',
        'stackable',
        'valid_from',
        'valid_until',
        'created_by',
        'status',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'max_discount_amount' => 'decimal:2',
        'min_order_amount' => 'decimal:2',
        'scope_ids' => 'array',
        'allowed_roles' => 'array',
        'stackable' => 'boolean',
        'valid_from' => 'datetime',
        'valid_until' => 'datetime',
    ];

    // ── Relationships ──

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // ── Scopes ──

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeByCode($query, string $code)
    {
        return $query->where('code', $code);
    }

    public function scopeValid($query)
    {
        $now = now();
        return $query->where('status', 'active')
            ->where(function ($q) use ($now) {
                $q->whereNull('valid_from')->orWhere('valid_from', '<=', $now);
            })
            ->where(function ($q) use ($now) {
                $q->whereNull('valid_until')->orWhere('valid_until', '>=', $now);
            });
    }

    // ── Helpers ──

    public function isExpired(): bool
    {
        if ($this->valid_until === null) {
            return false;
        }
        return $this->valid_until->isPast();
    }

    public function isActive(): bool
    {
        return $this->status === 'active' && !$this->isExpired();
    }

    public function hasUsesRemaining(): bool
    {
        if ($this->max_uses_total === null) {
            return true;
        }
        return $this->actualUsedCount() < $this->max_uses_total;
    }

    /**
     * Derive actual usage count from ledger entries (immutable source of truth).
     * Never rely on the stale used_count column.
     */
    public function actualUsedCount(): int
    {
        return UpeLedgerEntry::where('reference_type', 'discount')
            ->where('reference_id', $this->id)
            ->count();
    }

    public function calculateDiscount(float $baseAmount): float
    {
        if ($this->discount_type === 'percentage') {
            $discount = $baseAmount * ($this->value / 100);
            if ($this->max_discount_amount !== null) {
                $discount = min($discount, (float) $this->max_discount_amount);
            }
            return round($discount, 2);
        }

        return min((float) $this->value, $baseAmount);
    }

    public function appliesToProduct(int $productId): bool
    {
        if ($this->scope === 'global') {
            return true;
        }

        if ($this->scope === 'product' && is_array($this->scope_ids)) {
            return in_array($productId, $this->scope_ids);
        }

        return false;
    }

    public function isAllowedForRole(string $role): bool
    {
        if ($this->allowed_roles === null) {
            return true;
        }

        return in_array($role, $this->allowed_roles);
    }
}
