<?php

namespace App\Services\PaymentEngine;

use App\Models\PaymentEngine\UpePaymentRequest;
use App\Models\PaymentEngine\UpeSale;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PaymentRequestService
{
    private AuditService $audit;

    public function __construct(AuditService $audit)
    {
        $this->audit = $audit;
    }

    /**
     * Create a new payment request.
     */
    public function create(
        string $requestType,
        int $userId,
        ?int $saleId = null,
        array $payload = []
    ): UpePaymentRequest {
        return UpePaymentRequest::create([
            'uuid' => (string) Str::uuid(),
            'request_type' => $requestType,
            'user_id' => $userId,
            'sale_id' => $saleId,
            'payload' => $payload,
            'status' => 'pending',
        ]);
    }

    /**
     * Verify a request (Support role).
     */
    public function verify(UpePaymentRequest $request, int $verifiedBy, ?array $additionalPayload = null): UpePaymentRequest
    {
        return $this->transition($request, 'verified', function ($req) use ($verifiedBy, $additionalPayload) {
            $updates = [
                'status' => 'verified',
                'verified_by' => $verifiedBy,
                'verified_at' => now(),
            ];

            if ($additionalPayload) {
                $updates['payload'] = array_merge($req->payload ?? [], $additionalPayload);
            }

            $req->update($updates);

            $this->audit->logRequestTransition($verifiedBy, 'support', $req->id, 'pending', 'verified');

            return $req->fresh();
        });
    }

    /**
     * Approve a request (Admin role).
     */
    public function approve(UpePaymentRequest $request, int $approvedBy, ?array $additionalPayload = null): UpePaymentRequest
    {
        return $this->transition($request, 'approved', function ($req) use ($approvedBy, $additionalPayload) {
            $updates = [
                'status' => 'approved',
                'approved_by' => $approvedBy,
                'approved_at' => now(),
            ];

            if ($additionalPayload) {
                $updates['payload'] = array_merge($req->payload ?? [], $additionalPayload);
            }

            $req->update($updates);

            $this->audit->logRequestTransition($approvedBy, 'admin', $req->id, 'verified', 'approved');

            return $req->fresh();
        });
    }

    /**
     * Execute an approved request. Idempotent — will not re-execute.
     *
     * @param callable $executor Function that performs the actual work. Receives (UpePaymentRequest) → array result.
     * @return array ['request' => UpePaymentRequest, 'result' => mixed]
     */
    public function execute(UpePaymentRequest $request, int $executedBy, callable $executor): array
    {
        return DB::transaction(function () use ($request, $executedBy, $executor) {
            $locked = UpePaymentRequest::where('id', $request->id)->lockForUpdate()->first();

            // Idempotency guard
            if ($locked->isExecuted()) {
                return [
                    'request' => $locked,
                    'result' => $locked->execution_result,
                    'already_executed' => true,
                ];
            }

            if ($locked->status !== 'approved') {
                throw new \RuntimeException(
                    "Request #{$locked->id} status is '{$locked->status}'. Must be 'approved' to execute."
                );
            }

            // Execute the actual business logic
            $result = $executor($locked);

            $locked->update([
                'status' => 'executed',
                'executed_at' => now(),
                'execution_result' => is_array($result) ? $result : ['result' => $result],
            ]);

            $this->audit->logRequestTransition($executedBy, 'admin', $locked->id, 'approved', 'executed');

            return [
                'request' => $locked->fresh(),
                'result' => $result,
                'already_executed' => false,
            ];
        });
    }

    /**
     * Reject a request at any stage.
     */
    public function reject(UpePaymentRequest $request, int $rejectedBy, string $reason): UpePaymentRequest
    {
        if ($request->isTerminal()) {
            throw new \RuntimeException("Request #{$request->id} is already in terminal status '{$request->status}'.");
        }

        $oldStatus = $request->status;

        $request->update([
            'status' => 'rejected',
            'rejected_reason' => $reason,
        ]);

        $this->audit->logRequestTransition($rejectedBy, 'admin', $request->id, $oldStatus, 'rejected');

        return $request->fresh();
    }

    /**
     * Validate and perform a state transition.
     */
    private function transition(UpePaymentRequest $request, string $targetStatus, callable $action): UpePaymentRequest
    {
        if (!$request->canTransitionTo($targetStatus)) {
            throw new \RuntimeException(
                "Cannot transition request #{$request->id} from '{$request->status}' to '{$targetStatus}'."
            );
        }

        if ($request->isTerminal()) {
            throw new \RuntimeException("Request #{$request->id} is in terminal status '{$request->status}'.");
        }

        return $action($request);
    }
}
