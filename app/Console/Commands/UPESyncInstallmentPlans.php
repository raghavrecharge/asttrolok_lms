<?php

namespace App\Console\Commands;

use App\Models\PaymentEngine\UpeInstallmentPlan;
use App\Models\PaymentEngine\UpeInstallmentSchedule;
use App\Models\PaymentEngine\UpeSale;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class UPESyncInstallmentPlans extends Command
{
    protected $signature = 'upe:sync-installment-plans
                            {--dry-run : Show what would be synced without writing}
                            {--user= : Sync only for a specific user_id}';

    protected $description = 'Create UPE installment plans and schedules from legacy InstallmentOrder data for UPE sales missing plans';

    private int $plansCreated = 0;
    private int $schedulesCreated = 0;
    private int $skipped = 0;
    private int $errors = 0;
    private bool $dryRun = false;

    public function handle(): int
    {
        $this->dryRun = (bool) $this->option('dry-run');

        if ($this->dryRun) {
            $this->warn('DRY-RUN MODE — no data will be written');
        }

        $this->info('=== UPE Installment Plans Sync ===');

        // Find UPE sales with pricing_mode=installment that have no installment plan
        $query = UpeSale::where('pricing_mode', 'installment')
            ->whereDoesntHave('installmentPlan')
            ->with('product');

        if ($userId = $this->option('user')) {
            $query->where('user_id', (int) $userId);
        }

        $sales = $query->get();
        $this->info("Found {$sales->count()} installment sales without UPE plans");

        $bar = $this->output->createProgressBar($sales->count());
        $bar->start();

        foreach ($sales as $sale) {
            try {
                $this->syncSale($sale);
            } catch (\Throwable $e) {
                $this->errors++;
                if ($this->errors <= 10) {
                    $this->newLine();
                    $this->error("sale#{$sale->id} u#{$sale->user_id}: {$e->getMessage()}");
                }
            }
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->table(['Metric', 'Count'], [
            ['Plans created', $this->plansCreated],
            ['Schedules created', $this->schedulesCreated],
            ['Skipped (no legacy data)', $this->skipped],
            ['Errors', $this->errors],
        ]);

        return Command::SUCCESS;
    }

    private function syncSale(UpeSale $sale): void
    {
        if (!$sale->product) {
            $this->skipped++;
            return;
        }

        $webinarId = $sale->product->external_id;

        // Find legacy installment orders for this user+webinar
        // Pick the most recent active (non-paying) order
        $legacyOrder = DB::table('installment_orders')
            ->where('user_id', $sale->user_id)
            ->where('webinar_id', $webinarId)
            ->whereIn('status', ['open', 'pending_verification'])
            ->orderByDesc('id')
            ->first();

        if (!$legacyOrder) {
            $this->skipped++;
            return;
        }

        // Get the installment config
        $installmentConfig = DB::table('installments')->where('id', $legacyOrder->installment_id)->first();

        // Get all payments for this order
        $payments = DB::table('installment_order_payments')
            ->where('installment_order_id', $legacyOrder->id)
            ->orderBy('id')
            ->get();

        if ($payments->isEmpty()) {
            $this->skipped++;
            return;
        }

        // Get installment steps from config
        $steps = collect();
        if ($installmentConfig) {
            $steps = DB::table('installment_steps')
                ->where('installment_id', $installmentConfig->id)
                ->orderBy('deadline')
                ->get();
        }

        // Calculate total amount (item_price from order)
        $totalAmount = (float) $legacyOrder->item_price;
        if ($totalAmount <= 0) {
            $totalAmount = $sale->base_fee_snapshot;
        }

        // Build schedule entries: upfront + steps
        // Upfront percentage from config
        $upfrontPercent = $installmentConfig ? (float) ($installmentConfig->upfront ?? 0) : 0;
        $upfrontAmount = round($totalAmount * $upfrontPercent / 100, 2);

        $scheduleEntries = [];
        $sequence = 1;

        // Schedule 1: Upfront
        $scheduleEntries[] = [
            'sequence' => $sequence++,
            'amount_due' => $upfrontAmount > 0 ? $upfrontAmount : ($payments->first() ? (float) $payments->first()->amount : 0),
            'due_date' => $legacyOrder->created_at
                ? date('Y-m-d', is_numeric($legacyOrder->created_at) ? $legacyOrder->created_at : strtotime($legacyOrder->created_at))
                : now()->format('Y-m-d'),
        ];

        // Subsequent steps
        $orderCreatedTs = is_numeric($legacyOrder->created_at) ? $legacyOrder->created_at : strtotime($legacyOrder->created_at);
        $cumulativeDeadlineDays = 0;

        foreach ($steps as $step) {
            $stepPercent = (float) ($step->amount ?? 0);
            $stepAmount = round($totalAmount * $stepPercent / 100, 2);
            $cumulativeDeadlineDays += (int) ($step->deadline ?? 30);

            $dueDate = date('Y-m-d', $orderCreatedTs + ($cumulativeDeadlineDays * 86400));

            $scheduleEntries[] = [
                'sequence' => $sequence++,
                'amount_due' => $stepAmount,
                'due_date' => $dueDate,
            ];
        }

        // Now map payments to schedules
        $paidPayments = $payments->where('status', 'paid')->values();
        $numSchedules = count($scheduleEntries);
        $totalPaid = $paidPayments->sum('amount');

        // Determine status for each schedule based on paid payments
        // Simple approach: mark schedules as paid in order up to number of paid payments
        foreach ($scheduleEntries as $i => &$entry) {
            if ($i < $paidPayments->count()) {
                $pmt = $paidPayments[$i];
                $entry['status'] = 'paid';
                $entry['amount_paid'] = (float) $pmt->amount;
                $entry['paid_at'] = $pmt->created_at;
            } else {
                // Check if there's a partial/paying payment
                $nextPayment = $payments->whereIn('status', ['paying', 'part'])->first();
                if ($nextPayment && $i == $paidPayments->count()) {
                    $entry['status'] = 'due';
                    $entry['amount_paid'] = 0;
                } else {
                    $entry['status'] = 'upcoming';
                    $entry['amount_paid'] = 0;
                    // Check if overdue
                    if (strtotime($entry['due_date']) < time()) {
                        $entry['status'] = 'overdue';
                    }
                }
            }
        }
        unset($entry);

        if ($this->dryRun) {
            $this->plansCreated++;
            $this->schedulesCreated += count($scheduleEntries);
            return;
        }

        // Determine plan status
        $allPaid = collect($scheduleEntries)->every(fn($e) => $e['status'] === 'paid');
        $planStatus = $allPaid ? 'completed' : 'active';

        // Create the plan
        $plan = UpeInstallmentPlan::create([
            'sale_id' => $sale->id,
            'total_amount' => $totalAmount,
            'num_installments' => count($scheduleEntries),
            'plan_type' => 'standard',
            'status' => $planStatus,
        ]);

        $this->plansCreated++;

        // Create schedules
        foreach ($scheduleEntries as $entry) {
            UpeInstallmentSchedule::create([
                'plan_id' => $plan->id,
                'sequence' => $entry['sequence'],
                'amount_due' => $entry['amount_due'],
                'amount_paid' => $entry['amount_paid'],
                'due_date' => $entry['due_date'],
                'status' => $entry['status'],
                'paid_at' => isset($entry['paid_at']) && $entry['paid_at']
                    ? (is_numeric($entry['paid_at']) ? date('Y-m-d H:i:s', $entry['paid_at']) : $entry['paid_at'])
                    : null,
            ]);
            $this->schedulesCreated++;
        }
    }
}
