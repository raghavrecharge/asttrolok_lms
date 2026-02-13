<?php

namespace App\Models\PaymentEngine;

use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class UpePaymentRequest extends Model
{
    protected $table = 'upe_payment_requests';

    protected $fillable = [
        'uuid',
        'request_type',
        'user_id',
        'sale_id',
        'payload',
        'status',
        'verified_by',
        'verified_at',
        'approved_by',
        'approved_at',
        'executed_at',
        'execution_result',
        'rejected_reason',
    ];

    protected $casts = [
        'payload' => 'array',
        'execution_result' => 'array',
        'verified_at' => 'datetime',
        'approved_at' => 'datetime',
        'executed_at' => 'datetime',
    ];

    const TRANSITIONS = [
        'pending' => ['verified', 'rejected'],
        'verified' => ['approved', 'rejected'],
        'approved' => ['executed', 'rejected'],
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
        return $this->belongsTo(User::class);
    }

    public function sale()
    {
        return $this->belongsTo(UpeSale::class, 'sale_id');
    }

    public function verifiedByUser()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function approvedByUser()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // ── Scopes ──

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('request_type', $type);
    }

    // ── State Machine ──

    public function canTransitionTo(string $newStatus): bool
    {
        $allowed = self::TRANSITIONS[$this->status] ?? [];
        return in_array($newStatus, $allowed);
    }

    public function isExecuted(): bool
    {
        return $this->executed_at !== null;
    }

    public function isTerminal(): bool
    {
        return in_array($this->status, ['executed', 'rejected']);
    }
}
