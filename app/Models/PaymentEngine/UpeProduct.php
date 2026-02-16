<?php

namespace App\Models\PaymentEngine;

use App\User;
use Illuminate\Database\Eloquent\Model;

class UpeProduct extends Model
{
    protected $table = 'upe_products';

    protected $appends = ['name'];

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

    public function getNameAttribute(): string
    {
        try {
            switch ($this->product_type) {
                case 'course_video':
                case 'webinar':
                    $entity = \App\Models\Webinar::find($this->external_id);
                    return $entity ? ($entity->title ?? "Course #{$this->external_id}") : "Course #{$this->external_id}";
                case 'bundle':
                    $entity = \App\Models\Bundle::find($this->external_id);
                    return $entity ? ($entity->title ?? "Bundle #{$this->external_id}") : "Bundle #{$this->external_id}";
                case 'meeting':
                    return "Meeting #{$this->external_id}";
                case 'product':
                    $entity = \App\Models\Product::find($this->external_id);
                    return $entity ? ($entity->title ?? $entity->name ?? "Product #{$this->external_id}") : "Product #{$this->external_id}";
                case 'subscribe':
                case 'subscription':
                    $entity = \App\Models\Subscribe::find($this->external_id);
                    return $entity ? ($entity->title ?? "Subscription #{$this->external_id}") : "Subscription #{$this->external_id}";
                default:
                    return ucfirst($this->product_type) . " #{$this->external_id}";
            }
        } catch (\Throwable $e) {
            return ucfirst($this->product_type) . " #{$this->external_id}";
        }
    }
}
