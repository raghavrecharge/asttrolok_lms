<?php

namespace App\Services\PaymentEngine;

use App\Models\PaymentEngine\UpeInstallmentPlan;
use App\Models\PaymentEngine\UpeInstallmentSchedule;
use App\Models\PaymentEngine\UpeLedgerEntry;
use App\Models\PaymentEngine\UpeSale;
use Illuminate\Support\Facades\DB;

class InstallmentEngine
{
    private PaymentLedgerService $ledger;
    private AuditService $audit;

    public function __construct(PaymentLedgerService $ledger, AuditService $audit)
    {
        $this->ledger = $ledger;
        $this->audit = $audit;
    }

    /**
     * Create an installment plan for a sale.
     *
     * @param UpeSale  $sale             The sale to attach the plan to
     * @param int      $numInstallments  Number of EMIs
     * @param string   $planType         standard|flexible
     * @param array    $customSchedule   Optional custom schedule [{amount, due_date}, ...]
     * @param int|null $approvedBy       Admin for flexible plans
     * @return UpeInstallmentPlan
     */
    public function createPlan(
        UpeSale $sale,
        int $numInstallments,
        string $planType = 'standard',
        array $customSchedule = [],
        ?int $approvedBy = null
    ): UpeInstallmentPlan {
        if ($sale->pricing_mode !== 'installment') {
            throw new \RuntimeException('Sale pricing mode must be "installment" to create an installment plan.');
        }

        // Check no active plan exists
        $existingPlan = UpeInstallmentPlan::where('sale_id', $sale->id)->active()->first();
        if ($existingPlan) {
            throw new \RuntimeException("Sale #{$sale->id} already has an active installment plan.");
        }

        if ($numInstallments < 2) {
            throw new \InvalidArgumentException('Installment plan must have at least 2 installments.');
        }

        // Calculate total amount: base_fee minus any discounts already applied
        $totalDiscounts = UpeLedgerEntry::forSale($sale->id)
            ->ofType(UpeLedgerEntry::TYPE_DISCOUNT)
            ->sum('amount');
        $totalAmount = max(0, (float) $sale->base_fee_snapshot - (float) $totalDiscounts);

        return DB::transaction(function () use ($sale, $numInstallments, $planType, $customSchedule, $approvedBy, $totalAmount) {
            $plan = UpeInstallmentPlan::create([
                'sale_id' => $sale->id,
                'total_amount' => $totalAmount,
                'num_installments' => $numInstallments,
                'plan_type' => $planType,
                'status' => 'active',
                'approved_by' => $approvedBy,
            ]);

            if (!empty($customSchedule)) {
                $this->createCustomSchedules($plan, $customSchedule);
            } else {
                $this->createEvenSchedules($plan, $totalAmount, $numInstallments);
            }

            // Activate sale immediately for installment purchases
            if ($sale->isPendingPayment()) {
                $sale->update([
                    'status' => 'active',
                    'valid_from' => now(),
                    'valid_until' => $sale->product && $sale->product->validity_days
                        ? now()->addDays($sale->product->validity_days)
                        : null,
                ]);
            }

            if ($approvedBy) {
                $this->audit->log($approvedBy, 'admin', 'installment.plan_created', 'installment_plan', $plan->id, null, [
                    'sale_id' => $sale->id,
                    'total_amount' => $totalAmount,
                    'num_installments' => $numInstallments,
                    'plan_type' => $planType,
                ]);
            }

            return $plan;
        });
    }

    /**
     * Record a payment against the next due installment.
     * Enforces sequential order — cannot skip installments.
     */
    public function recordPayment(
        UpeInstallmentPlan $plan,
        float $amount,
        string $paymentMethod,
        ?string $gatewayTransactionId = null,
        ?array $gatewayResponse = null,
        ?int $processedBy = null
    ): array {
        if (!$plan->isActive()) {
            throw new \RuntimeException("Installment plan #{$plan->id} is not active.");
        }

        if ($amount <= 0) {
            throw new \InvalidArgumentException('Payment amount must be positive.');
        }

        return DB::transaction(function () use ($plan, $amount, $paymentMethod, $gatewayTransactionId, $gatewayResponse, $processedBy) {
            $plan = UpeInstallmentPlan::where('id', $plan->id)->lockForUpdate()->first();
            $remaining = $amount;
            $paidSchedules = [];

            // Get schedules in order, starting from first unpaid
            $schedules = $plan->schedules()
                ->whereIn('status', ['due', 'partial', 'overdue', 'upcoming'])
                ->orderBy('sequence')
                ->lockForUpdate()
                ->get();

            if ($schedules->isEmpty()) {
                throw new \RuntimeException('No unpaid installments remaining.');
            }

            foreach ($schedules as $schedule) {
                if ($remaining <= 0) break;

                $needed = $schedule->remainingAmount();
                $payment = min($remaining, $needed);

                // Record ledger entry
                $entry = $this->ledger->recordInstallmentPayment(
                    saleId: $plan->sale_id,
                    amount: $payment,
                    scheduleId: $schedule->id,
                    paymentMethod: $paymentMethod,
                    gatewayTransactionId: $gatewayTransactionId,
                    gatewayResponse: $gatewayResponse,
                    processedBy: $processedBy
                );

                // Derive paid amount from immutable ledger (source of truth)
                $derivedPaid = $schedule->derivedAmountPaid();
                $newStatus = ($derivedPaid >= (float) $schedule->amount_due) ? 'paid' : 'partial';

                // Only update status and link — amount_paid is kept as a denormalized cache
                // but the TRUTH is always derivedAmountPaid() from the ledger
                $schedule->update([
                    'amount_paid' => $derivedPaid,
                    'status' => $newStatus,
                    'paid_at' => ($newStatus === 'paid') ? now() : $schedule->paid_at,
                    'ledger_entry_id' => ($newStatus === 'paid') ? $entry->id : $schedule->ledger_entry_id,
                ]);

                $paidSchedules[] = [
                    'schedule_id' => $schedule->id,
                    'sequence' => $schedule->sequence,
                    'amount_applied' => $payment,
                    'new_status' => $newStatus,
                ];

                $remaining -= $payment;

                // Only allow payment to flow to next schedule (sequential enforcement)
                // Gateway txn ID used only for first schedule to avoid duplicate idempotency keys
                $gatewayTransactionId = null;
            }

            // Check if all schedules are paid → complete the plan
            $allPaid = $plan->schedules()->whereIn('status', ['upcoming', 'due', 'partial', 'overdue'])->count() === 0;
            if ($allPaid) {
                $plan->update(['status' => 'completed']);
            }

            // Mark next upcoming as due
            $this->updateScheduleStatuses($plan);

            return [
                'plan_id' => $plan->id,
                'amount_received' => $amount,
                'amount_applied' => $amount - $remaining,
                'overpayment' => max(0, $remaining),
                'schedules_affected' => $paidSchedules,
                'plan_completed' => $allPaid,
            ];
        });
    }

    /**
     * Get the next due installment for a plan.
     */
    public function getNextDue(UpeInstallmentPlan $plan): ?UpeInstallmentSchedule
    {
        return $plan->nextDueSchedule();
    }

    /**
     * Restructure an installment plan.
     * Closes the old plan and creates a new one for the remaining balance.
     */
    public function restructure(
        UpeInstallmentPlan $oldPlan,
        int $newNumInstallments,
        array $customSchedule = [],
        int $approvedBy = 0
    ): UpeInstallmentPlan {
        if (!$oldPlan->isActive()) {
            throw new \RuntimeException("Cannot restructure a plan that is not active.");
        }

        return DB::transaction(function () use ($oldPlan, $newNumInstallments, $customSchedule, $approvedBy) {
            $oldPlan = UpeInstallmentPlan::where('id', $oldPlan->id)->lockForUpdate()->first();

            // Calculate remaining balance
            $remaining = $oldPlan->totalRemaining();
            if ($remaining <= 0) {
                throw new \RuntimeException('No remaining balance to restructure.');
            }

            // Close old plan
            $oldPlan->schedules()
                ->whereIn('status', ['upcoming', 'due', 'partial', 'overdue'])
                ->update(['status' => 'waived']);

            $oldPlan->update(['status' => 'restructured']);

            // Create new plan
            $newPlan = UpeInstallmentPlan::create([
                'sale_id' => $oldPlan->sale_id,
                'total_amount' => $remaining,
                'num_installments' => $newNumInstallments,
                'plan_type' => 'flexible',
                'status' => 'active',
                'restructured_from_id' => $oldPlan->id,
                'approved_by' => $approvedBy,
            ]);

            if (!empty($customSchedule)) {
                $this->createCustomSchedules($newPlan, $customSchedule);
            } else {
                $this->createEvenSchedules($newPlan, $remaining, $newNumInstallments);
            }

            $this->audit->log($approvedBy, 'admin', 'installment.restructured', 'installment_plan', $newPlan->id, [
                'old_plan_id' => $oldPlan->id,
                'old_remaining' => $remaining,
            ], [
                'new_plan_id' => $newPlan->id,
                'new_num_installments' => $newNumInstallments,
                'new_total' => $remaining,
            ]);

            return $newPlan;
        });
    }

    /**
     * Split a single unpaid schedule into N sub-schedules with custom amounts and dates.
     * The original schedule is waived and replaced by the new sub-schedules.
     *
     * @param UpeInstallmentPlan     $plan           The plan containing the schedule
     * @param UpeInstallmentSchedule $schedule       The specific schedule to split
     * @param array                  $subSchedules   [{amount, due_date}, ...] — must sum to schedule's remaining amount
     * @param int                    $approvedBy     Admin user ID
     * @return array                                 The newly created sub-schedules
     */
    public function splitSchedule(
        UpeInstallmentPlan $plan,
        UpeInstallmentSchedule $schedule,
        array $subSchedules,
        int $approvedBy = 0
    ): array {
        if (!$plan->isActive()) {
            throw new \RuntimeException("Cannot restructure a plan that is not active.");
        }

        if ($schedule->isPaid()) {
            throw new \RuntimeException("Cannot split an already-paid schedule.");
        }

        if ($schedule->plan_id !== $plan->id) {
            throw new \RuntimeException("Schedule does not belong to this plan.");
        }

        if (count($subSchedules) < 2) {
            throw new \InvalidArgumentException("Must split into at least 2 sub-schedules.");
        }

        $targetAmount = $schedule->remainingAmount();
        $splitTotal = round(array_sum(array_column($subSchedules, 'amount')), 2);

        if (abs($splitTotal - $targetAmount) > 1) {
            throw new \InvalidArgumentException(
                "Sub-schedule total ({$splitTotal}) does not match schedule remaining amount ({$targetAmount})."
            );
        }

        return DB::transaction(function () use ($plan, $schedule, $subSchedules, $approvedBy, $targetAmount) {
            $plan = UpeInstallmentPlan::where('id', $plan->id)->lockForUpdate()->first();
            $schedule = UpeInstallmentSchedule::where('id', $schedule->id)->lockForUpdate()->first();

            $originalSequence = $schedule->sequence;

            // 1. Waive the original schedule
            $schedule->update(['status' => 'waived']);

            // 2. Shift sequences of all subsequent schedules to make room
            $shiftBy = count($subSchedules) - 1; // e.g., splitting 1 into 3 = shift by 2
            if ($shiftBy > 0) {
                UpeInstallmentSchedule::where('plan_id', $plan->id)
                    ->where('sequence', '>', $originalSequence)
                    ->where('id', '!=', $schedule->id)
                    ->orderByDesc('sequence')
                    ->each(function ($s) use ($shiftBy) {
                        $s->update(['sequence' => $s->sequence + $shiftBy]);
                    });
            }

            // 3. Carry forward existing payment from the waived schedule
            $carriedPayment = (float) $schedule->amount_paid;
            $schedule->update(['amount_paid' => 0]); // payment moves to sub-schedules

            // 4. Create new sub-schedules, distributing carried payment via waterfall
            $created = [];
            $remainingCarry = $carriedPayment;
            foreach ($subSchedules as $i => $sub) {
                $newSeq = $originalSequence + $i;
                $due = round($sub['amount'], 2);

                // Waterfall: apply as much carried payment as possible
                $applied = min($remainingCarry, $due);
                $remainingCarry = round($remainingCarry - $applied, 2);

                // Determine status based on payment
                if ($applied >= $due - 0.01) {
                    $status = 'paid';
                    $applied = $due; // cap at due
                } elseif ($applied > 0) {
                    $status = 'partial';
                } else {
                    $isFirst = ($i === 0);
                    $status = $isFirst ? 'due' : 'upcoming';
                }

                $newSchedule = UpeInstallmentSchedule::create([
                    'plan_id' => $plan->id,
                    'sequence' => $newSeq,
                    'amount_due' => $due,
                    'amount_paid' => round($applied, 2),
                    'due_date' => $sub['due_date'],
                    'status' => $status,
                    'paid_at' => ($status === 'paid') ? now() : null,
                ]);

                $created[] = $newSchedule;
            }

            // 5. Update plan num_installments
            $activeScheduleCount = UpeInstallmentSchedule::where('plan_id', $plan->id)
                ->whereNotIn('status', ['waived'])
                ->count();
            $plan->update(['num_installments' => $activeScheduleCount]);

            // 6. Audit trail
            $this->audit->log($approvedBy, 'admin', 'installment.schedule_split', 'installment_schedule', $schedule->id, [
                'original_sequence' => $originalSequence,
                'original_amount' => $targetAmount,
            ], [
                'num_sub_schedules' => count($subSchedules),
                'sub_schedule_ids' => array_map(fn($s) => $s->id, $created),
                'plan_id' => $plan->id,
            ]);

            return $created;
        });
    }

    /**
     * Mark overdue installments. Called by scheduled job.
     */
    public function markOverdue(): int
    {
        return UpeInstallmentSchedule::whereIn('status', ['upcoming', 'due', 'partial'])
            ->where('due_date', '<', now()->toDateString())
            ->whereHas('plan', function ($q) {
                $q->where('status', 'active');
            })
            ->update(['status' => 'overdue']);
    }

    /**
     * Mark upcoming installments as due when their date arrives.
     */
    public function updateScheduleStatuses(UpeInstallmentPlan $plan): void
    {
        // First unpaid schedule becomes 'due' if it's upcoming
        $nextUnpaid = $plan->schedules()
            ->where('status', 'upcoming')
            ->orderBy('sequence')
            ->first();

        if ($nextUnpaid) {
            $nextUnpaid->update(['status' => 'due']);
        }
    }

    // ── Private Helpers ──

    private function createEvenSchedules(UpeInstallmentPlan $plan, float $total, int $count): void
    {
        $emi = round($total / $count, 2);
        $runningTotal = 0;

        for ($i = 1; $i <= $count; $i++) {
            $amount = ($i === $count) ? round($total - $runningTotal, 2) : $emi;
            $runningTotal += $amount;

            UpeInstallmentSchedule::create([
                'plan_id' => $plan->id,
                'sequence' => $i,
                'amount_due' => $amount,
                'amount_paid' => 0,
                'due_date' => now()->addMonths($i)->toDateString(),
                'status' => ($i === 1) ? 'due' : 'upcoming',
            ]);
        }
    }

    private function createCustomSchedules(UpeInstallmentPlan $plan, array $schedule): void
    {
        $totalScheduled = array_sum(array_column($schedule, 'amount'));
        if (abs($totalScheduled - (float) $plan->total_amount) > 0.01) {
            throw new \InvalidArgumentException(
                "Custom schedule total ({$totalScheduled}) does not match plan total ({$plan->total_amount})."
            );
        }

        foreach ($schedule as $i => $item) {
            UpeInstallmentSchedule::create([
                'plan_id' => $plan->id,
                'sequence' => $i + 1,
                'amount_due' => $item['amount'],
                'amount_paid' => 0,
                'due_date' => $item['due_date'],
                'status' => ($i === 0) ? 'due' : 'upcoming',
            ]);
        }
    }
}
