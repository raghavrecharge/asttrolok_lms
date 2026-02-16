<?php

namespace App\Console\Commands;

use App\Models\PaymentEngine\UpeProduct;
use App\Models\PaymentEngine\UpeSale;
use App\Models\PaymentEngine\UpeLedgerEntry;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class UPESyncAccessControl extends Command
{
    protected $signature = 'upe:sync-access-control
                            {--dry-run : Show what would be synced without writing}';

    protected $description = 'One-way sync: read webinar_access_control entries and create UPE sales for any missing (idempotent)';

    private int $synced = 0;
    private int $skipped = 0;
    private int $errors = 0;
    private bool $dryRun = false;

    public function handle(): int
    {
        $this->dryRun = (bool) $this->option('dry-run');

        if ($this->dryRun) {
            $this->warn('🔍 DRY-RUN MODE — no data will be written');
        }

        $this->info('=== UPE WebinarAccessControl Sync ===');

        $entries = DB::table('webinar_access_control')->get();
        $this->info("Found {$entries->count()} WebinarAccessControl entries");

        $bar = $this->output->createProgressBar($entries->count());
        $bar->start();

        foreach ($entries as $entry) {
            try {
                $this->syncEntry($entry);
            } catch (\Throwable $e) {
                $this->errors++;
                if ($this->errors <= 5) {
                    $this->newLine();
                    $this->error("u#{$entry->user_id} w#{$entry->webinar_id}: {$e->getMessage()}");
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
        $webinar = DB::table('webinars')->where('id', $entry->webinar_id)->first();
        if (!$webinar) {
            $this->skipped++;
            return;
        }

        $productType = match ($webinar->type ?? 'course') {
            'webinar' => 'webinar',
            default => 'course_video',
        };

        if ($this->dryRun) {
            $exists = UpeProduct::whereIn('product_type', ['course_video', 'webinar'])
                ->where('external_id', $entry->webinar_id)
                ->first();

            if ($exists) {
                $hasUpeSale = UpeSale::where('user_id', $entry->user_id)
                    ->where('product_id', $exists->id)
                    ->whereIn('status', ['active', 'partially_refunded'])
                    ->exists();
                if ($hasUpeSale) {
                    $this->skipped++;
                    return;
                }
            }
            $this->synced++;
            return;
        }

        $upeProduct = UpeProduct::firstOrCreate(
            ['external_id' => $webinar->id, 'product_type' => $productType],
            [
                'name' => $webinar->slug ?? "webinar-{$webinar->id}",
                'base_fee' => $webinar->price ?? 0,
                'validity_days' => $webinar->access_days ?? null,
                'status' => 'active',
            ]
        );

        $existingSale = UpeSale::where('user_id', $entry->user_id)
            ->where('product_id', $upeProduct->id)
            ->whereIn('status', ['active', 'partially_refunded'])
            ->first();

        if ($existingSale) {
            $this->skipped++;
            return;
        }

        $validFrom = now();
        $validUntil = !empty($entry->expire)
            ? \Carbon\Carbon::parse($entry->expire)
            : ($webinar->access_days ? $validFrom->copy()->addDays($webinar->access_days) : null);

        $upeSale = UpeSale::create([
            'uuid' => (string) Str::uuid(),
            'user_id' => $entry->user_id,
            'product_id' => $upeProduct->id,
            'sale_type' => 'free',
            'pricing_mode' => 'free',
            'base_fee_snapshot' => 0,
            'status' => 'active',
            'valid_from' => $validFrom,
            'valid_until' => $validUntil,
            'metadata' => json_encode([
                'source' => 'webinar_access_control_sync',
                'wac_id' => $entry->id,
                'percentage' => $entry->percentage ?? null,
            ]),
        ]);

        // Ledger entry with 0 amount (admin grant, not a payment)
        UpeLedgerEntry::create([
            'uuid' => (string) Str::uuid(),
            'sale_id' => $upeSale->id,
            'entry_type' => UpeLedgerEntry::TYPE_PAYMENT,
            'direction' => UpeLedgerEntry::DIR_CREDIT,
            'amount' => 0.01, // Minimum positive for ledger constraint
            'currency' => 'INR',
            'payment_method' => 'system',
            'description' => "Admin-granted access via WebinarAccessControl #{$entry->id}",
            'idempotency_key' => "wac_sync_{$entry->id}",
        ]);

        \Illuminate\Support\Facades\Cache::forget(
            \App\Services\PaymentEngine\AccessEngine::CACHE_PREFIX . "{$entry->user_id}_{$upeProduct->id}"
        );

        $this->synced++;
    }
}
