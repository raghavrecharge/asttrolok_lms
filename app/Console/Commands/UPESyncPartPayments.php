<?php

namespace App\Console\Commands;

use App\Models\PaymentEngine\UpeProduct;
use App\Models\PaymentEngine\UpeSale;
use App\Models\PaymentEngine\UpeLedgerEntry;
use App\Models\PaymentEngine\UpeInstallmentPlan;
use App\Models\PaymentEngine\UpeInstallmentSchedule;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class UPESyncPartPayments extends Command
{
    protected $signature = 'upe:sync-part-payments
                            {--dry-run : Show what would be synced without writing}
                            {--user= : Sync only a specific user_id}
                            {--webinar= : Sync only a specific webinar_id}';

    protected $description = 'One-way sync: read legacy webinar_part_payment rows and append to UPE ledger (idempotent, re-runnable)';

    // ── Counters ──
    private int $combosProcessed = 0;
    private int $combosSkippedFullySynced = 0;
    private int $salesCreated = 0;
    private int $salesReused = 0;
    private int $plansCreated = 0;
    private int $plansReused = 0;
    private int $schedulesCreated = 0;
    private int $ledgerEntriesCreated = 0;
    private int $ledgerEntriesSkipped = 0;
    private int $schedulesUpdated = 0;
    private float $totalAmountSynced = 0;
    private array $errors = [];
    private bool $dryRun = false;

    public function handle(): int
    {
        $this->dryRun = (bool) $this->option('dry-run');
        $filterUser = $this->option('user');
        $filterWebinar = $this->option('webinar');

        if ($this->dryRun) {
            $this->warn('🔍 DRY-RUN MODE — no data will be written');
        }

        $this->info('=== UPE Part-Payment Sync ===');
        $this->newLine();

        // ── 1. Read all distinct (user_id, webinar_id) combos from webinar_part_payment ──
        $query = DB::table('webinar_part_payment')
            ->select('user_id', 'webinar_id')
            ->groupBy('user_id', 'webinar_id');

        if ($filterUser) {
            $query->where('user_id', (int) $filterUser);
        }
        if ($filterWebinar) {
            $query->where('webinar_id', (int) $filterWebinar);
        }

        $combos = $query->get();

        $this->info("Found {$combos->count()} user+webinar combos with part-payments");
        $this->newLine();

        $bar = $this->output->createProgressBar($combos->count());
        $bar->start();

        foreach ($combos as $combo) {
            try {
                $this->syncCombo((int) $combo->user_id, (int) $combo->webinar_id);
            } catch (\Throwable $e) {
                $this->errors[] = "u#{$combo->user_id} w#{$combo->webinar_id}: {$e->getMessage()}";
            }
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        // ── Summary ──
        $this->info('=== Sync Summary ===');
        $this->table(
            ['Metric', 'Count'],
            [
                ['Combos processed', $this->combosProcessed],
                ['Combos skipped (fully synced)', $this->combosSkippedFullySynced],
                ['UPE sales created', $this->salesCreated],
                ['UPE sales reused', $this->salesReused],
                ['Installment plans created', $this->plansCreated],
                ['Installment plans reused', $this->plansReused],
                ['Schedules created', $this->schedulesCreated],
                ['Schedules updated (status)', $this->schedulesUpdated],
                ['Ledger entries created', $this->ledgerEntriesCreated],
                ['Ledger entries skipped (already exist)', $this->ledgerEntriesSkipped],
                ['Total amount synced (₹)', number_format($this->totalAmountSynced, 2)],
            ]
        );

        if (!empty($this->errors)) {
            $this->newLine();
            $this->error('Errors (' . count($this->errors) . '):');
            foreach (array_slice($this->errors, 0, 20) as $err) {
                $this->line("  • {$err}");
            }
            if (count($this->errors) > 20) {
                $this->line('  ... and ' . (count($this->errors) - 20) . ' more');
            }
        }

        // ── Reconciliation check ──
        $this->newLine();
        $this->runReconciliation($filterUser, $filterWebinar);

        return empty($this->errors) ? Command::SUCCESS : Command::FAILURE;
    }

    /**
     * Sync all part-payments for a single (user_id, webinar_id) combo.
     */
    private function syncCombo(int $userId, int $webinarId): void
    {
        $this->combosProcessed++;

        // ── A. Load all part-payment rows for this combo, chronological ──
        $partPayments = DB::table('webinar_part_payment')
            ->where('user_id', $userId)
            ->where('webinar_id', $webinarId)
            ->orderBy('created_at')
            ->orderBy('id')
            ->get();

        if ($partPayments->isEmpty()) {
            return;
        }

        // ── B. Check if ALL rows are already synced (fast path) ──
        $allSynced = true;
        foreach ($partPayments as $pp) {
            $key = "legacy_part_payment_{$pp->id}";
            if (!UpeLedgerEntry::where('idempotency_key', $key)->exists()) {
                $allSynced = false;
                break;
            }
        }
        if ($allSynced) {
            $this->combosSkippedFullySynced++;
            return;
        }

        if ($this->dryRun) {
            $total = $partPayments->sum('amount');
            $this->salesCreated++;
            $this->plansCreated++;
            $this->ledgerEntriesCreated += $partPayments->count();
            $this->totalAmountSynced += $total;
            return;
        }

        // ── C. Resolve UPE product ──
        $webinar = DB::table('webinars')->where('id', $webinarId)->first();
        if (!$webinar) {
            $this->errors[] = "u#{$userId} w#{$webinarId}: webinar not found";
            return;
        }

        $productType = match ($webinar->type ?? 'course') {
            'webinar' => 'webinar',
            default => 'course_video',
        };

        $upeProduct = UpeProduct::firstOrCreate(
            ['external_id' => $webinarId, 'product_type' => $productType],
            [
                'name' => $webinar->slug ?? "webinar-{$webinarId}",
                'base_fee' => $webinar->price ?? 0,
                'validity_days' => $webinar->access_days ?? null,
                'status' => 'active',
            ]
        );

        // ── D. Find or create UPE sale (pricing_mode = installment) ──
        DB::transaction(function () use ($userId, $webinarId, $webinar, $upeProduct, $partPayments) {
            $upeSale = UpeSale::where('user_id', $userId)
                ->where('product_id', $upeProduct->id)
                ->where('pricing_mode', 'installment')
                ->whereIn('status', ['active', 'pending_payment', 'partially_refunded'])
                ->first();

            if ($upeSale) {
                $this->salesReused++;
            } else {
                $validFrom = $this->parseDate($partPayments->first()->created_at);
                $validUntil = ($webinar->access_days ?? null)
                    ? $validFrom->copy()->addDays($webinar->access_days)
                    : null;

                $upeSale = UpeSale::create([
                    'uuid' => (string) Str::uuid(),
                    'user_id' => $userId,
                    'product_id' => $upeProduct->id,
                    'sale_type' => 'paid',
                    'pricing_mode' => 'installment',
                    'base_fee_snapshot' => $webinar->price ?? 0,
                    'currency' => 'INR',
                    'status' => 'active',
                    'valid_from' => $validFrom,
                    'valid_until' => $validUntil,
                    'executed_at' => $validFrom,
                    'metadata' => json_encode([
                        'migrated_from' => 'part_payment_sync',
                        'webinar_id' => $webinarId,
                    ]),
                ]);
                $this->salesCreated++;
            }

            // ── E. Find or create installment plan ──
            $plan = UpeInstallmentPlan::where('sale_id', $upeSale->id)
                ->whereIn('status', ['active', 'completed'])
                ->first();

            if ($plan) {
                $this->plansReused++;
            } else {
                // Build plan from legacy InstallmentOrder if available
                $legacyOrder = DB::table('installment_orders')
                    ->where('user_id', $userId)
                    ->where('webinar_id', $webinarId)
                    ->whereIn('status', ['open', 'paying', 'part'])
                    ->first();

                $totalAmount = $legacyOrder->item_price ?? $webinar->price ?? 0;
                $numInstallments = 1;

                if ($legacyOrder) {
                    $stepCount = DB::table('installment_order_payments')
                        ->where('installment_order_id', $legacyOrder->id)
                        ->count();
                    $numInstallments = max($stepCount, 2);
                }

                $plan = UpeInstallmentPlan::create([
                    'sale_id' => $upeSale->id,
                    'total_amount' => $totalAmount,
                    'num_installments' => $numInstallments,
                    'plan_type' => 'standard',
                    'status' => 'active',
                ]);
                $this->plansCreated++;

                // Build schedules from legacy InstallmentOrderPayments
                $this->buildSchedulesFromLegacy($plan, $userId, $webinarId, $legacyOrder);
            }

            // ── F. Append ledger entries for each part-payment row ──
            // Then re-derive schedule statuses from ledger
            $this->appendPartPaymentLedgerEntries($upeSale, $plan, $partPayments);
            $this->refreshScheduleStatuses($plan);
        });
    }

    /**
     * Build UPE schedules from legacy InstallmentOrder + InstallmentOrderPayments.
     * If no legacy order exists, create a single catch-all schedule.
     */
    private function buildSchedulesFromLegacy(
        UpeInstallmentPlan $plan,
        int $userId,
        int $webinarId,
        ?object $legacyOrder
    ): void {
        if (!$legacyOrder) {
            // No installment order — create a single schedule for the full amount
            UpeInstallmentSchedule::create([
                'plan_id' => $plan->id,
                'sequence' => 1,
                'amount_due' => $plan->total_amount,
                'amount_paid' => 0,
                'due_date' => now()->toDateString(),
                'status' => 'due',
            ]);
            $this->schedulesCreated++;
            return;
        }

        $legacyPayments = DB::table('installment_order_payments')
            ->where('installment_order_id', $legacyOrder->id)
            ->orderBy('created_at')
            ->get();

        if ($legacyPayments->isEmpty()) {
            UpeInstallmentSchedule::create([
                'plan_id' => $plan->id,
                'sequence' => 1,
                'amount_due' => $plan->total_amount,
                'amount_paid' => 0,
                'due_date' => now()->toDateString(),
                'status' => 'due',
            ]);
            $this->schedulesCreated++;
            return;
        }

        $sequence = 0;
        foreach ($legacyPayments as $lp) {
            $sequence++;
            $dueDate = is_numeric($lp->created_at)
                ? Carbon::createFromTimestamp((int) $lp->created_at)->toDateString()
                : Carbon::parse($lp->created_at)->toDateString();

            UpeInstallmentSchedule::create([
                'plan_id' => $plan->id,
                'sequence' => $sequence,
                'amount_due' => $lp->amount,
                'amount_paid' => 0,       // will be derived from ledger
                'due_date' => $dueDate,
                'status' => 'due',        // will be refreshed after ledger sync
            ]);
            $this->schedulesCreated++;
        }
    }

    /**
     * For each webinar_part_payment row, create an immutable ledger entry
     * linked to the appropriate schedule (sequential accumulation).
     */
    private function appendPartPaymentLedgerEntries(
        UpeSale $upeSale,
        UpeInstallmentPlan $plan,
        $partPayments
    ): void {
        // Load schedules in order
        $schedules = $plan->schedules()
            ->orderBy('sequence')
            ->get();

        if ($schedules->isEmpty()) {
            return;
        }

        foreach ($partPayments as $pp) {
            $idempotencyKey = "legacy_part_payment_{$pp->id}";

            // Skip if already synced
            $existing = UpeLedgerEntry::where('idempotency_key', $idempotencyKey)->first();
            if ($existing) {
                $this->ledgerEntriesSkipped++;
                continue;
            }

            $amount = (float) $pp->amount;
            if ($amount <= 0) {
                $this->ledgerEntriesSkipped++;
                continue;
            }

            // Find the best schedule to link this payment to:
            // First unpaid/partially-paid schedule, determined by remaining amount from ledger
            $targetSchedule = $this->findTargetSchedule($schedules);

            $createdAt = $this->parseDate($pp->created_at);

            UpeLedgerEntry::create([
                'uuid' => (string) Str::uuid(),
                'sale_id' => $upeSale->id,
                'entry_type' => UpeLedgerEntry::TYPE_INSTALLMENT_PAYMENT,
                'direction' => UpeLedgerEntry::DIR_CREDIT,
                'amount' => $amount,
                'currency' => 'INR',
                'payment_method' => 'razorpay',
                'reference_type' => 'installment_schedule',
                'reference_id' => $targetSchedule->id,
                'description' => "Part-payment synced from legacy wpp#{$pp->id}",
                'idempotency_key' => $idempotencyKey,
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);

            $this->ledgerEntriesCreated++;
            $this->totalAmountSynced += $amount;

            // Refresh the in-memory schedule collection so next iteration sees updated ledger
            $schedules = $plan->schedules()
                ->orderBy('sequence')
                ->get();
        }
    }

    /**
     * Find the first schedule that still has remaining balance.
     * Falls back to the last schedule if all are fully paid (overpayment scenario).
     */
    private function findTargetSchedule($schedules): UpeInstallmentSchedule
    {
        foreach ($schedules as $schedule) {
            // Skip waived/paid schedules — they should not receive new payments
            if (in_array($schedule->status, ['waived', 'paid'])) {
                continue;
            }

            $paid = (float) UpeLedgerEntry::where('reference_type', 'installment_schedule')
                ->where('reference_id', $schedule->id)
                ->where('direction', 'credit')
                ->sum('amount');

            $debits = (float) UpeLedgerEntry::where('reference_type', 'installment_schedule')
                ->where('reference_id', $schedule->id)
                ->where('direction', 'debit')
                ->sum('amount');

            $netPaid = $paid - $debits;

            if ($netPaid < (float) $schedule->amount_due) {
                return $schedule;
            }
        }

        // All schedules fully paid — link to last non-waived schedule (overpayment)
        $lastNonWaived = $schedules->filter(fn($s) => $s->status !== 'waived')->last();
        return $lastNonWaived ?? $schedules->last();
    }

    /**
     * Re-derive schedule statuses from immutable ledger (source of truth).
     */
    private function refreshScheduleStatuses(UpeInstallmentPlan $plan): void
    {
        $schedules = $plan->schedules()->orderBy('sequence')->get();
        $allPaid = true;

        foreach ($schedules as $schedule) {
            $derivedPaid = (float) UpeLedgerEntry::where('reference_type', 'installment_schedule')
                ->where('reference_id', $schedule->id)
                ->sum('amount');

            $derivedPaid = round($derivedPaid, 2);
            $amountDue = (float) $schedule->amount_due;

            if ($derivedPaid >= $amountDue && $amountDue > 0) {
                $newStatus = 'paid';
            } elseif ($derivedPaid > 0) {
                $newStatus = 'partial';
                $allPaid = false;
            } else {
                $newStatus = 'due';
                $allPaid = false;
            }

            if ($schedule->status !== $newStatus || round((float) $schedule->amount_paid, 2) !== $derivedPaid) {
                $schedule->update([
                    'amount_paid' => $derivedPaid,
                    'status' => $newStatus,
                    'paid_at' => ($newStatus === 'paid' && !$schedule->paid_at) ? now() : $schedule->paid_at,
                ]);
                $this->schedulesUpdated++;
            }
        }

        // Update plan status
        if ($allPaid && $schedules->isNotEmpty()) {
            $plan->update(['status' => 'completed']);
        }
    }

    /**
     * Post-sync reconciliation: compare legacy totals vs UPE ledger totals.
     */
    private function runReconciliation(?string $filterUser, ?string $filterWebinar): void
    {
        $this->info('=== Reconciliation Check ===');

        $legacyQuery = DB::table('webinar_part_payment')
            ->select('user_id', 'webinar_id', DB::raw('SUM(amount) as legacy_total'), DB::raw('COUNT(*) as row_count'))
            ->groupBy('user_id', 'webinar_id');

        if ($filterUser) {
            $legacyQuery->where('user_id', (int) $filterUser);
        }
        if ($filterWebinar) {
            $legacyQuery->where('webinar_id', (int) $filterWebinar);
        }

        $legacyCombos = $legacyQuery->get();

        $mismatches = [];
        $matched = 0;

        foreach ($legacyCombos as $lc) {
            // Find UPE sale for this combo
            $upeProduct = UpeProduct::whereIn('product_type', ['course_video', 'course_live', 'webinar'])
                ->where('external_id', $lc->webinar_id)
                ->first();

            if (!$upeProduct) {
                $mismatches[] = [
                    "u#{$lc->user_id} w#{$lc->webinar_id}",
                    number_format($lc->legacy_total, 2),
                    '0.00',
                    'NO UPE PRODUCT',
                ];
                continue;
            }

            $upeSale = UpeSale::where('user_id', $lc->user_id)
                ->where('product_id', $upeProduct->id)
                ->where('pricing_mode', 'installment')
                ->first();

            if (!$upeSale) {
                $mismatches[] = [
                    "u#{$lc->user_id} w#{$lc->webinar_id}",
                    number_format($lc->legacy_total, 2),
                    '0.00',
                    'NO UPE SALE',
                ];
                continue;
            }

            // Sum ledger entries created by this sync (identified by idempotency prefix)
            $upeTotal = (float) UpeLedgerEntry::where('sale_id', $upeSale->id)
                ->where('idempotency_key', 'LIKE', 'legacy_part_payment_%')
                ->sum('amount');

            if (abs($lc->legacy_total - $upeTotal) > 0.01) {
                $mismatches[] = [
                    "u#{$lc->user_id} w#{$lc->webinar_id}",
                    number_format($lc->legacy_total, 2),
                    number_format($upeTotal, 2),
                    'MISMATCH (' . number_format($lc->legacy_total - $upeTotal, 2) . ')',
                ];
            } else {
                $matched++;
            }
        }

        $this->info("Matched: {$matched} / {$legacyCombos->count()}");

        if (!empty($mismatches)) {
            $this->warn('Mismatches found:');
            $this->table(['Combo', 'Legacy Total', 'UPE Total', 'Issue'], array_slice($mismatches, 0, 30));
        } else {
            $this->info('✅ All combos reconciled — legacy totals match UPE ledger totals');
        }
    }

    /**
     * Parse a created_at value that may be a unix timestamp or datetime string.
     */
    private function parseDate($value): Carbon
    {
        if (is_numeric($value)) {
            return Carbon::createFromTimestamp((int) $value);
        }
        return Carbon::parse($value);
    }
}
