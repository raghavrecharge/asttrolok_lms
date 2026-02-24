<?php

namespace App\Console\Commands;

use App\Models\PaymentEngine\UpeProduct;
use App\Models\PaymentEngine\UpeSale;
use App\Models\PaymentEngine\UpeLedgerEntry;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class UPESyncLegacySales extends Command
{
    protected $signature = 'upe:sync-legacy-sales
                            {--dry-run : Show what would be synced without writing}
                            {--user= : Sync only for a specific user_id}';

    protected $description = 'One-way sync: create UPE sales for legacy Sale records (type=webinar) that have no UPE equivalent (idempotent)';

    private int $synced = 0;
    private int $skipped = 0;
    private int $errors = 0;
    private bool $dryRun = false;

    public function handle(): int
    {
        $this->dryRun = (bool) $this->option('dry-run');

        if ($this->dryRun) {
            $this->warn('DRY-RUN MODE — no data will be written');
        }

        $this->info('=== UPE Legacy Sales Sync ===');

        $query = DB::table('sales')
            ->whereNull('refund_at')
            ->where('access_to_purchased_item', true)
            ->whereNotNull('webinar_id')
            ->where('type', 'webinar')
            ->select('id', 'buyer_id', 'webinar_id', 'total_amount', 'payment_method', 'created_at');

        if ($userId = $this->option('user')) {
            $query->where('buyer_id', (int) $userId);
        }

        $entries = $query->get();

        // Deduplicate: keep latest sale per user+webinar
        $grouped = $entries->groupBy(function ($e) {
            return $e->buyer_id . '_' . $e->webinar_id;
        });

        $unique = $grouped->map(fn($group) => $group->sortByDesc('id')->first());

        $this->info("Found {$entries->count()} legacy sales ({$unique->count()} unique user+webinar combos)");

        $bar = $this->output->createProgressBar($unique->count());
        $bar->start();

        foreach ($unique as $entry) {
            try {
                $this->syncEntry($entry);
            } catch (\Throwable $e) {
                $this->errors++;
                if ($this->errors <= 10) {
                    $this->newLine();
                    $this->error("sale#{$entry->id} u#{$entry->buyer_id} w#{$entry->webinar_id}: {$e->getMessage()}");
                }
            }
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->table(['Metric', 'Count'], [
            ['UPE sales created', $this->synced],
            ['Already existed (skipped)', $this->skipped],
            ['Errors', $this->errors],
        ]);

        return Command::SUCCESS;
    }

    private function syncEntry(object $entry): void
    {
        // Check user exists
        $userExists = DB::table('users')->where('id', $entry->buyer_id)->exists();
        if (!$userExists) {
            $this->skipped++;
            return;
        }

        $webinar = DB::table('webinars')->where('id', $entry->webinar_id)->first();
        if (!$webinar) {
            $this->skipped++;
            return;
        }

        $productType = match ($webinar->type ?? 'course') {
            'webinar' => 'webinar',
            default => 'course_video',
        };

        // Check if UPE sale already exists for this user+product
        $upeProduct = UpeProduct::whereIn('product_type', ['course_video', 'webinar'])
            ->where('external_id', $entry->webinar_id)
            ->first();

        if ($upeProduct) {
            $existingSale = UpeSale::where('user_id', $entry->buyer_id)
                ->where('product_id', $upeProduct->id)
                ->whereIn('status', ['active', 'partially_refunded', 'pending_payment'])
                ->exists();

            if ($existingSale) {
                $this->skipped++;
                return;
            }
        }

        if ($this->dryRun) {
            $this->synced++;
            return;
        }

        // Create product if needed
        if (!$upeProduct) {
            $upeProduct = UpeProduct::firstOrCreate(
                ['external_id' => $webinar->id, 'product_type' => $productType],
                [
                    'name' => $webinar->slug ?? "webinar-{$webinar->id}",
                    'base_fee' => $webinar->price ?? 0,
                    'validity_days' => $webinar->access_days ?? null,
                    'status' => 'active',
                ]
            );
        }

        $amount = (float) ($entry->total_amount ?? 0);
        $isPaid = $amount > 0;

        $validFrom = $entry->created_at
            ? \Carbon\Carbon::createFromTimestamp($entry->created_at)
            : now();
        $validUntil = $webinar->access_days
            ? $validFrom->copy()->addDays($webinar->access_days)
            : null;

        $upeSale = UpeSale::create([
            'uuid' => (string) Str::uuid(),
            'user_id' => $entry->buyer_id,
            'product_id' => $upeProduct->id,
            'sale_type' => $isPaid ? 'paid' : 'free',
            'pricing_mode' => $isPaid ? 'full' : 'free',
            'base_fee_snapshot' => $webinar->price ?? 0,
            'status' => 'active',
            'valid_from' => $validFrom,
            'valid_until' => $validUntil,
            'metadata' => json_encode([
                'source' => 'legacy_sale_sync',
                'legacy_sale_id' => $entry->id,
                'original_amount' => $amount,
            ]),
        ]);

        // Ledger entry for the payment
        if ($amount > 0) {
            // Normalize payment_method to match enum: cash,bank_transfer,razorpay,paypal,stripe,payment_link,wallet,system
            $method = $entry->payment_method ?? 'system';
            $allowedMethods = ['cash', 'bank_transfer', 'razorpay', 'paypal', 'stripe', 'payment_link', 'wallet', 'system'];
            if (!in_array($method, $allowedMethods)) {
                $method = 'razorpay'; // Most legacy payments were via Razorpay
            }

            UpeLedgerEntry::create([
                'uuid' => (string) Str::uuid(),
                'sale_id' => $upeSale->id,
                'entry_type' => UpeLedgerEntry::TYPE_PAYMENT,
                'direction' => UpeLedgerEntry::DIR_CREDIT,
                'amount' => $amount,
                'currency' => 'INR',
                'payment_method' => $method,
                'description' => "Synced from legacy Sale #{$entry->id}",
                'idempotency_key' => "legacy_sale_sync_{$entry->id}",
            ]);
        }

        Cache::forget(
            \App\Services\PaymentEngine\AccessEngine::CACHE_PREFIX . "{$entry->buyer_id}_{$upeProduct->id}"
        );

        $this->synced++;
    }
}
