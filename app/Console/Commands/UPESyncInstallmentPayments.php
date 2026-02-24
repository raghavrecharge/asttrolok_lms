<?php

namespace App\Console\Commands;

use App\Models\PaymentEngine\UpeProduct;
use App\Models\PaymentEngine\UpeSale;
use App\Models\PaymentEngine\UpeLedgerEntry;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class UPESyncInstallmentPayments extends Command
{
    protected $signature = 'upe:sync-installment-payments
                            {--dry-run : Show what would be synced without writing}
                            {--user= : Sync only for a specific user_id}';

    protected $description = 'Sync paid InstallmentOrderPayments into UPE ledger entries for matching UPE sales (idempotent)';

    private int $salesCreated = 0;
    private int $ledgerCreated = 0;
    private int $skipped = 0;
    private int $errors = 0;
    private bool $dryRun = false;

    public function handle(): int
    {
        $this->dryRun = (bool) $this->option('dry-run');

        if ($this->dryRun) {
            $this->warn('DRY-RUN MODE — no data will be written');
        }

        $this->info('=== UPE Installment Payments Sync ===');

        $orderQuery = DB::table('installment_orders as io')
            ->join('installment_order_payments as iop', 'iop.installment_order_id', '=', 'io.id')
            ->where('iop.status', 'paid')
            ->select('io.user_id', 'io.webinar_id', 'io.id as order_id', 'iop.id as payment_id', 'iop.amount', 'iop.created_at as paid_at');

        if ($userId = $this->option('user')) {
            $orderQuery->where('io.user_id', (int) $userId);
        }

        $payments = $orderQuery->get();
        $this->info("Found {$payments->count()} paid installment_order_payments");

        $bar = $this->output->createProgressBar($payments->count());
        $bar->start();

        foreach ($payments as $payment) {
            try {
                $this->syncPayment($payment);
            } catch (\Throwable $e) {
                $this->errors++;
                if ($this->errors <= 10) {
                    $this->newLine();
                    $this->error("iop#{$payment->payment_id} u#{$payment->user_id} w#{$payment->webinar_id}: {$e->getMessage()}");
                }
            }
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->table(['Metric', 'Count'], [
            ['UPE sales created/found', $this->salesCreated],
            ['Ledger entries created', $this->ledgerCreated],
            ['Already existed (skipped)', $this->skipped],
            ['Errors', $this->errors],
        ]);

        return Command::SUCCESS;
    }

    private function syncPayment(object $payment): void
    {
        $webinar = DB::table('webinars')->where('id', $payment->webinar_id)->first();
        if (!$webinar) {
            $this->skipped++;
            return;
        }

        $userExists = DB::table('users')->where('id', $payment->user_id)->exists();
        if (!$userExists) {
            $this->skipped++;
            return;
        }

        $amount = (float) $payment->amount;
        if ($amount <= 0) {
            $this->skipped++;
            return;
        }

        $idempotencyKey = "iop_sync_{$payment->payment_id}";

        // Check if ledger entry already exists
        $exists = DB::table('upe_ledger_entries')
            ->where('idempotency_key', $idempotencyKey)
            ->exists();

        if ($exists) {
            $this->skipped++;
            return;
        }

        if ($this->dryRun) {
            $this->ledgerCreated++;
            return;
        }

        $productType = match ($webinar->type ?? 'course') {
            'webinar' => 'webinar',
            default => 'course_video',
        };

        // Find or create UPE product
        $upeProduct = UpeProduct::firstOrCreate(
            ['external_id' => $webinar->id, 'product_type' => $productType],
            [
                'name' => $webinar->slug ?? "webinar-{$webinar->id}",
                'base_fee' => $webinar->price ?? 0,
                'validity_days' => $webinar->access_days ?? null,
                'status' => 'active',
            ]
        );

        // Find or create UPE sale for this user+product
        $upeSale = UpeSale::where('user_id', $payment->user_id)
            ->where('product_id', $upeProduct->id)
            ->whereIn('status', ['active', 'partially_refunded', 'pending_payment'])
            ->orderByDesc('id')
            ->first();

        if (!$upeSale) {
            $upeSale = UpeSale::create([
                'uuid' => (string) Str::uuid(),
                'user_id' => $payment->user_id,
                'product_id' => $upeProduct->id,
                'sale_type' => 'paid',
                'pricing_mode' => 'installment',
                'base_fee_snapshot' => $webinar->price ?? 0,
                'status' => 'active',
                'valid_from' => now(),
                'valid_until' => $webinar->access_days ? now()->addDays($webinar->access_days) : null,
                'metadata' => json_encode([
                    'source' => 'installment_payment_sync',
                    'order_id' => $payment->order_id,
                ]),
            ]);
            $this->salesCreated++;
        }

        // Create ledger entry
        UpeLedgerEntry::create([
            'uuid' => (string) Str::uuid(),
            'sale_id' => $upeSale->id,
            'entry_type' => UpeLedgerEntry::TYPE_PAYMENT,
            'direction' => UpeLedgerEntry::DIR_CREDIT,
            'amount' => $amount,
            'currency' => 'INR',
            'payment_method' => 'razorpay',
            'description' => "Synced from InstallmentOrderPayment #{$payment->payment_id}",
            'idempotency_key' => $idempotencyKey,
        ]);

        Cache::forget(
            \App\Services\PaymentEngine\AccessEngine::CACHE_PREFIX . "{$payment->user_id}_{$upeProduct->id}"
        );

        $this->ledgerCreated++;
    }
}
