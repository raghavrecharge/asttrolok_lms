<?php

namespace App\Models\PaymentEngine;

use App\User;
use Illuminate\Database\Eloquent\Model;
use App\Models\PaymentEngine\UpeLedgerEntry;

class UpeInstallmentPlan extends Model
{
    protected $table = 'upe_installment_plans';

    protected $fillable = [
        'sale_id',
        'total_amount',
        'num_installments',
        'plan_type',
        'status',
        'restructured_from_id',
        'approved_by',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
    ];

    // ── Relationships ──

    public function sale()
    {
        return $this->belongsTo(UpeSale::class, 'sale_id');
    }

    public function schedules()
    {
        return $this->hasMany(UpeInstallmentSchedule::class, 'plan_id')->orderBy('due_date')->orderBy('sequence');
    }

    public function restructuredFrom()
    {
        return $this->belongsTo(self::class, 'restructured_from_id');
    }

    public function restructuredTo()
    {
        return $this->hasOne(self::class, 'restructured_from_id');
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

    // ── Helpers ──

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Derive total paid from immutable ledger (source of truth).
     */
    public function totalPaid(): float
    {
        $scheduleIds = $this->schedules()->pluck('id')->toArray();
        if (empty($scheduleIds)) {
            return 0;
        }
        return round((float) UpeLedgerEntry::where('reference_type', 'installment_schedule')
            ->whereIn('reference_id', $scheduleIds)
            ->sum('amount'), 2);
    }

    public function totalRemaining(): float
    {
        return round((float) $this->total_amount - $this->totalPaid(), 2);
    }

    public function nextDueSchedule(): ?UpeInstallmentSchedule
    {
        return $this->schedules()
            ->whereIn('status', ['due', 'partial', 'overdue'])
            ->orderBy('due_date')
            ->orderBy('sequence')
            ->first();
    }
}
