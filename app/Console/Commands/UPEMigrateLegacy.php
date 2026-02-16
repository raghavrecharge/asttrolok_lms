<?php

namespace App\Console\Commands;

use App\Models\PaymentEngine\UpeProduct;
use App\Models\PaymentEngine\UpeSale;
use App\Models\PaymentEngine\UpeLedgerEntry;
use App\Models\PaymentEngine\UpeInstallmentPlan;
use App\Models\PaymentEngine\UpeInstallmentSchedule;
use App\Models\PaymentEngine\UpeSubscription;
use App\Models\PaymentEngine\UpeAuditLog;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class UPEMigrateLegacy extends Command
{
    protected $signature = 'upe:migrate-legacy
                            {--dry-run : Show what would be migrated without writing}
                            {--step=all : Run specific step: products|sales|installments|subscriptions|meetings|store|orphan-orders|all}';

    protected $description = 'Backfill legacy sales/orders/installments/subscriptions into UPE tables';

    private int $productsCreated = 0;
    private int $salesCreated = 0;
    private int $salesSkipped = 0;
    private int $ledgerEntriesCreated = 0;
    private int $installmentPlansCreated = 0;
    private int $installmentSchedulesCreated = 0;
    private int $subscriptionsCreated = 0;
    private int $meetingsCreated = 0;
    private int $productOrdersCreated = 0;
    private int $orphanOrdersCreated = 0;
    private bool $dryRun = false;

    public function handle(): int
    {
        $this->dryRun = $this->option('dry-run');
        $step = $this->option('step');

        if ($this->dryRun) {
            $this->warn('🔍 DRY RUN MODE — no data will be written');
        }

        $this->info('=== UPE Legacy Migration ===');
        $this->newLine();

        if (in_array($step, ['all', 'products'])) {
            $this->migrateProducts();
        }
        if (in_array($step, ['all', 'sales'])) {
            $this->migrateSales();
        }
        if (in_array($step, ['all', 'installments'])) {
            $this->migrateInstallments();
        }
        if (in_array($step, ['all', 'subscriptions'])) {
            $this->migrateSubscriptions();
        }
        if (in_array($step, ['all', 'meetings'])) {
            $this->migrateMeetings();
        }
        if (in_array($step, ['all', 'store'])) {
            $this->migrateProductOrders();
        }
        if (in_array($step, ['all', 'orphan-orders'])) {
            $this->migrateOrphanOrders();
        }

        $this->newLine();
        $this->info('=== Migration Summary ===');
        $this->table(
            ['Metric', 'Count'],
            [
                ['Products created', $this->productsCreated],
                ['Sales created', $this->salesCreated],
                ['Sales skipped (duplicate/invalid)', $this->salesSkipped],
                ['Ledger entries created', $this->ledgerEntriesCreated],
                ['Installment plans created', $this->installmentPlansCreated],
                ['Installment schedules created', $this->installmentSchedulesCreated],
                ['Subscriptions created', $this->subscriptionsCreated],
                ['Meetings created', $this->meetingsCreated],
                ['Product orders created', $this->productOrdersCreated],
                ['Orphan orders created', $this->orphanOrdersCreated],
            ]
        );

        return Command::SUCCESS;
    }

    // ────────────────────────────────────────
    // STEP 1: Products
    // ────────────────────────────────────────
    private function migrateProducts(): void
    {
        $this->info('Step 1: Migrating products...');

        // Webinars → upe_products
        $webinars = DB::table('webinars')
            ->whereIn('status', ['active', 'pending'])
            ->whereNull('deleted_at')
            ->get();

        foreach ($webinars as $webinar) {
            $exists = UpeProduct::where('product_type', $this->mapWebinarType($webinar->type))
                ->where('external_id', $webinar->id)
                ->exists();

            if ($exists) {
                continue;
            }

            if (!$this->dryRun) {
                UpeProduct::create([
                    'product_type' => $this->mapWebinarType($webinar->type),
                    'external_id' => $webinar->id,
                    'base_fee' => $webinar->price ?? 0,
                    'currency' => 'INR',
                    'validity_days' => $webinar->access_days,
                    'is_upgradeable' => false,
                    'adjustment_eligible' => true,
                    'adjustment_max_percent' => 80.00,
                    'status' => $webinar->status === 'active' ? 'active' : 'archived',
                    'metadata' => [
                        'migrated_from' => 'webinars',
                        'legacy_id' => $webinar->id,
                        'slug' => $webinar->slug,
                    ],
                ]);
            }
            $this->productsCreated++;
        }

        // Bundles → upe_products
        $bundles = DB::table('bundles')
            ->whereIn('status', ['active', 'pending'])
            ->get();

        foreach ($bundles as $bundle) {
            $exists = UpeProduct::where('product_type', 'bundle')
                ->where('external_id', $bundle->id)
                ->exists();

            if ($exists) {
                continue;
            }

            if (!$this->dryRun) {
                UpeProduct::create([
                    'product_type' => 'bundle',
                    'external_id' => $bundle->id,
                    'base_fee' => $bundle->price ?? 0,
                    'currency' => 'INR',
                    'validity_days' => $bundle->access_days ?? null,
                    'is_upgradeable' => false,
                    'adjustment_eligible' => true,
                    'adjustment_max_percent' => 80.00,
                    'status' => $bundle->status === 'active' ? 'active' : 'archived',
                    'metadata' => [
                        'migrated_from' => 'bundles',
                        'legacy_id' => $bundle->id,
                        'slug' => $bundle->slug ?? null,
                    ],
                ]);
            }
            $this->productsCreated++;
        }

        // Subscriptions → upe_products
        $subscriptions = DB::table('subscriptions')
            ->whereIn('status', ['active', 'pending'])
            ->get();

        foreach ($subscriptions as $sub) {
            $exists = UpeProduct::where('product_type', 'subscription')
                ->where('external_id', $sub->id)
                ->exists();

            if ($exists) {
                continue;
            }

            if (!$this->dryRun) {
                UpeProduct::create([
                    'product_type' => 'subscription',
                    'external_id' => $sub->id,
                    'base_fee' => $sub->price ?? 0,
                    'currency' => 'INR',
                    'validity_days' => $sub->access_days ?? null,
                    'is_upgradeable' => false,
                    'adjustment_eligible' => false,
                    'adjustment_max_percent' => 0,
                    'status' => $sub->status === 'active' ? 'active' : 'archived',
                    'metadata' => [
                        'migrated_from' => 'subscriptions',
                        'legacy_id' => $sub->id,
                        'slug' => $sub->slug,
                        'billing_cycle' => $sub->billing_cycle,
                    ],
                ]);
            }
            $this->productsCreated++;
        }

        $this->info("  → Products: {$this->productsCreated} created");
    }

    // ────────────────────────────────────────
    // STEP 2: Sales
    // ────────────────────────────────────────
    private function migrateSales(): void
    {
        $this->info('Step 2: Migrating sales...');

        // For each (buyer_id, webinar_id) pair, find the latest active sale
        // For bundle sales, use bundle_id instead
        $sales = DB::table('sales')
            ->join('users', 'users.id', '=', 'sales.buyer_id')
            ->where('users.email', 'NOT LIKE', '%@rechargestudio.com')
            ->select('sales.*')
            ->orderBy('sales.created_at', 'desc')
            ->get();

        // Group: pick latest per (buyer_id, webinar_id) and (buyer_id, bundle_id)
        $processed = [];

        foreach ($sales as $sale) {
            $productType = null;
            $externalId = null;

            if (!empty($sale->webinar_id)) {
                $productType = 'webinar';
                $externalId = $sale->webinar_id;
            } elseif (!empty($sale->bundle_id)) {
                $productType = 'bundle';
                $externalId = $sale->bundle_id;
            } elseif (!empty($sale->subscription_id)) {
                $productType = 'subscription';
                $externalId = $sale->subscription_id;
            } elseif (!empty($sale->subscribe_id)) {
                // Legacy subscribe type — skip, handled by subscription migration
                continue;
            } elseif (!empty($sale->meeting_id) || !empty($sale->meeting_time_id)) {
                // Meetings — skip for now (not course access)
                continue;
            } elseif (!empty($sale->promotion_id) || !empty($sale->registration_package_id)) {
                // Internal records — skip
                continue;
            } elseif (!empty($sale->installment_payment_id)) {
                // Installment payments handled in Step 3
                continue;
            } else {
                $this->salesSkipped++;
                continue;
            }

            // Idempotency: check if already migrated
            $idempotencyKey = "legacy_sale_{$sale->id}";
            $alreadyExists = !$this->dryRun && UpeSale::where('metadata->legacy_sale_id', $sale->id)->exists();
            if ($alreadyExists) {
                $this->salesSkipped++;
                continue;
            }

            // Find matching UPE product
            $upeProduct = $this->findUpeProduct($productType, $externalId);
            if (!$upeProduct && !$this->dryRun) {
                // Product not migrated (deleted webinar, etc.)
                $this->salesSkipped++;
                continue;
            }

            // Determine UPE status
            $upeStatus = $this->mapSaleStatus($sale);

            // Determine sale_type
            $saleType = ($sale->manual_added == 1 || ($sale->total_amount ?? $sale->amount) == 0) ? 'free' : 'paid';

            // Determine pricing_mode
            $pricingMode = 'full';
            if ($sale->type === 'installment_payment') {
                $pricingMode = 'installment';
            } elseif ($sale->type === 'subscribe' || $sale->type === 'subscription') {
                $pricingMode = 'subscription';
            } elseif ($saleType === 'free') {
                $pricingMode = 'free';
            }

            // Calculate valid_from and valid_until
            $validFrom = $this->unixToCarbon($sale->created_at);
            $validUntil = null;
            if ($upeProduct && $upeProduct->validity_days) {
                $validUntil = $validFrom->copy()->addDays($upeProduct->validity_days);
            }

            $amount = $sale->total_amount ?? $sale->amount ?? 0;

            if (!$this->dryRun) {
                DB::transaction(function () use (
                    $sale, $upeProduct, $upeStatus, $saleType, $pricingMode,
                    $validFrom, $validUntil, $amount, $idempotencyKey
                ) {
                    $upeSale = UpeSale::create([
                        'uuid' => (string) Str::uuid(),
                        'user_id' => $sale->buyer_id,
                        'product_id' => $upeProduct->id,
                        'sale_type' => $saleType,
                        'pricing_mode' => $pricingMode,
                        'base_fee_snapshot' => $upeProduct->base_fee,
                        'currency' => 'INR',
                        'status' => $upeStatus,
                        'valid_from' => $validFrom,
                        'valid_until' => $validUntil,
                        'executed_at' => $validFrom,
                        'metadata' => [
                            'migrated_from' => 'legacy',
                            'legacy_sale_id' => $sale->id,
                            'legacy_order_id' => $sale->order_id,
                            'legacy_type' => $sale->type,
                            'legacy_payment_method' => $sale->payment_method,
                            'legacy_manual_added' => $sale->manual_added,
                        ],
                    ]);

                    // Create ledger entry for the payment
                    // Skip for installment sales — Step 3 will create properly linked
                    // installment_payment entries with reference to schedules
                    if ($amount > 0 && $pricingMode !== 'installment' && in_array($upeStatus, ['active', 'completed', 'expired', 'partially_refunded'])) {
                        $paymentMethod = $this->mapPaymentMethod($sale->payment_method);

                        UpeLedgerEntry::create([
                            'uuid' => (string) Str::uuid(),
                            'sale_id' => $upeSale->id,
                            'entry_type' => UpeLedgerEntry::TYPE_PAYMENT,
                            'direction' => UpeLedgerEntry::DIR_CREDIT,
                            'amount' => $amount,
                            'currency' => 'INR',
                            'payment_method' => $paymentMethod,
                            'description' => "Migrated from legacy sale #{$sale->id}",
                            'idempotency_key' => $idempotencyKey,
                        ]);
                        $this->ledgerEntriesCreated++;
                    }

                    // If refunded, also create refund ledger entry
                    if ($sale->refund_at && $amount > 0) {
                        UpeLedgerEntry::create([
                            'uuid' => (string) Str::uuid(),
                            'sale_id' => $upeSale->id,
                            'entry_type' => UpeLedgerEntry::TYPE_REFUND,
                            'direction' => UpeLedgerEntry::DIR_DEBIT,
                            'amount' => $amount,
                            'currency' => 'INR',
                            'payment_method' => 'system',
                            'description' => "Refund migrated from legacy sale #{$sale->id}",
                            'idempotency_key' => "legacy_refund_{$sale->id}",
                        ]);
                        $this->ledgerEntriesCreated++;
                    }

                    // If discount was applied
                    if (!empty($sale->discount) && $sale->discount > 0) {
                        UpeLedgerEntry::create([
                            'uuid' => (string) Str::uuid(),
                            'sale_id' => $upeSale->id,
                            'entry_type' => UpeLedgerEntry::TYPE_DISCOUNT,
                            'direction' => UpeLedgerEntry::DIR_CREDIT,
                            'amount' => $sale->discount,
                            'currency' => 'INR',
                            'payment_method' => 'system',
                            'description' => "Discount migrated from legacy sale #{$sale->id}",
                            'idempotency_key' => "legacy_discount_{$sale->id}",
                        ]);
                        $this->ledgerEntriesCreated++;
                    }
                });
            }

            $this->salesCreated++;
        }

        $this->info("  → Sales: {$this->salesCreated} created, {$this->salesSkipped} skipped");
    }

    // ────────────────────────────────────────
    // STEP 3: Installments
    // ────────────────────────────────────────
    private function migrateInstallments(): void
    {
        $this->info('Step 3: Migrating installment orders...');

        $installmentOrders = DB::table('installment_orders')
            ->join('users', 'users.id', '=', 'installment_orders.user_id')
            ->where('users.email', 'NOT LIKE', '%@rechargestudio.com')
            ->whereIn('installment_orders.status', ['open', 'paying', 'part'])
            ->select('installment_orders.*')
            ->get();

        foreach ($installmentOrders as $io) {
            // Check idempotency
            if (!$this->dryRun) {
                $alreadyExists = UpeInstallmentPlan::whereHas('sale', function ($q) use ($io) {
                    $q->where('metadata->legacy_installment_order_id', $io->id);
                })->exists();
                if ($alreadyExists) {
                    continue;
                }
            }

            // Find or create the parent UPE sale for this installment order
            $externalId = $io->webinar_id ?? $io->bundle_id;
            $productType = $io->webinar_id ? 'webinar' : ($io->bundle_id ? 'bundle' : null);

            if (!$externalId || !$productType) {
                continue;
            }

            $upeProduct = $this->findUpeProduct($productType, $externalId);
            if (!$upeProduct && !$this->dryRun) {
                continue;
            }

            // Get all payments for this installment order
            $payments = DB::table('installment_order_payments')
                ->where('installment_order_id', $io->id)
                ->orderBy('created_at')
                ->get();

            $totalPaid = $payments->where('status', 'paid')->sum('amount');

            // Find if a UPE sale already exists for this user+product (from Step 2)
            $existingSale = null;
            if (!$this->dryRun && $upeProduct) {
                $existingSale = UpeSale::where('user_id', $io->user_id)
                    ->where('product_id', $upeProduct->id)
                    ->where('pricing_mode', 'installment')
                    ->first();
            }

            if (!$this->dryRun) {
                DB::transaction(function () use ($io, $upeProduct, $payments, $totalPaid, $existingSale) {
                    // Create UPE sale if doesn't exist
                    $upeSale = $existingSale;
                    if (!$upeSale) {
                        $validFrom = $this->unixToCarbon($io->created_at);
                        $validUntil = $upeProduct->validity_days
                            ? $validFrom->copy()->addDays($upeProduct->validity_days)
                            : null;

                        // Map status: open/part → active, paying → pending_payment
                        $status = in_array($io->status, ['open', 'part']) ? 'active' : 'pending_payment';

                        $upeSale = UpeSale::create([
                            'uuid' => (string) Str::uuid(),
                            'user_id' => $io->user_id,
                            'product_id' => $upeProduct->id,
                            'sale_type' => 'paid',
                            'pricing_mode' => 'installment',
                            'base_fee_snapshot' => $upeProduct->base_fee,
                            'currency' => 'INR',
                            'status' => $status,
                            'valid_from' => $validFrom,
                            'valid_until' => $validUntil,
                            'executed_at' => $validFrom,
                            'metadata' => [
                                'migrated_from' => 'legacy',
                                'legacy_installment_order_id' => $io->id,
                                'legacy_status' => $io->status,
                                'legacy_item_price' => $io->item_price,
                                'legacy_discount' => $io->discount,
                            ],
                        ]);
                        $this->salesCreated++;
                    }

                    // Create installment plan
                    $plan = UpeInstallmentPlan::create([
                        'sale_id' => $upeSale->id,
                        'total_amount' => $io->item_price,
                        'num_installments' => $payments->count(),
                        'plan_type' => 'standard',
                        'status' => in_array($io->status, ['open', 'part']) ? 'active' : 'completed',
                    ]);
                    $this->installmentPlansCreated++;

                    // Create schedules + ledger entries for each payment
                    $sequence = 0;
                    foreach ($payments as $payment) {
                        $sequence++;
                        $isPaid = $payment->status === 'paid';
                        $paidAt = $isPaid ? $this->unixToCarbon($payment->created_at) : null;

                        $scheduleStatus = match ($payment->status) {
                            'paid' => 'paid',
                            'paying' => 'due',
                            'part' => 'partial',
                            'canceled' => 'upcoming',
                            default => 'upcoming',
                        };

                        $schedule = UpeInstallmentSchedule::create([
                            'plan_id' => $plan->id,
                            'sequence' => $sequence,
                            'amount_due' => $payment->amount,
                            'amount_paid' => $isPaid ? $payment->amount : 0,
                            'due_date' => $this->unixToCarbon($payment->created_at)->toDateString(),
                            'status' => $scheduleStatus,
                            'paid_at' => $paidAt,
                        ]);
                        $this->installmentSchedulesCreated++;

                        // Ledger entry for paid installments
                        if ($isPaid && $payment->amount > 0) {
                            $ledger = UpeLedgerEntry::create([
                                'uuid' => (string) Str::uuid(),
                                'sale_id' => $upeSale->id,
                                'entry_type' => UpeLedgerEntry::TYPE_INSTALLMENT_PAYMENT,
                                'direction' => UpeLedgerEntry::DIR_CREDIT,
                                'amount' => $payment->amount,
                                'currency' => 'INR',
                                'payment_method' => 'razorpay',
                                'reference_type' => 'installment_schedule',
                                'reference_id' => $schedule->id,
                                'description' => "Installment #{$sequence} migrated from legacy payment #{$payment->id}",
                                'idempotency_key' => "legacy_installment_payment_{$payment->id}",
                            ]);

                            // Link ledger entry to schedule
                            DB::table('upe_installment_schedules')
                                ->where('id', $schedule->id)
                                ->update(['ledger_entry_id' => $ledger->id]);

                            $this->ledgerEntriesCreated++;
                        }
                    }
                });
            } else {
                $this->installmentPlansCreated++;
                $this->installmentSchedulesCreated += $payments->count();
            }
        }

        $this->info("  → Installment plans: {$this->installmentPlansCreated}, schedules: {$this->installmentSchedulesCreated}");
    }

    // ────────────────────────────────────────
    // STEP 4: Subscriptions
    // ────────────────────────────────────────
    private function migrateSubscriptions(): void
    {
        $this->info('Step 4: Migrating subscriptions...');

        $subAccesses = DB::table('subscription_access')
            ->join('users', 'users.id', '=', 'subscription_access.user_id')
            ->where('users.email', 'NOT LIKE', '%@rechargestudio.com')
            ->select('subscription_access.*')
            ->get();

        foreach ($subAccesses as $sa) {
            // Check idempotency
            if (!$this->dryRun) {
                $exists = UpeSubscription::where('user_id', $sa->user_id)
                    ->whereHas('product', function ($q) use ($sa) {
                        $q->where('product_type', 'subscription')
                            ->where('external_id', $sa->subscription_id);
                    })->exists();
                if ($exists) {
                    continue;
                }
            }

            $upeProduct = $this->findUpeProduct('subscription', $sa->subscription_id);
            if (!$upeProduct && !$this->dryRun) {
                continue;
            }

            $now = time();
            $accessTillDate = (int) $sa->access_till_date;
            $isActive = $accessTillDate > $now;
            $createdAtUnix = is_numeric($sa->created_at) ? (int) $sa->created_at : strtotime($sa->created_at);

            if (!$this->dryRun) {
                DB::transaction(function () use ($sa, $upeProduct, $isActive, $createdAtUnix, $accessTillDate) {
                    $validFrom = Carbon::createFromTimestamp($createdAtUnix);
                    $validUntil = Carbon::createFromTimestamp($accessTillDate);

                    // Create UPE sale
                    $upeSale = UpeSale::create([
                        'uuid' => (string) Str::uuid(),
                        'user_id' => $sa->user_id,
                        'product_id' => $upeProduct->id,
                        'sale_type' => 'paid',
                        'pricing_mode' => 'subscription',
                        'base_fee_snapshot' => $upeProduct->base_fee,
                        'currency' => 'INR',
                        'status' => $isActive ? 'active' : 'expired',
                        'valid_from' => $validFrom,
                        'valid_until' => $validUntil,
                        'executed_at' => $validFrom,
                        'metadata' => [
                            'migrated_from' => 'legacy',
                            'legacy_subscription_access_id' => $sa->id,
                            'legacy_subscription_id' => $sa->subscription_id,
                            'paid_no_of_subscriptions' => $sa->paid_no_of_subscriptions,
                            'access_content_count' => $sa->access_content_count,
                        ],
                    ]);
                    $this->salesCreated++;

                    // Create UPE subscription
                    $subscription = DB::table('subscriptions')
                        ->where('id', $sa->subscription_id)
                        ->first();

                    UpeSubscription::create([
                        'sale_id' => $upeSale->id,
                        'user_id' => $sa->user_id,
                        'product_id' => $upeProduct->id,
                        'billing_amount' => $upeProduct->base_fee,
                        'billing_interval' => $subscription->billing_cycle ?? 'monthly',
                        'current_period_start' => $validFrom,
                        'current_period_end' => $validUntil,
                        'grace_period_days' => 3,
                        'status' => $isActive ? 'active' : 'expired',
                        'gateway_subscription_id' => $subscription->razorpay_plan_id ?? null,
                    ]);
                    $this->subscriptionsCreated++;

                    // Create ledger entries for subscription payments
                    $subPayments = DB::table('subscription_payments')
                        ->where('user_id', $sa->user_id)
                        ->where('subscription_id', $sa->subscription_id)
                        ->get();

                    foreach ($subPayments as $sp) {
                        // Some legacy rows store count (1) instead of INR amount
                        // Use product price if amount looks like a count
                        $ledgerAmount = ($sp->amount && $sp->amount > 10)
                            ? $sp->amount
                            : $upeProduct->base_fee;

                        UpeLedgerEntry::create([
                            'uuid' => (string) Str::uuid(),
                            'sale_id' => $upeSale->id,
                            'entry_type' => UpeLedgerEntry::TYPE_SUBSCRIPTION_CHARGE,
                            'direction' => UpeLedgerEntry::DIR_CREDIT,
                            'amount' => $ledgerAmount,
                            'currency' => 'INR',
                            'payment_method' => $sp->via_payment ? 'razorpay' : 'system',
                            'description' => "Subscription payment migrated from legacy #{$sp->id}" .
                                ($sp->admin ? ' (admin-granted)' : ''),
                            'idempotency_key' => "legacy_sub_payment_{$sp->id}",
                        ]);
                        $this->ledgerEntriesCreated++;
                    }
                });
            } else {
                $this->subscriptionsCreated++;
            }
        }

        $this->info("  → Subscriptions: {$this->subscriptionsCreated}");
    }

    // ────────────────────────────────────────
    // STEP 5: Meetings / Consultations
    // ────────────────────────────────────────
    private function migrateMeetings(): void
    {
        $this->info('Step 5: Migrating meetings/consultations...');

        // Create meeting products from webinars that are meetings
        $meetings = DB::table('webinars')
            ->where('type', 'webinar')
            ->whereNull('deleted_at')
            ->get();

        // Also handle meetings table if it exists
        $meetingRows = DB::table('meetings')->get();
        foreach ($meetingRows as $meeting) {
            $exists = UpeProduct::where('product_type', 'meeting')
                ->where('external_id', $meeting->id)
                ->exists();
            if ($exists) continue;

            if (!$this->dryRun) {
                $creator = DB::table('users')->where('id', $meeting->creator_id)->first();
                UpeProduct::create([
                    'product_type' => 'meeting',
                    'external_id' => $meeting->id,
                    'base_fee' => $meeting->amount ?? 0,
                    'currency' => 'INR',
                    'validity_days' => null,
                    'is_upgradeable' => false,
                    'adjustment_eligible' => false,
                    'adjustment_max_percent' => 0,
                    'status' => !empty($meeting->disabled) ? 'archived' : 'active',
                    'metadata' => [
                        'migrated_from' => 'meetings',
                        'legacy_id' => $meeting->id,
                        'creator_id' => $meeting->creator_id,
                        'creator_name' => $creator->full_name ?? null,
                    ],
                ]);
                $this->productsCreated++;
            }
        }

        // Migrate reserve_meetings → upe_sales
        $reservations = DB::table('reserve_meetings')
            ->join('users', 'users.id', '=', 'reserve_meetings.user_id')
            ->where('users.email', 'NOT LIKE', '%@rechargestudio.com')
            ->where('reserve_meetings.paid_amount', '>', 0)
            ->select('reserve_meetings.*')
            ->get();

        foreach ($reservations as $rm) {
            // Idempotency
            if (!$this->dryRun) {
                $alreadyExists = UpeSale::where('metadata->legacy_reserve_meeting_id', $rm->id)->exists();
                if ($alreadyExists) continue;
            }

            $upeProduct = !$this->dryRun
                ? UpeProduct::where('product_type', 'meeting')->where('external_id', $rm->meeting_id)->first()
                : null;

            if (!$upeProduct && !$this->dryRun) {
                // Try to find by sale's meeting_id
                continue;
            }

            if (!$this->dryRun) {
                DB::transaction(function () use ($rm, $upeProduct) {
                    $validFrom = Carbon::createFromTimestamp($rm->created_at);

                    $upeSale = UpeSale::create([
                        'uuid' => (string) Str::uuid(),
                        'user_id' => $rm->user_id,
                        'product_id' => $upeProduct->id,
                        'sale_type' => 'paid',
                        'pricing_mode' => 'full',
                        'base_fee_snapshot' => $rm->paid_amount,
                        'currency' => 'INR',
                        'status' => 'completed',
                        'valid_from' => $validFrom,
                        'valid_until' => null,
                        'executed_at' => $validFrom,
                        'metadata' => [
                            'migrated_from' => 'legacy',
                            'legacy_reserve_meeting_id' => $rm->id,
                            'legacy_meeting_id' => $rm->meeting_id,
                            'legacy_sale_id' => $rm->sale_id,
                            'meeting_type' => $rm->meeting_type,
                            'meeting_date' => $rm->day,
                        ],
                    ]);

                    if ($rm->paid_amount > 0) {
                        UpeLedgerEntry::create([
                            'uuid' => (string) Str::uuid(),
                            'sale_id' => $upeSale->id,
                            'entry_type' => UpeLedgerEntry::TYPE_PAYMENT,
                            'direction' => UpeLedgerEntry::DIR_CREDIT,
                            'amount' => $rm->paid_amount,
                            'currency' => 'INR',
                            'payment_method' => 'razorpay',
                            'description' => "Meeting payment migrated from reserve_meeting #{$rm->id}",
                            'idempotency_key' => "legacy_meeting_{$rm->id}",
                        ]);
                        $this->ledgerEntriesCreated++;
                    }
                });
            }

            $this->meetingsCreated++;
        }

        $this->info("  → Meetings: {$this->meetingsCreated} created");
    }

    // ────────────────────────────────────────
    // STEP 6: Product Orders (Store)
    // ────────────────────────────────────────
    private function migrateProductOrders(): void
    {
        $this->info('Step 6: Migrating product orders...');

        // Create UPE products for store products
        $products = DB::table('products')
            ->whereIn('status', ['active', 'pending'])
            ->get();

        foreach ($products as $product) {
            $exists = UpeProduct::where('product_type', 'physical_product')
                ->where('external_id', $product->id)
                ->exists();
            if ($exists) continue;

            if (!$this->dryRun) {
                UpeProduct::create([
                    'product_type' => 'physical_product',
                    'external_id' => $product->id,
                    'base_fee' => $product->price ?? 0,
                    'currency' => 'INR',
                    'validity_days' => null,
                    'is_upgradeable' => false,
                    'adjustment_eligible' => false,
                    'adjustment_max_percent' => 0,
                    'status' => $product->status === 'active' ? 'active' : 'archived',
                    'metadata' => [
                        'migrated_from' => 'products',
                        'legacy_id' => $product->id,
                        'slug' => $product->slug ?? null,
                    ],
                ]);
                $this->productsCreated++;
            }
        }

        // Migrate product_orders → upe_sales
        $productOrders = DB::table('product_orders')
            ->join('users', 'users.id', '=', 'product_orders.buyer_id')
            ->where('users.email', 'NOT LIKE', '%@rechargestudio.com')
            ->whereIn('product_orders.status', ['waiting_delivery', 'shipped', 'success'])
            ->select('product_orders.*')
            ->get();

        foreach ($productOrders as $po) {
            if (!$this->dryRun) {
                $alreadyExists = UpeSale::where('metadata->legacy_product_order_id', $po->id)->exists();
                if ($alreadyExists) continue;
            }

            $upeProduct = !$this->dryRun
                ? UpeProduct::where('product_type', 'physical_product')->where('external_id', $po->product_id)->first()
                : null;

            if (!$upeProduct && !$this->dryRun) continue;

            // Get amount from linked sale
            $amount = 0;
            if ($po->sale_id) {
                $legacySale = DB::table('sales')->where('id', $po->sale_id)->first();
                $amount = $legacySale ? ($legacySale->total_amount ?? $legacySale->amount ?? 0) : 0;
            }

            if (!$this->dryRun) {
                DB::transaction(function () use ($po, $upeProduct, $amount) {
                    $validFrom = Carbon::createFromTimestamp($po->created_at);

                    $statusMap = [
                        'waiting_delivery' => 'active',
                        'shipped' => 'active',
                        'success' => 'completed',
                    ];

                    $upeSale = UpeSale::create([
                        'uuid' => (string) Str::uuid(),
                        'user_id' => $po->buyer_id,
                        'product_id' => $upeProduct->id,
                        'sale_type' => $amount > 0 ? 'paid' : 'free',
                        'pricing_mode' => 'full',
                        'base_fee_snapshot' => $upeProduct->base_fee,
                        'currency' => 'INR',
                        'status' => $statusMap[$po->status] ?? 'active',
                        'valid_from' => $validFrom,
                        'valid_until' => null,
                        'executed_at' => $validFrom,
                        'metadata' => [
                            'migrated_from' => 'legacy',
                            'legacy_product_order_id' => $po->id,
                            'legacy_sale_id' => $po->sale_id,
                            'legacy_status' => $po->status,
                            'quantity' => $po->quantity,
                        ],
                    ]);

                    if ($amount > 0) {
                        UpeLedgerEntry::create([
                            'uuid' => (string) Str::uuid(),
                            'sale_id' => $upeSale->id,
                            'entry_type' => UpeLedgerEntry::TYPE_PAYMENT,
                            'direction' => UpeLedgerEntry::DIR_CREDIT,
                            'amount' => $amount,
                            'currency' => 'INR',
                            'payment_method' => 'razorpay',
                            'description' => "Product order payment migrated from product_order #{$po->id}",
                            'idempotency_key' => "legacy_product_order_{$po->id}",
                        ]);
                        $this->ledgerEntriesCreated++;
                    }
                });
            }

            $this->productOrdersCreated++;
        }

        $this->info("  → Product orders: {$this->productOrdersCreated} created");
    }

    // ────────────────────────────────────────
    // STEP 7: Orphan Paid Orders
    // ────────────────────────────────────────
    private function migrateOrphanOrders(): void
    {
        $this->info('Step 7: Migrating orphan paid orders...');

        // Find paid orders that have NO corresponding sales record
        $paidOrderIds = DB::table('orders')->where('status', 'paid')->pluck('id');
        $ordersWithSales = DB::table('sales')
            ->whereIn('order_id', $paidOrderIds)
            ->distinct('order_id')
            ->pluck('order_id');
        $orphanOrderIds = $paidOrderIds->diff($ordersWithSales);

        // Get order items with webinar_id or bundle_id from orphan orders
        $items = DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->join('users', 'users.id', '=', 'orders.user_id')
            ->where('users.email', 'NOT LIKE', '%@rechargestudio.com')
            ->whereIn('order_items.order_id', $orphanOrderIds)
            ->where(function ($q) {
                $q->where(function ($q2) {
                    $q2->whereNotNull('order_items.webinar_id')
                        ->where('order_items.webinar_id', '!=', 0);
                })->orWhere(function ($q2) {
                    $q2->whereNotNull('order_items.bundle_id')
                        ->where('order_items.bundle_id', '!=', 0);
                });
            })
            ->select('order_items.*', 'orders.user_id as order_user_id', 'orders.payment_method as order_payment_method')
            ->get();

        foreach ($items as $item) {
            $userId = $item->order_user_id ?? $item->user_id;
            $webinarId = $item->webinar_id ?? null;
            $bundleId = $item->bundle_id ?? null;

            if (!$userId) continue;

            // Determine product type
            $productType = $webinarId ? 'webinar' : ($bundleId ? 'bundle' : null);
            $externalId = $webinarId ?: $bundleId;
            if (!$productType || !$externalId) continue;

            // Check if user already has access via existing UPE sale
            if (!$this->dryRun) {
                $upeProduct = $this->findUpeProduct($productType, $externalId);
                if (!$upeProduct) continue;

                $alreadyHasAccess = UpeSale::where('user_id', $userId)
                    ->where('product_id', $upeProduct->id)
                    ->where('status', 'active')
                    ->exists();
                if ($alreadyHasAccess) continue;

                // Also check idempotency
                $alreadyMigrated = UpeSale::where('metadata->legacy_orphan_order_item_id', $item->id)->exists();
                if ($alreadyMigrated) continue;

                DB::transaction(function () use ($item, $upeProduct, $userId) {
                    $validFrom = Carbon::createFromTimestamp($item->created_at);
                    $validUntil = $upeProduct->validity_days
                        ? $validFrom->copy()->addDays($upeProduct->validity_days)
                        : null;

                    $amount = $item->total_amount ?? $item->amount ?? 0;

                    $upeSale = UpeSale::create([
                        'uuid' => (string) Str::uuid(),
                        'user_id' => $userId,
                        'product_id' => $upeProduct->id,
                        'sale_type' => $amount > 0 ? 'paid' : 'free',
                        'pricing_mode' => 'full',
                        'base_fee_snapshot' => $upeProduct->base_fee,
                        'currency' => 'INR',
                        'status' => 'active',
                        'valid_from' => $validFrom,
                        'valid_until' => $validUntil,
                        'executed_at' => $validFrom,
                        'metadata' => [
                            'migrated_from' => 'legacy',
                            'legacy_orphan_order_item_id' => $item->id,
                            'legacy_order_id' => $item->order_id,
                        ],
                    ]);

                    if ($amount > 0) {
                        UpeLedgerEntry::create([
                            'uuid' => (string) Str::uuid(),
                            'sale_id' => $upeSale->id,
                            'entry_type' => UpeLedgerEntry::TYPE_PAYMENT,
                            'direction' => UpeLedgerEntry::DIR_CREDIT,
                            'amount' => $amount,
                            'currency' => 'INR',
                            'payment_method' => $this->mapPaymentMethod($item->order_payment_method ?? null),
                            'description' => "Payment from orphan order item #{$item->id} (order #{$item->order_id})",
                            'idempotency_key' => "legacy_orphan_order_{$item->id}",
                        ]);
                        $this->ledgerEntriesCreated++;
                    }
                });
            }

            $this->orphanOrdersCreated++;
        }

        $this->info("  → Orphan orders: {$this->orphanOrdersCreated} created");
    }

    // ────────────────────────────────────────
    // HELPER METHODS
    // ────────────────────────────────────────

    private function mapWebinarType(string $type): string
    {
        return match ($type) {
            'webinar' => 'webinar',
            'course' => 'course_video',
            'text_lesson' => 'course_video',
            default => 'course_video',
        };
    }

    private function findUpeProduct(?string $legacyType, int $externalId): ?UpeProduct
    {
        if (!$legacyType) {
            return null;
        }

        $productTypes = match ($legacyType) {
            'webinar' => ['course_video', 'course_live', 'webinar'],
            'bundle' => ['bundle'],
            'subscription' => ['subscription'],
            'meeting' => ['meeting'],
            'physical_product' => ['physical_product'],
            default => ['course_video'],
        };

        return UpeProduct::whereIn('product_type', $productTypes)
            ->where('external_id', $externalId)
            ->first();
    }

    private function mapSaleStatus(object $sale): string
    {
        // Refunded
        if (!empty($sale->refund_at)) {
            return 'refunded';
        }

        // Access revoked
        if ($sale->access_to_purchased_item == 0) {
            return 'cancelled';
        }

        // Active
        return 'active';
    }

    private function mapPaymentMethod(?string $method): string
    {
        return match ($method) {
            'payment_channel' => 'razorpay',
            'credit' => 'wallet',
            'subscribe' => 'system',
            default => 'razorpay',
        };
    }

    private function unixToCarbon(int $timestamp): Carbon
    {
        return Carbon::createFromTimestamp($timestamp);
    }
}
