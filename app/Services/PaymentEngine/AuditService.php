<?php

namespace App\Services\PaymentEngine;

use App\Models\PaymentEngine\UpeAuditLog;

class AuditService
{
    public function log(
        int $actorId,
        string $actorRole,
        string $action,
        string $entityType,
        int $entityId,
        ?array $oldState = null,
        ?array $newState = null
    ): UpeAuditLog {
        return UpeAuditLog::record(
            $actorId,
            $actorRole,
            $action,
            $entityType,
            $entityId,
            $oldState,
            $newState,
            request()?->ip(),
            request()?->userAgent()
        );
    }

    public function logSaleCreated(int $actorId, string $role, array $saleData): UpeAuditLog
    {
        return $this->log($actorId, $role, 'sale.created', 'sale', $saleData['id'] ?? 0, null, $saleData);
    }

    public function logSaleStatusChange(int $actorId, string $role, int $saleId, string $old, string $new): UpeAuditLog
    {
        return $this->log($actorId, $role, 'sale.status_changed', 'sale', $saleId, ['status' => $old], ['status' => $new]);
    }

    public function logLedgerEntry(int $actorId, string $role, array $entryData): UpeAuditLog
    {
        return $this->log($actorId, $role, 'ledger.' . ($entryData['entry_type'] ?? 'unknown'), 'ledger_entry', $entryData['id'] ?? 0, null, $entryData);
    }

    public function logRequestTransition(int $actorId, string $role, int $requestId, string $old, string $new): UpeAuditLog
    {
        return $this->log($actorId, $role, 'request.' . $new, 'payment_request', $requestId, ['status' => $old], ['status' => $new]);
    }

    public function logDiscountApplied(int $actorId, string $role, int $saleId, array $discountData): UpeAuditLog
    {
        return $this->log($actorId, $role, 'discount.applied', 'sale', $saleId, null, $discountData);
    }

    public function logRefund(int $actorId, string $role, int $saleId, array $refundData): UpeAuditLog
    {
        return $this->log($actorId, $role, 'refund.processed', 'sale', $saleId, null, $refundData);
    }

    public function logAdjustment(int $actorId, string $role, int $adjustmentId, array $data): UpeAuditLog
    {
        return $this->log($actorId, $role, 'adjustment.executed', 'adjustment', $adjustmentId, null, $data);
    }

    public function logSubscriptionEvent(int $actorId, string $role, int $subscriptionId, string $event, array $data = []): UpeAuditLog
    {
        return $this->log($actorId, $role, 'subscription.' . $event, 'subscription', $subscriptionId, null, $data);
    }
}
