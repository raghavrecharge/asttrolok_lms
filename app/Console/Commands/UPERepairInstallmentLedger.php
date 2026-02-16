<?php

namespace App\Console\Commands;

use App\Models\PaymentEngine\UpeInstallmentPlan;
use App\Models\PaymentEngine\UpeInstallmentSchedule;
use App\Models\PaymentEngine\UpeLedgerEntry;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class UPERepairInstallmentLedger extends Command
{
    protected $signature = 'upe:repair-installment-ledger
                            {--dry-run : Show what would be fixed without writing}
                            {--sale-id= : Fix a specific sale only}';

    protected $description = 'Repair orphaned generic payment ledger entries that should be linked to installment Schedule #1';

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');
        $saleIdFilter = $this->option('sale-id');

        if ($dryRun) {
            $this->warn('DRY RUN MODE — no data will be changed');
        }

        $this->info('Scanning for orphaned first-payment ledger entries...');

        $query = UpeInstallmentPlan::with(['schedules', 'sale']);
        if ($saleIdFilter) {
            $query->where('sale_id', $saleIdFilter);
        }
        $plans = $query->get();

        $fixed = 0;
        $skipped = 0;
        $errors = 0;

        foreach ($plans as $plan) {
            $sched1 = $plan->schedules->where('sequence', 1)->first();
            if (!$sched1) {
                continue;
            }

            // Skip if Schedule #1 already has payment linked
            if ($sched1->amount_paid > 0 && $sched1->ledger_entry_id) {
                continue;
            }

            // Find the orphaned generic 'payment' credit in the ledger for this sale
            $genericPayment = UpeLedgerEntry::where('sale_id', $plan->sale_id)
                ->where('entry_type', UpeLedgerEntry::TYPE_PAYMENT)
                ->where('direction', UpeLedgerEntry::DIR_CREDIT)
                ->whereNull('reference_type')
                ->first();

            if (!$genericPayment) {
                // Also check for entries with empty reference_type
                $genericPayment = UpeLedgerEntry::where('sale_id', $plan->sale_id)
                    ->where('entry_type', UpeLedgerEntry::TYPE_PAYMENT)
                    ->where('direction', UpeLedgerEntry::DIR_CREDIT)
                    ->where(function ($q) {
                        $q->whereNull('reference_type')
                          ->orWhere('reference_type', '');
                    })
                    ->first();
            }

            if (!$genericPayment) {
                continue;
            }

            $ledgerAmount = (float) $genericPayment->amount;
            $schedDue = (float) $sched1->amount_due;

            $this->line(
                "  Sale #{$plan->sale_id} | Plan #{$plan->id} | Sched #{$sched1->id} " .
                "(due: {$schedDue}) | Ledger #{$genericPayment->id} (amt: {$ledgerAmount})"
            );

            if ($dryRun) {
                $fixed++;
                continue;
            }

            try {
                DB::transaction(function () use ($plan, $sched1, $genericPayment, $ledgerAmount, $schedDue) {
                    // 1. Reclassify the generic ledger entry as installment_payment
                    //    (Ledger is immutable via model events, so use DB::table directly)
                    DB::table('upe_ledger_entries')
                        ->where('id', $genericPayment->id)
                        ->update([
                            'entry_type' => UpeLedgerEntry::TYPE_INSTALLMENT_PAYMENT,
                            'reference_type' => 'installment_schedule',
                            'reference_id' => $sched1->id,
                        ]);

                    // 2. Update Schedule #1 with the payment info
                    $paidAmount = min($ledgerAmount, $schedDue);
                    $newStatus = ($paidAmount >= $schedDue) ? 'paid' : 'partial';

                    DB::table('upe_installment_schedules')
                        ->where('id', $sched1->id)
                        ->update([
                            'amount_paid' => $paidAmount,
                            'status' => $newStatus,
                            'ledger_entry_id' => $genericPayment->id,
                            'paid_at' => $genericPayment->created_at,
                        ]);
                });

                $fixed++;
                $this->info("    ✓ Fixed: ledger #{$genericPayment->id} → Schedule #{$sched1->id} ({$ledgerAmount})");
            } catch (\Throwable $e) {
                $errors++;
                $this->error("    ✗ Error: " . $e->getMessage());
            }
        }

        $this->newLine();
        $this->info('=== Repair Summary ===');
        $this->table(['Metric', 'Count'], [
            ['Fixed', $fixed],
            ['Errors', $errors],
            ['Total plans scanned', $plans->count()],
        ]);

        if ($dryRun && $fixed > 0) {
            $this->warn("Run without --dry-run to apply these fixes.");
        }

        return $errors > 0 ? Command::FAILURE : Command::SUCCESS;
    }
}
