<?php

namespace App\Models\PaymentEngine;

use App\User;
use Illuminate\Database\Eloquent\Model;

class UpeAdjustment extends Model
{
    public $timestamps = false;

    protected $table = 'upe_adjustments';

    protected static function boot()
    {
        parent::boot();

        static::updating(function () {
            throw new \RuntimeException('Adjustment records are immutable. Updates are not allowed.');
        });

        static::deleting(function () {
            throw new \RuntimeException('Adjustment records are immutable. Deletes are not allowed.');
        });
    }

    protected $fillable = [
        'source_sale_id',
        'target_sale_id',
        'adjustment_type',
        'source_amount',
        'target_amount',
        'adjustment_percent',
        'policy_snapshot',
        'source_ledger_entry_id',
        'target_ledger_entry_id',
        'approved_by',
    ];

    protected $casts = [
        'source_amount' => 'decimal:2',
        'target_amount' => 'decimal:2',
        'adjustment_percent' => 'decimal:2',
        'policy_snapshot' => 'array',
        'created_at' => 'datetime',
    ];

    // ── Relationships ──

    public function sourceSale()
    {
        return $this->belongsTo(UpeSale::class, 'source_sale_id');
    }

    public function targetSale()
    {
        return $this->belongsTo(UpeSale::class, 'target_sale_id');
    }

    public function sourceLedgerEntry()
    {
        return $this->belongsTo(UpeLedgerEntry::class, 'source_ledger_entry_id');
    }

    public function targetLedgerEntry()
    {
        return $this->belongsTo(UpeLedgerEntry::class, 'target_ledger_entry_id');
    }

    public function approvedByUser()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
