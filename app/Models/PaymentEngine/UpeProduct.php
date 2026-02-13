<?php

namespace App\Models\PaymentEngine;

use App\User;
use Illuminate\Database\Eloquent\Model;

class UpeProduct extends Model
{
    protected $table = 'upe_products';

    protected $fillable = [
        'product_type',
        'external_id',
        'base_fee',
        'currency',
        'validity_days',
        'is_upgradeable',
        'upgrade_policy_id',
        'adjustment_eligible',
        'adjustment_max_percent',
        'status',
        'metadata',
    ];

    protected static function boot()
    {
        parent::boot();

        static::updating(function ($model) {
            if ($model->isDirty('base_fee')) {
                throw new \RuntimeException(
                    'Product base_fee is immutable after creation. Create a new product instead.'
                );
            }
        });
    }

    protected $casts = [
        'base_fee' => 'decimal:2',
        'adjustment_max_percent' => 'decimal:2',
        'is_upgradeable' => 'boolean',
        'adjustment_eligible' => 'boolean',
        'metadata' => 'array',
    ];

    // ── Relationships ──

    public function sales()
    {
        return $this->hasMany(UpeSale::class, 'product_id');
    }

    public function subscriptions()
    {
        return $this->hasMany(UpeSubscription::class, 'product_id');
    }

    // ── Scopes ──

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('product_type', $type);
    }

    public function scopeForExternal($query, string $type, int $externalId)
    {
        return $query->where('product_type', $type)->where('external_id', $externalId);
    }

    // ── Helpers ──

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function hasValidity(): bool
    {
        return $this->validity_days !== null;
    }
}
