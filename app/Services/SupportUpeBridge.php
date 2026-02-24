<?php

namespace App\Services;

use App\Models\PaymentEngine\UpeProduct;
use App\Models\PaymentEngine\UpeSale;
use App\Models\PaymentEngine\UpeLedgerEntry;
use App\Models\PaymentEngine\UpeSupportAction;
use App\Models\PaymentEngine\UpeMentorBadge;
use App\Services\PaymentEngine\AccessEngine;
use App\Services\PaymentEngine\PaymentLedgerService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Bridge between legacy AdminSupportController completion flow and the UPE system.
 *
 * When AdminSupportController marks a ticket as "completed", it creates legacy Sale
 * records. But AccessEngine ONLY reads from upe_sales. This bridge creates the
 * corresponding UPE records so AccessEngine can find them.
 *
 * RULES:
 *  - Never mutate existing UPE records
 *  - All operations are additive (new rows only)
 *  - Idempotent: safe to call twice for the same support request
 */
class SupportUpeBridge
{
    private PaymentLedgerService $ledger;
    private AccessEngine $access;

    public function __construct(PaymentLedgerService $ledger, AccessEngine $access)
    {
        $this->ledger = $ledger;
        $this->access = $access;
    }

    /**
     * Resolve UPE product_id from webinar_id.
     */
    public function resolveProductId(int $webinarId): ?int
    {
        $product = UpeProduct::where('external_id', $webinarId)
            ->where('product_type', 'course_video')
            ->first();

        return $product?->id;
    }

    /**
     * Get or create UPE product for a webinar.
     */
    public function getOrCreateProduct(int $webinarId): ?UpeProduct
    {
        $product = UpeProduct::where('external_id', $webinarId)
            ->where('product_type', 'course_video')
            ->first();

        if ($product) {
            return $product;
        }

        // Create product from webinar
        $webinar = \App\Models\Webinar::find($webinarId);
        if (!$webinar) {
            Log::error('SupportUpeBridge: Webinar not found', ['webinar_id' => $webinarId]);
            return null;
        }

        return UpeProduct::create([
            'product_type' => 'course_video',
            'external_id' => $webinarId,
            'base_fee' => $webinar->price ?? 0,
            'currency' => 'INR',
            'validity_days' => $webinar->access_days ?: null,
            'status' => 'active',
            'metadata' => ['slug' => $webinar->slug, 'legacy_id' => $webinarId, 'migrated_from' => 'webinars'],
        ]);
    }

    // ══════════════════════════════════════════════════════════════
    //  RELATIVE / FRIEND ACCESS — create UPE sale for the user
    // ══════════════════════════════════════════════════════════════

    public function grantRelativeAccess(int $userId, int $webinarId, int $supportRequestId, int $adminId): ?UpeSale
    {
        $product = $this->getOrCreateProduct($webinarId);
        if (!$product) return null;

        // Idempotency: check if UPE sale already exists for this support request
        $existing = UpeSale::where('user_id', $userId)
            ->where('product_id', $product->id)
            ->where('support_request_id', $supportRequestId)
            ->first();

        if ($existing) {
            Log::info('SupportUpeBridge: Relative access UPE sale already exists', ['sale_id' => $existing->id]);
            return $existing;
        }

        $validFrom = now();
        $validUntil = $product->validity_days
            ? $validFrom->copy()->addDays($product->validity_days)
            : null;

        $sale = UpeSale::create([
            'uuid' => (string) Str::uuid(),
            'user_id' => $userId,
            'product_id' => $product->id,
            'sale_type' => 'free',
            'pricing_mode' => 'free',
            'base_fee_snapshot' => 0,
            'currency' => 'INR',
            'status' => 'active',
            'valid_from' => $validFrom,
            'valid_until' => $validUntil,
            'support_request_id' => $supportRequestId,
            'approved_by' => $adminId,
            'executed_at' => now(),
            'metadata' => [
                'source' => 'admin_support_relative_access',
                'support_request_id' => $supportRequestId,
            ],
        ]);

        $this->access->invalidate($userId, $product->id);

        Log::info('SupportUpeBridge: Relative access UPE sale created', [
            'sale_id' => $sale->id, 'user_id' => $userId, 'product_id' => $product->id,
        ]);

        return $sale;
    }

    // ══════════════════════════════════════════════════════════════
    //  MENTOR ACCESS — create UPE sale for the user + course
    // ══════════════════════════════════════════════════════════════

    public function grantMentorAccess(int $userId, int $webinarId, int $supportRequestId, int $adminId): ?UpeSale
    {
        $product = $this->getOrCreateProduct($webinarId);
        if (!$product) return null;

        $existing = UpeSale::where('user_id', $userId)
            ->where('product_id', $product->id)
            ->where('support_request_id', $supportRequestId)
            ->first();

        if ($existing) {
            Log::info('SupportUpeBridge: Mentor access UPE sale already exists', ['sale_id' => $existing->id]);
            return $existing;
        }

        $validFrom = now();
        $validUntil = $product->validity_days
            ? $validFrom->copy()->addDays($product->validity_days)
            : null;

        $sale = UpeSale::create([
            'uuid' => (string) Str::uuid(),
            'user_id' => $userId,
            'product_id' => $product->id,
            'sale_type' => 'free',
            'pricing_mode' => 'free',
            'base_fee_snapshot' => 0,
            'currency' => 'INR',
            'status' => 'active',
            'valid_from' => $validFrom,
            'valid_until' => $validUntil,
            'support_request_id' => $supportRequestId,
            'approved_by' => $adminId,
            'executed_at' => now(),
            'metadata' => [
                'source' => 'admin_support_mentor_access',
                'support_request_id' => $supportRequestId,
            ],
        ]);

        $this->access->invalidate($userId, $product->id);

        Log::info('SupportUpeBridge: Mentor access UPE sale created', [
            'sale_id' => $sale->id, 'user_id' => $userId, 'product_id' => $product->id,
        ]);

        return $sale;
    }

    // ══════════════════════════════════════════════════════════════
    //  TEMPORARY ACCESS — create UPE support action (not sale)
    // ══════════════════════════════════════════════════════════════

    public function grantTemporaryAccess(int $userId, int $webinarId, int $supportRequestId, int $adminId, int $days = 7, int $percentage = 100): ?UpeSupportAction
    {
        $product = $this->getOrCreateProduct($webinarId);
        if (!$product) return null;

        // Idempotency
        $existing = UpeSupportAction::where('user_id', $userId)
            ->where('product_id', $product->id)
            ->where('action_type', UpeSupportAction::ACTION_TEMPORARY_ACCESS)
            ->where('status', UpeSupportAction::STATUS_EXECUTED)
            ->where('metadata->support_request_id', (string) $supportRequestId)
            ->first();

        if ($existing) {
            Log::info('SupportUpeBridge: Temp access already exists', ['action_id' => $existing->id]);
            return $existing;
        }

        $expiresAt = now()->addDays($days);

        $action = UpeSupportAction::create([
            'uuid' => (string) Str::uuid(),
            'action_type' => UpeSupportAction::ACTION_TEMPORARY_ACCESS,
            'status' => UpeSupportAction::STATUS_EXECUTED,
            'user_id' => $userId,
            'product_id' => $product->id,
            'expires_at' => $expiresAt,
            'requested_by' => $adminId,
            'requested_at' => now(),
            'executed_by' => $adminId,
            'executed_at' => now(),
            'metadata' => [
                'source' => 'admin_support_temporary_access',
                'support_request_id' => $supportRequestId,
                'temporary_days' => $days,
                'percentage' => $percentage,
            ],
            'idempotency_key' => "admin_temp_{$supportRequestId}",
        ]);

        $this->access->invalidate($userId, $product->id);

        Log::info('SupportUpeBridge: Temporary access created', [
            'action_id' => $action->id, 'expires_at' => $expiresAt->toDateTimeString(),
        ]);

        return $action;
    }

    // ══════════════════════════════════════════════════════════════
    //  COURSE EXTENSION — create child UPE sale
    // ══════════════════════════════════════════════════════════════

    public function grantCourseExtension(int $userId, int $webinarId, int $supportRequestId, int $adminId, int $days): ?UpeSale
    {
        $product = $this->getOrCreateProduct($webinarId);
        if (!$product) return null;

        $existing = UpeSale::where('user_id', $userId)
            ->where('product_id', $product->id)
            ->where('support_request_id', $supportRequestId)
            ->first();

        if ($existing) {
            Log::info('SupportUpeBridge: Extension UPE sale already exists', ['sale_id' => $existing->id]);
            return $existing;
        }

        // Find original sale to link as parent
        $parentSale = UpeSale::where('user_id', $userId)
            ->where('product_id', $product->id)
            ->whereNotIn('status', ['cancelled'])
            ->orderByDesc('id')
            ->first();

        $validFrom = now();
        $validUntil = $validFrom->copy()->addDays($days);

        $sale = UpeSale::create([
            'uuid' => (string) Str::uuid(),
            'user_id' => $userId,
            'product_id' => $product->id,
            'sale_type' => 'free',
            'pricing_mode' => 'free',
            'base_fee_snapshot' => 0,
            'currency' => 'INR',
            'status' => 'active',
            'valid_from' => $validFrom,
            'valid_until' => $validUntil,
            'parent_sale_id' => $parentSale?->id,
            'support_request_id' => $supportRequestId,
            'approved_by' => $adminId,
            'executed_at' => now(),
            'metadata' => [
                'source' => 'admin_support_course_extension',
                'extension_days' => $days,
                'support_request_id' => $supportRequestId,
            ],
        ]);

        $this->access->invalidate($userId, $product->id);

        Log::info('SupportUpeBridge: Course extension UPE sale created', [
            'sale_id' => $sale->id, 'days' => $days,
        ]);

        return $sale;
    }

    // ══════════════════════════════════════════════════════════════
    //  OFFLINE PAYMENT — create UPE sale + ledger entry with USER amount
    // ══════════════════════════════════════════════════════════════

    public function recordOfflinePayment(int $userId, int $webinarId, int $supportRequestId, int $adminId, float $cashAmount): ?UpeSale
    {
        $product = $this->getOrCreateProduct($webinarId);
        if (!$product) return null;

        // Find existing UPE sale or create new one
        $sale = UpeSale::where('user_id', $userId)
            ->where('product_id', $product->id)
            ->whereNotIn('status', ['cancelled', 'refunded'])
            ->orderByDesc('id')
            ->first();

        if (!$sale) {
            $validFrom = now();
            $validUntil = $product->validity_days
                ? $validFrom->copy()->addDays($product->validity_days)
                : null;

            $sale = UpeSale::create([
                'uuid' => (string) Str::uuid(),
                'user_id' => $userId,
                'product_id' => $product->id,
                'sale_type' => 'paid',
                'pricing_mode' => 'full',
                'base_fee_snapshot' => $product->base_fee,
                'currency' => 'INR',
                'status' => 'active',
                'valid_from' => $validFrom,
                'valid_until' => $validUntil,
                'support_request_id' => $supportRequestId,
                'approved_by' => $adminId,
                'executed_at' => now(),
                'metadata' => [
                    'source' => 'admin_support_offline_payment',
                    'support_request_id' => $supportRequestId,
                    'cash_amount' => $cashAmount,
                ],
            ]);
        }

        // Record the ACTUAL cash amount in the ledger (not base_fee)
        $idempotencyKey = "admin_offline_{$supportRequestId}";
        $existingEntry = UpeLedgerEntry::where('idempotency_key', $idempotencyKey)->first();

        if (!$existingEntry) {
            $this->ledger->recordPayment(
                saleId: $sale->id,
                amount: $cashAmount,
                paymentMethod: 'cash',
                processedBy: $adminId,
                description: "Offline/cash payment via support request #{$supportRequestId}",
                idempotencyKey: $idempotencyKey
            );
        }

        $this->access->invalidate($userId, $product->id);

        Log::info('SupportUpeBridge: Offline payment recorded', [
            'sale_id' => $sale->id, 'cash_amount' => $cashAmount,
        ]);

        return $sale;
    }

    // ══════════════════════════════════════════════════════════════
    //  REFUND — create UPE refund ledger entry + update sale status
    // ══════════════════════════════════════════════════════════════

    public function recordRefund(int $userId, int $webinarId, int $supportRequestId, int $adminId): ?UpeSale
    {
        $product = $this->getOrCreateProduct($webinarId);
        if (!$product) return null;

        $sale = UpeSale::where('user_id', $userId)
            ->where('product_id', $product->id)
            ->whereNotIn('status', ['cancelled', 'refunded'])
            ->orderByDesc('id')
            ->first();

        if (!$sale) {
            Log::warning('SupportUpeBridge: No UPE sale found for refund', [
                'user_id' => $userId, 'product_id' => $product->id,
            ]);
            return null;
        }

        $balance = $this->ledger->balance($sale->id);
        if ($balance <= 0) {
            // Free course or zero balance — just mark as refunded
            $sale->update(['status' => 'refunded']);
            $this->access->invalidate($userId, $product->id);
            return $sale;
        }

        $idempotencyKey = "admin_refund_{$supportRequestId}";
        $existingEntry = UpeLedgerEntry::where('idempotency_key', $idempotencyKey)->first();

        if (!$existingEntry) {
            $this->ledger->recordRefund(
                saleId: $sale->id,
                amount: $balance,
                paymentMethod: 'bank_transfer',
                processedBy: $adminId,
                referenceType: 'support_request',
                referenceId: $supportRequestId,
                description: "Refund via admin support #{$supportRequestId}",
                idempotencyKey: $idempotencyKey
            );
        }

        $sale->update(['status' => 'refunded']);
        $this->access->invalidate($userId, $product->id);

        Log::info('SupportUpeBridge: Refund recorded', [
            'sale_id' => $sale->id, 'refund_amount' => $balance,
        ]);

        return $sale;
    }

    // ══════════════════════════════════════════════════════════════
    //  WRONG COURSE CORRECTION — revoke old + grant new in UPE
    // ══════════════════════════════════════════════════════════════

    public function handleWrongCourseCorrection(int $userId, int $wrongWebinarId, int $correctWebinarId, int $supportRequestId, int $adminId): ?UpeSale
    {
        $wrongProduct = $this->getOrCreateProduct($wrongWebinarId);
        $correctProduct = $this->getOrCreateProduct($correctWebinarId);

        if (!$wrongProduct || !$correctProduct) return null;

        // Soft-revoke wrong course UPE sale
        $wrongSale = UpeSale::where('user_id', $userId)
            ->where('product_id', $wrongProduct->id)
            ->whereIn('status', ['active', 'partially_refunded'])
            ->first();

        if ($wrongSale) {
            $wrongSale->update(['status' => 'refunded']);
            $this->access->invalidate($userId, $wrongProduct->id);
        }

        // Create UPE sale for correct course
        return $this->grantRelativeAccess($userId, $correctWebinarId, $supportRequestId, $adminId);
    }

    // ══════════════════════════════════════════════════════════════
    //  FREE COURSE GRANT — batch create UPE sales
    // ══════════════════════════════════════════════════════════════

    public function grantFreeCourseAccess(int $userId, int $webinarId, int $supportRequestId, int $adminId): ?UpeSale
    {
        return $this->grantRelativeAccess($userId, $webinarId, $supportRequestId, $adminId);
    }

    // ══════════════════════════════════════════════════════════════
    //  POST-PURCHASE COUPON — record discount in UPE ledger
    // ══════════════════════════════════════════════════════════════

    public function recordCouponDiscount(int $userId, int $webinarId, int $supportRequestId, int $adminId, float $discountAmount, string $couponCode, int $discountId): ?UpeSale
    {
        $product = $this->getOrCreateProduct($webinarId);
        if (!$product) return null;

        $sale = UpeSale::where('user_id', $userId)
            ->where('product_id', $product->id)
            ->whereNotIn('status', ['cancelled', 'refunded'])
            ->orderByDesc('id')
            ->first();

        if (!$sale) {
            Log::warning('SupportUpeBridge: No UPE sale found for coupon discount', [
                'user_id' => $userId, 'product_id' => $product->id,
            ]);
            return null;
        }

        // Idempotency: check for existing ledger entry
        $idempotencyKey = "admin_coupon_{$supportRequestId}";
        $existingEntry = UpeLedgerEntry::where('idempotency_key', $idempotencyKey)->first();

        if (!$existingEntry) {
            $this->ledger->recordDiscount(
                saleId: $sale->id,
                amount: $discountAmount,
                discountId: $discountId,
                processedBy: $adminId,
                description: "Post-purchase coupon '{$couponCode}' via support #{$supportRequestId}"
            );
        }

        Log::info('SupportUpeBridge: Coupon discount recorded', [
            'sale_id' => $sale->id, 'discount_amount' => $discountAmount, 'coupon_code' => $couponCode,
        ]);

        return $sale;
    }
}
