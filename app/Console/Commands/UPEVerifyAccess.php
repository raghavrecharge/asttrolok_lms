<?php

namespace App\Console\Commands;

use App\Models\PaymentEngine\UpeProduct;
use App\Models\PaymentEngine\UpeSale;
use App\Models\PaymentEngine\UpeInstallmentPlan;
use App\Models\PaymentEngine\UpeSubscription;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class UPEVerifyAccess extends Command
{
    protected $signature = 'upe:verify-access
                            {--export=storage/app/upe_affected_users.csv : Path to export CSV}
                            {--check=all : Check specific category: sales|installments|subscriptions|wac|all}';

    protected $description = 'Compare legacy access vs UPE access and generate affected user IDs report';

    private array $affectedUsers = [];
    private int $totalLegacyAccess = 0;
    private int $totalUpeAccess = 0;
    private int $totalMissing = 0;

    public function handle(): int
    {
        $check = $this->option('check');
        $exportPath = $this->option('export');

        $this->info('=== UPE Access Verification Report ===');
        $this->info('Generated: ' . now()->format('Y-m-d H:i:s'));
        $this->newLine();

        if (in_array($check, ['all', 'sales'])) {
            $this->verifySalesAccess();
        }
        if (in_array($check, ['all', 'installments'])) {
            $this->verifyInstallmentAccess();
        }
        if (in_array($check, ['all', 'subscriptions'])) {
            $this->verifySubscriptionAccess();
        }
        if (in_array($check, ['all', 'wac'])) {
            $this->verifyWebinarAccessControl();
        }

        $this->newLine();
        $this->info('=== Summary ===');
        $this->table(
            ['Metric', 'Count'],
            [
                ['Total legacy access records', $this->totalLegacyAccess],
                ['Total matched in UPE', $this->totalUpeAccess],
                ['Total MISSING in UPE (affected users)', $this->totalMissing],
            ]
        );

        if (!empty($this->affectedUsers)) {
            $this->exportCSV($exportPath);
            $this->newLine();
            $this->warn("⚠ {$this->totalMissing} users need manual access via support channel.");
            $this->info("CSV exported to: {$exportPath}");

            // Show breakdown by risk category
            $this->newLine();
            $this->info('=== Breakdown by Risk Category ===');
            $categories = [];
            foreach ($this->affectedUsers as $user) {
                $cat = $user['risk_category'];
                $categories[$cat] = ($categories[$cat] ?? 0) + 1;
            }
            $catTable = [];
            foreach ($categories as $cat => $count) {
                $catTable[] = [$cat, $count];
            }
            $this->table(['Risk Category', 'Count'], $catTable);
        } else {
            $this->info('✅ All legacy access records matched in UPE. No affected users.');
        }

        return Command::SUCCESS;
    }

    // ────────────────────────────────────────
    // CHECK 1: Sales-based access
    // ────────────────────────────────────────
    private function verifySalesAccess(): void
    {
        $this->info('Checking: Sales-based course access...');

        // Find all legacy sales granting active access
        $legacySales = DB::table('sales as s')
            ->join('users as u', 'u.id', '=', 's.buyer_id')
            ->leftJoin('webinars as w', 'w.id', '=', 's.webinar_id')
            ->leftJoin('bundles as b', 'b.id', '=', 's.bundle_id')
            ->select([
                's.id as sale_id',
                's.buyer_id as user_id',
                'u.email',
                'u.full_name',
                'u.mobile',
                's.webinar_id',
                's.bundle_id',
                's.type as sale_type',
                's.payment_method',
                's.amount',
                's.total_amount',
                's.manual_added',
                's.access_to_purchased_item',
                's.refund_at',
                's.created_at',
                'w.slug as webinar_slug',
                'w.access_days as webinar_access_days',
                DB::raw("COALESCE(w.slug, b.slug, 'unknown') as course_name"),
            ])
            ->whereNull('s.refund_at')
            ->where('s.access_to_purchased_item', 1)
            ->where('u.email', 'NOT LIKE', '%@rechargestudio.com')
            ->where(function ($q) {
                $q->whereNotNull('s.webinar_id')
                    ->orWhereNotNull('s.bundle_id');
            })
            // Skip installment_payment type — handled separately
            ->where('s.type', '!=', 'installment_payment')
            ->orderBy('s.buyer_id')
            ->get();

        $this->totalLegacyAccess += $legacySales->count();

        foreach ($legacySales as $ls) {
            // Check if access has expired based on access_days
            if ($ls->webinar_access_days) {
                $saleDate = Carbon::createFromTimestamp($ls->created_at);
                $expiresAt = $saleDate->copy()->addDays($ls->webinar_access_days);
                if ($expiresAt->isPast()) {
                    // Already expired — not an active access, skip
                    $this->totalLegacyAccess--;
                    continue;
                }
            }

            // Determine product type and external_id
            $externalId = $ls->webinar_id ?? $ls->bundle_id;
            $productType = $ls->webinar_id ? 'webinar' : 'bundle';

            // Find matching UPE sale
            $productTypes = $productType === 'webinar'
                ? ['course_video', 'course_live', 'webinar']
                : ['bundle'];

            $hasUpeAccess = UpeSale::where('user_id', $ls->user_id)
                ->whereIn('status', ['active', 'partially_refunded'])
                ->whereHas('product', function ($q) use ($productTypes, $externalId) {
                    $q->whereIn('product_type', $productTypes)
                        ->where('external_id', $externalId);
                })
                ->where(function ($q) {
                    $q->whereNull('valid_until')
                        ->orWhere('valid_until', '>', now());
                })
                ->exists();

            if ($hasUpeAccess) {
                $this->totalUpeAccess++;
            } else {
                $this->totalMissing++;
                $this->affectedUsers[] = [
                    'user_id' => $ls->user_id,
                    'email' => $ls->email,
                    'full_name' => $ls->full_name,
                    'mobile' => $ls->mobile,
                    'course_name' => $ls->course_name,
                    'webinar_id' => $ls->webinar_id,
                    'bundle_id' => $ls->bundle_id,
                    'access_type' => $ls->manual_added ? 'manual_grant' : ($ls->payment_method ?? 'unknown'),
                    'amount_paid' => $ls->total_amount ?? $ls->amount,
                    'purchase_date' => Carbon::createFromTimestamp($ls->created_at)->format('Y-m-d'),
                    'risk_category' => 'SALE_NOT_IN_UPE',
                    'risk_reason' => 'Legacy sale exists with active access but no matching UPE sale found',
                    'legacy_sale_id' => $ls->sale_id,
                ];
            }
        }

        $matched = $this->totalUpeAccess;
        $missing = count(array_filter($this->affectedUsers, fn($u) => $u['risk_category'] === 'SALE_NOT_IN_UPE'));
        $this->info("  → Sales: {$legacySales->count()} legacy, {$matched} matched, {$missing} missing");
    }

    // ────────────────────────────────────────
    // CHECK 2: Installment-based access
    // ────────────────────────────────────────
    private function verifyInstallmentAccess(): void
    {
        $this->info('Checking: Installment-based course access...');

        $installmentOrders = DB::table('installment_orders as io')
            ->join('users as u', 'u.id', '=', 'io.user_id')
            ->leftJoin('webinars as w', 'w.id', '=', 'io.webinar_id')
            ->select([
                'io.id as installment_order_id',
                'io.user_id',
                'u.email',
                'u.full_name',
                'u.mobile',
                'io.webinar_id',
                'io.bundle_id',
                'io.item_price',
                'io.status as io_status',
                'io.discount',
                'io.created_at',
                'w.slug as course_name',
                'w.access_days as webinar_access_days',
            ])
            ->whereIn('io.status', ['open', 'paying', 'part'])
            ->whereNull('io.refund_at')
            ->where('u.email', 'NOT LIKE', '%@rechargestudio.com')
            ->get();

        $this->totalLegacyAccess += $installmentOrders->count();
        $matchedCount = 0;
        $missingCount = 0;

        foreach ($installmentOrders as $io) {
            $externalId = $io->webinar_id ?? $io->bundle_id;
            $productType = $io->webinar_id ? 'webinar' : 'bundle';
            $productTypes = $productType === 'webinar'
                ? ['course_video', 'course_live', 'webinar']
                : ['bundle'];

            // Check UPE: sale with installment pricing mode for this user+product
            $hasUpeAccess = UpeSale::where('user_id', $io->user_id)
                ->whereIn('status', ['active', 'pending_payment'])
                ->where('pricing_mode', 'installment')
                ->whereHas('product', function ($q) use ($productTypes, $externalId) {
                    $q->whereIn('product_type', $productTypes)
                        ->where('external_id', $externalId);
                })
                ->exists();

            if ($hasUpeAccess) {
                $matchedCount++;
                $this->totalUpeAccess++;
            } else {
                $missingCount++;
                $this->totalMissing++;

                // Calculate total paid so far
                $totalPaid = DB::table('installment_order_payments')
                    ->where('installment_order_id', $io->installment_order_id)
                    ->where('status', 'paid')
                    ->sum('amount');

                $this->affectedUsers[] = [
                    'user_id' => $io->user_id,
                    'email' => $io->email,
                    'full_name' => $io->full_name,
                    'mobile' => $io->mobile,
                    'course_name' => $io->course_name ?? 'unknown',
                    'webinar_id' => $io->webinar_id,
                    'bundle_id' => $io->bundle_id,
                    'access_type' => 'installment',
                    'amount_paid' => $totalPaid,
                    'purchase_date' => Carbon::createFromTimestamp($io->created_at)->format('Y-m-d'),
                    'risk_category' => 'INSTALLMENT_NOT_IN_UPE',
                    'risk_reason' => "Installment order #{$io->installment_order_id} (status={$io->io_status}) not migrated. Paid ₹{$totalPaid} of ₹{$io->item_price}",
                    'legacy_sale_id' => null,
                ];
            }
        }

        $this->info("  → Installments: {$installmentOrders->count()} legacy, {$matchedCount} matched, {$missingCount} missing");
    }

    // ────────────────────────────────────────
    // CHECK 3: Subscription-based access
    // ────────────────────────────────────────
    private function verifySubscriptionAccess(): void
    {
        $this->info('Checking: Subscription-based access...');

        $now = time();

        $subAccesses = DB::table('subscription_access as sa')
            ->join('users as u', 'u.id', '=', 'sa.user_id')
            ->leftJoin('subscriptions as s', 's.id', '=', 'sa.subscription_id')
            ->select([
                'sa.id',
                'sa.user_id',
                'u.email',
                'u.full_name',
                'u.mobile',
                'sa.subscription_id',
                'sa.access_till_date',
                'sa.paid_no_of_subscriptions',
                'sa.access_content_count',
                'sa.created_at',
                's.slug as subscription_name',
            ])
            ->where('sa.access_till_date', '>', $now)
            ->where('u.email', 'NOT LIKE', '%@rechargestudio.com')
            ->get();

        $this->totalLegacyAccess += $subAccesses->count();
        $matchedCount = 0;
        $missingCount = 0;

        foreach ($subAccesses as $sa) {
            $hasUpeAccess = UpeSubscription::where('user_id', $sa->user_id)
                ->whereIn('status', ['active', 'trial', 'grace'])
                ->whereHas('product', function ($q) use ($sa) {
                    $q->where('product_type', 'subscription')
                        ->where('external_id', $sa->subscription_id);
                })
                ->exists();

            if ($hasUpeAccess) {
                $matchedCount++;
                $this->totalUpeAccess++;
            } else {
                $missingCount++;
                $this->totalMissing++;

                $createdAtUnix = is_numeric($sa->created_at) ? (int) $sa->created_at : strtotime($sa->created_at);

                $this->affectedUsers[] = [
                    'user_id' => $sa->user_id,
                    'email' => $sa->email,
                    'full_name' => $sa->full_name,
                    'mobile' => $sa->mobile,
                    'course_name' => $sa->subscription_name ?? "subscription_{$sa->subscription_id}",
                    'webinar_id' => null,
                    'bundle_id' => null,
                    'access_type' => 'subscription',
                    'amount_paid' => null,
                    'purchase_date' => Carbon::createFromTimestamp($createdAtUnix)->format('Y-m-d'),
                    'risk_category' => 'SUBSCRIPTION_NOT_IN_UPE',
                    'risk_reason' => "Active subscription access (till " . Carbon::createFromTimestamp($sa->access_till_date)->format('Y-m-d') . ") not migrated. Paid {$sa->paid_no_of_subscriptions} cycles.",
                    'legacy_sale_id' => null,
                ];
            }
        }

        $this->info("  → Subscriptions: {$subAccesses->count()} active legacy, {$matchedCount} matched, {$missingCount} missing");
    }

    // ────────────────────────────────────────
    // CHECK 4: WebinarAccessControl-only access
    // ────────────────────────────────────────
    private function verifyWebinarAccessControl(): void
    {
        $this->info('Checking: WebinarAccessControl-only access (support grants)...');

        // Find all active WAC entries where expire is in the future
        $wacRecords = DB::table('webinar_access_control as wac')
            ->join('users as u', 'u.id', '=', 'wac.user_id')
            ->leftJoin('webinars as w', 'w.id', '=', 'wac.webinar_id')
            ->select([
                'wac.id',
                'wac.user_id',
                'u.email',
                'u.full_name',
                'u.mobile',
                'wac.webinar_id',
                'wac.percentage',
                'wac.expire',
                'wac.created_at',
                'w.slug as course_name',
            ])
            ->where('wac.expire', '>', now())
            ->where('u.email', 'NOT LIKE', '%@rechargestudio.com')
            ->get();

        $this->totalLegacyAccess += $wacRecords->count();
        $matchedCount = 0;
        $missingCount = 0;

        foreach ($wacRecords as $wac) {
            // Check if this user already has UPE access for this course
            // (via sale, installment, or any other path)
            $productTypes = ['course_video', 'course_live', 'webinar'];

            $hasUpeAccess = UpeSale::where('user_id', $wac->user_id)
                ->whereIn('status', ['active', 'partially_refunded'])
                ->whereHas('product', function ($q) use ($productTypes, $wac) {
                    $q->whereIn('product_type', $productTypes)
                        ->where('external_id', $wac->webinar_id);
                })
                ->where(function ($q) {
                    $q->whereNull('valid_until')
                        ->orWhere('valid_until', '>', now());
                })
                ->exists();

            if ($hasUpeAccess) {
                $matchedCount++;
                $this->totalUpeAccess++;
            } else {
                $missingCount++;
                $this->totalMissing++;
                $this->affectedUsers[] = [
                    'user_id' => $wac->user_id,
                    'email' => $wac->email,
                    'full_name' => $wac->full_name,
                    'mobile' => $wac->mobile,
                    'course_name' => $wac->course_name ?? "webinar_{$wac->webinar_id}",
                    'webinar_id' => $wac->webinar_id,
                    'bundle_id' => null,
                    'access_type' => "wac_percentage_{$wac->percentage}",
                    'amount_paid' => 0,
                    'purchase_date' => Carbon::parse($wac->created_at)->format('Y-m-d'),
                    'risk_category' => 'WAC_ONLY_ACCESS',
                    'risk_reason' => "Access via WebinarAccessControl only ({$wac->percentage}% access, expires " . Carbon::parse($wac->expire)->format('Y-m-d') . "). No sale or payment exists — this was a support/admin grant.",
                    'legacy_sale_id' => null,
                ];
            }
        }

        $this->info("  → WAC records: {$wacRecords->count()} active, {$matchedCount} covered by UPE, {$missingCount} need re-grant");
    }

    // ────────────────────────────────────────
    // CSV EXPORT
    // ────────────────────────────────────────
    private function exportCSV(string $path): void
    {
        $fullPath = base_path($path);
        $dir = dirname($fullPath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $fp = fopen($fullPath, 'w');

        // Header
        fputcsv($fp, [
            'user_id',
            'email',
            'full_name',
            'mobile',
            'course_name',
            'webinar_id',
            'bundle_id',
            'access_type',
            'amount_paid',
            'purchase_date',
            'risk_category',
            'risk_reason',
            'legacy_sale_id',
        ]);

        // Sort by risk category then user_id
        usort($this->affectedUsers, function ($a, $b) {
            $catCmp = strcmp($a['risk_category'], $b['risk_category']);
            return $catCmp !== 0 ? $catCmp : ($a['user_id'] <=> $b['user_id']);
        });

        foreach ($this->affectedUsers as $user) {
            fputcsv($fp, [
                $user['user_id'],
                $user['email'],
                $user['full_name'],
                $user['mobile'],
                $user['course_name'],
                $user['webinar_id'],
                $user['bundle_id'],
                $user['access_type'],
                $user['amount_paid'],
                $user['purchase_date'],
                $user['risk_category'],
                $user['risk_reason'],
                $user['legacy_sale_id'],
            ]);
        }

        fclose($fp);
    }
}
