<?php

namespace App\Models\PaymentEngine;

use Illuminate\Database\Eloquent\Model;

class UpeInstallmentSchedule extends Model
{
    protected $table = 'upe_installment_schedules';

    protected $fillable = [
        'plan_id',
        'sequence',
        'amount_due',
        'amount_paid',
        'due_date',
        'status',
        'paid_at',
        'ledger_entry_id',
    ];

    protected $casts = [
        'amount_due' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'due_date' => 'date',
        'paid_at' => 'datetime',
    ];

    // ── Relationships ──

    public function plan()
    {
        return $this->belongsTo(UpeInstallmentPlan::class, 'plan_id');
    }

    public function ledgerEntry()
    {
        return $this->belongsTo(UpeLedgerEntry::class, 'ledger_entry_id');
    }

    // ── Scopes ──

    public function scopeUnpaid($query)
    {
        return $query->whereIn('status', ['upcoming', 'due', 'partial', 'overdue']);
    }

    public function scopeOverdue($query)
    {
        return $query->where('status', 'overdue');
    }

    // ── Helpers ──

    /**
     * Derive amount_paid from immutable ledger entries (source of truth).
     * Do NOT rely on the amount_paid column directly.
     */
    public function derivedAmountPaid(): float
    {
        return round((float) UpeLedgerEntry::where('reference_type', 'installment_schedule')
            ->where('reference_id', $this->id)
            ->sum('amount'), 2);
    }

    public function remainingAmount(): float
    {
        return round((float) $this->amount_due - $this->derivedAmountPaid(), 2);
    }

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    public function isOverdue(): bool
    {
        return $this->status === 'overdue' || ($this->due_date->isPast() && !$this->isPaid());
    }
}
