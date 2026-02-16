<?php

namespace App\Http\Controllers\PaymentEngine;

use App\Http\Controllers\Controller;
use App\Models\PaymentEngine\UpeSupportAction;
use App\Models\PaymentEngine\UpeMentorBadge;
use App\Models\PaymentEngine\UpeProduct;
use App\Models\PaymentEngine\UpeSale;
use App\Services\PaymentEngine\SupportEligibilityResolver;
use App\Services\PaymentEngine\SupportActionService;
use App\Services\PaymentEngine\AuditService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class SupportActionController extends Controller
{
    private SupportEligibilityResolver $resolver;
    private SupportActionService $actionService;
    private AuditService $audit;

    public function __construct(
        SupportEligibilityResolver $resolver,
        SupportActionService $actionService,
        AuditService $audit
    ) {
        $this->resolver      = $resolver;
        $this->actionService = $actionService;
        $this->audit         = $audit;
    }

    // ═══════════════════════════════════════════════════════════════
    //  Visibility Matrix
    // ═══════════════════════════════════════════════════════════════

    /**
     * GET /support/visibility?user_id=X&product_id=Y
     * Returns which support actions are visible for a user+product.
     */
    public function visibility(Request $request): JsonResponse
    {
        $request->validate([
            'user_id'    => 'required|integer|exists:users,id',
            'product_id' => 'required|integer|exists:upe_products,id',
        ]);

        $actions = $this->resolver->resolveVisibleActions(
            (int) $request->user_id,
            (int) $request->product_id
        );

        $visible = [];
        $hidden  = [];
        foreach ($actions as $type => $eligibility) {
            $entry = [
                'action_type' => $type,
                'eligible'    => $eligibility->eligible,
                'reason'      => $eligibility->reason,
                'context'     => $eligibility->context,
            ];
            $eligibility->eligible ? $visible[] = $entry : $hidden[] = $entry;
        }

        return response()->json([
            'user_id'    => (int) $request->user_id,
            'product_id' => (int) $request->product_id,
            'visible'    => $visible,
            'hidden'     => $hidden,
        ]);
    }

    /**
     * GET /support/user-matrix?user_id=X
     * Full matrix of all products × actions for a user.
     */
    public function userMatrix(Request $request): JsonResponse
    {
        $request->validate(['user_id' => 'required|integer|exists:users,id']);

        $matrix = $this->resolver->resolveUserMatrix((int) $request->user_id);

        return response()->json(['user_id' => (int) $request->user_id, 'matrix' => $matrix]);
    }

    // ═══════════════════════════════════════════════════════════════
    //  Eligibility Checks (individual)
    // ═══════════════════════════════════════════════════════════════

    /**
     * POST /support/check-eligibility
     */
    public function checkEligibility(Request $request): JsonResponse
    {
        $request->validate([
            'action_type' => 'required|string',
            'user_id'     => 'required|integer|exists:users,id',
            'product_id'  => 'required|integer|exists:upe_products,id',
            'beneficiary_id'   => 'nullable|integer|exists:users,id',
            'source_product_id' => 'nullable|integer|exists:upe_products,id',
            'coupon_code'      => 'nullable|string|max:100',
        ]);

        $userId    = (int) $request->user_id;
        $productId = (int) $request->product_id;

        $result = match ($request->action_type) {
            'extension'         => $this->resolver->canExtendCourse($userId, $productId),
            'temporary_access'  => $this->resolver->canGrantTemporaryAccess($userId, $productId),
            'mentor_access'     => $this->resolver->canGrantMentorAccess($userId, $productId),
            'relative_access'   => $this->resolver->canGrantRelativeAccess(
                $userId, (int) $request->beneficiary_id, $productId
            ),
            'offline_payment'   => $this->resolver->canRecordOfflinePayment($userId, $productId),
            'refund'            => $this->resolver->canRefund($userId, $productId),
            'payment_migration' => $this->resolver->canMigratePayment(
                $userId, (int) $request->source_product_id, $productId
            ),
            'coupon_apply'      => $this->resolver->canApplyCoupon($userId, $productId, $request->coupon_code),
            default             => throw new \InvalidArgumentException("Unknown action type: {$request->action_type}"),
        };

        return response()->json($result->toArray());
    }

    // ═══════════════════════════════════════════════════════════════
    //  Create Support Action (pending approval)
    // ═══════════════════════════════════════════════════════════════

    /**
     * POST /support/create
     */
    public function create(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'action_type'       => 'required|in:extension,temporary_access,mentor_access,relative_access,offline_payment,refund,payment_migration,coupon_apply',
            'user_id'           => 'required|integer|exists:users,id',
            'product_id'        => 'required|integer|exists:upe_products,id',
            'beneficiary_id'    => 'nullable|integer|exists:users,id',
            'source_product_id' => 'nullable|integer|exists:upe_products,id',
            'amount'            => 'nullable|numeric|min:0',
            'payment_method'    => 'nullable|string|max:50',
            'coupon_code'       => 'nullable|string|max:100',
            'metadata'          => 'nullable|array',
            'metadata.extension_days'  => 'nullable|integer|min:1|max:3650',
            'metadata.temporary_days'  => 'nullable|integer|min:1|max:365',
            'metadata.reason'          => 'nullable|string|max:1000',
        ]);

        $actorId = Auth::id();

        // Build idempotency key
        $idempotencyKey = "support_{$validated['action_type']}_{$validated['user_id']}_{$validated['product_id']}_"
            . ($validated['beneficiary_id'] ?? '0') . '_'
            . ($validated['source_product_id'] ?? '0');

        // Check idempotency
        $existing = UpeSupportAction::where('idempotency_key', $idempotencyKey)
            ->whereIn('status', [UpeSupportAction::STATUS_PENDING, UpeSupportAction::STATUS_APPROVED, UpeSupportAction::STATUS_EXECUTED])
            ->first();

        if ($existing) {
            return response()->json([
                'success' => false,
                'message' => 'A support action of this type is already active.',
                'action'  => $existing,
            ], 409);
        }

        $action = UpeSupportAction::create([
            'uuid'              => (string) Str::uuid(),
            'action_type'       => $validated['action_type'],
            'status'            => UpeSupportAction::STATUS_PENDING,
            'user_id'           => $validated['user_id'],
            'beneficiary_id'    => $validated['beneficiary_id'] ?? null,
            'product_id'        => $validated['product_id'],
            'source_product_id' => $validated['source_product_id'] ?? null,
            'amount'            => $validated['amount'] ?? 0,
            'payment_method'    => $validated['payment_method'] ?? null,
            'coupon_code'       => $validated['coupon_code'] ?? null,
            'metadata'          => $validated['metadata'] ?? null,
            'requested_by'      => $actorId,
            'requested_at'      => now(),
            'idempotency_key'   => $idempotencyKey,
        ]);

        $this->audit->log($actorId, 'admin', 'support.action.created', 'support_action', $action->id, null, $action->toArray());

        return response()->json([
            'success' => true,
            'message' => 'Support action created.',
            'action'  => $action,
        ], 201);
    }

    // ═══════════════════════════════════════════════════════════════
    //  Approve Support Action
    // ═══════════════════════════════════════════════════════════════

    /**
     * POST /support/{id}/approve
     */
    public function approve(int $id): JsonResponse
    {
        $action  = UpeSupportAction::findOrFail($id);
        $actorId = Auth::id();

        if (!$action->canBeApproved()) {
            return response()->json([
                'success' => false,
                'message' => "Action cannot be approved (status: {$action->status}).",
            ], 400);
        }

        $action->update([
            'status'      => UpeSupportAction::STATUS_APPROVED,
            'approved_by' => $actorId,
            'approved_at' => now(),
        ]);

        $this->audit->log($actorId, 'admin', 'support.action.approved', 'support_action', $action->id,
            ['status' => 'pending'], ['status' => 'approved']);

        return response()->json(['success' => true, 'message' => 'Action approved.', 'action' => $action->fresh()]);
    }

    // ═══════════════════════════════════════════════════════════════
    //  Reject Support Action
    // ═══════════════════════════════════════════════════════════════

    /**
     * POST /support/{id}/reject
     */
    public function reject(Request $request, int $id): JsonResponse
    {
        $request->validate(['reason' => 'required|string|max:1000']);

        $action  = UpeSupportAction::findOrFail($id);
        $actorId = Auth::id();

        if ($action->isExecuted()) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot reject an already-executed action.',
            ], 400);
        }

        $oldStatus = $action->status;
        $action->update([
            'status'           => UpeSupportAction::STATUS_REJECTED,
            'rejection_reason' => $request->reason,
        ]);

        $this->audit->log($actorId, 'admin', 'support.action.rejected', 'support_action', $action->id,
            ['status' => $oldStatus], ['status' => 'rejected', 'reason' => $request->reason]);

        return response()->json(['success' => true, 'message' => 'Action rejected.', 'action' => $action->fresh()]);
    }

    // ═══════════════════════════════════════════════════════════════
    //  Execute Support Action
    // ═══════════════════════════════════════════════════════════════

    /**
     * POST /support/{id}/execute
     */
    public function execute(int $id): JsonResponse
    {
        $action  = UpeSupportAction::findOrFail($id);
        $actorId = Auth::id();

        if ($action->isExecuted()) {
            return response()->json([
                'success' => true,
                'message' => 'Action already executed (idempotent).',
                'action'  => $action,
            ]);
        }

        try {
            $result = $this->actionService->execute($action, $actorId);

            return response()->json([
                'success' => true,
                'message' => "Support action '{$action->action_type}' executed successfully.",
                'action'  => $result,
            ]);
        } catch (\RuntimeException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    // ═══════════════════════════════════════════════════════════════
    //  List / Show
    // ═══════════════════════════════════════════════════════════════

    /**
     * GET /support/actions?user_id=X&action_type=Y&status=Z
     */
    public function index(Request $request): JsonResponse
    {
        $query = UpeSupportAction::query()->with(['product', 'user', 'beneficiary']);

        if ($request->user_id) {
            $query->where(function ($q) use ($request) {
                $q->where('user_id', $request->user_id)
                  ->orWhere('beneficiary_id', $request->user_id);
            });
        }

        if ($request->action_type) {
            $query->where('action_type', $request->action_type);
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        $actions = $query->orderByDesc('created_at')->paginate(25);

        return response()->json($actions);
    }

    /**
     * GET /support/actions/{id}
     */
    public function show(int $id): JsonResponse
    {
        $action = UpeSupportAction::with([
            'product', 'user', 'beneficiary',
            'sourceSale', 'targetSale',
            'requestedByUser', 'approvedByUser', 'executedByUser',
        ])->findOrFail($id);

        return response()->json($action);
    }

    // ═══════════════════════════════════════════════════════════════
    //  Mentor Badge Management
    // ═══════════════════════════════════════════════════════════════

    /**
     * POST /support/mentor/grant
     */
    public function grantMentorBadge(Request $request): JsonResponse
    {
        $request->validate([
            'user_id' => 'required|integer|exists:users,id',
            'reason'  => 'nullable|string|max:1000',
        ]);

        $actorId = Auth::id();
        $badge   = UpeMentorBadge::grant((int) $request->user_id, $actorId, $request->reason);

        $this->audit->log($actorId, 'admin', 'mentor.badge.granted', 'mentor_badge', $badge->id, null, [
            'user_id' => $request->user_id, 'reason' => $request->reason,
        ]);

        return response()->json(['success' => true, 'message' => 'Mentor badge granted.', 'badge' => $badge]);
    }

    /**
     * POST /support/mentor/revoke
     */
    public function revokeMentorBadge(Request $request): JsonResponse
    {
        $request->validate([
            'user_id' => 'required|integer|exists:users,id',
            'reason'  => 'nullable|string|max:1000',
        ]);

        $actorId = Auth::id();
        $revoked = UpeMentorBadge::revoke((int) $request->user_id, $request->reason);

        if (!$revoked) {
            return response()->json(['success' => false, 'message' => 'No active mentor badge found.'], 404);
        }

        $this->audit->log($actorId, 'admin', 'mentor.badge.revoked', 'mentor_badge', 0, null, [
            'user_id' => $request->user_id, 'reason' => $request->reason,
        ]);

        return response()->json(['success' => true, 'message' => 'Mentor badge revoked.']);
    }

    /**
     * GET /support/mentor/list
     */
    public function listMentors(): JsonResponse
    {
        $mentors = UpeMentorBadge::with('user')
            ->where('status', UpeMentorBadge::STATUS_ACTIVE)
            ->get();

        return response()->json(['mentors' => $mentors]);
    }
}
