<?php

namespace App\Http\Controllers;

use App\Models\OfflinePayment;
use App\Models\Webinar;
use App\Models\Sale;
use App\Models\Financial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class OfflinePaymentController extends Controller
{
    /**
     * Show payment submission form
     */
    public function create($webinarId)
    {
        $user = Auth::user();
        
        if (!$user) {
            return redirect()->route('login')->with('error', 'Please login to submit payment details.');
        }

        $webinar = Webinar::findOrFail($webinarId);

        // Check if user already has access to this course
        $existingSale = Sale::where('buyer_id', $user->id)
            ->where('webinar_id', $webinarId)
            ->whereNull('refund_at')
            ->first();

        if ($existingSale) {
            return redirect()->route('webinar', $webinarId)->with('info', 'You already have access to this course.');
        }

        // Check if there's already a pending payment for this course
        $pendingPayment = OfflinePayment::where('user_id', $user->id)
            ->where('webinar_id', $webinarId)
            ->whereIn('status', [OfflinePayment::$pending, OfflinePayment::$waiting])
            ->first();

        $data = [
            'pageTitle' => 'Submit Payment Details - ' . $webinar->title,
            'webinar' => $webinar,
            'pendingPayment' => $pendingPayment,
        ];

        return view('web.default.offline_payment.create', $data);
    }

    /**
     * Store payment submission
     */
    public function store(Request $request, $webinarId)
    {
        $user = Auth::user();
        
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Authentication required.'], 401);
        }

        $webinar = Webinar::findOrFail($webinarId);

        $validated = $request->validate([
            'amount' => 'required|numeric|min:0',
            'utr_number' => 'required|string|max:100',
            'payment_date' => 'required|date|before_or_equal:today',
            'bank_name' => 'required|string|max:255',
            'screenshot' => 'required|file|image|mimes:jpeg,png,jpg|max:5120',
            'remark' => 'nullable|string|max:1000',
        ]);

        try {
            DB::beginTransaction();

            // Handle file upload
            $screenshotPath = null;
            if ($request->hasFile('screenshot')) {
                $file = $request->file('screenshot');
                $fileName = time() . '_' . $file->getClientOriginalName();
                $filePath = $file->storeAs('offline-payments/' . $user->id, $fileName, 'public');
                $screenshotPath = $filePath;
            }

            // Create offline payment entry
            $offlinePayment = OfflinePayment::create([
                'user_id' => $user->id,
                'webinar_id' => $webinarId,
                'amount' => $validated['amount'],
                'bank' => $validated['bank_name'],
                'utr_number' => $validated['utr_number'],
                'screenshot_path' => $screenshotPath,
                'pay_date' => $validated['payment_date'],
                'status' => OfflinePayment::$pending,
                'reference_number' => $validated['utr_number'], // For backward compatibility
            ]);

            // Log the payment submission
            Log::info('Offline payment submitted', [
                'payment_id' => $offlinePayment->id,
                'user_id' => $user->id,
                'webinar_id' => $webinarId,
                'amount' => $validated['amount'],
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Your payment details have been submitted successfully. Our team will verify the payment and activate your course.',
                'payment_id' => $offlinePayment->id,
                'redirect_url' => route('offline_payment.show', $offlinePayment->id)
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error submitting offline payment: ' . $e->getMessage());

            // Clean up uploaded file if there was an error
            if ($screenshotPath && Storage::disk('public')->exists($screenshotPath)) {
                Storage::disk('public')->delete($screenshotPath);
            }

            return response()->json([
                'success' => false,
                'message' => 'Error submitting payment details. Please try again.'
            ], 500);
        }
    }

    /**
     * Show payment details to user
     */
    public function show($paymentId)
    {
        $user = Auth::user();
        
        if (!$user) {
            return redirect()->route('login');
        }

        $payment = OfflinePayment::with(['webinar', 'user'])
            ->where('user_id', $user->id)
            ->findOrFail($paymentId);

        $data = [
            'pageTitle' => 'Payment Details - ' . $payment->webinar->title,
            'payment' => $payment,
        ];

        return view('web.default.offline_payment.show', $data);
    }

    /**
     * List user's offline payments
     */
    public function index()
    {
        $user = Auth::user();
        
        if (!$user) {
            return redirect()->route('login');
        }

        $payments = OfflinePayment::with(['webinar'])
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $data = [
            'pageTitle' => 'My Offline Payments',
            'payments' => $payments,
        ];

        return view('web.default.offline_payment.index', $data);
    }

    /**
     * Admin: List all offline payments
     */
    public function adminIndex(Request $request)
    {
        $query = OfflinePayment::with(['user', 'webinar']);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Search by user name, email, or UTR
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('utr_number', 'like', "%{$search}%")
                  ->orWhereHas('user', function($subQ) use ($search) {
                      $subQ->where('full_name', 'like', "%{$search}%")
                           ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        $payments = $query->orderBy('created_at', 'desc')->paginate(20);

        // Statistics
        $stats = [
            'total' => OfflinePayment::count(),
            'pending' => OfflinePayment::pending()->count(),
            'approved' => OfflinePayment::approved()->count(),
            'rejected' => OfflinePayment::rejected()->count(),
            'failed' => OfflinePayment::failed()->count(),
        ];

        $data = [
            'pageTitle' => 'Offline Payment Management',
            'payments' => $payments,
            'stats' => $stats,
        ];

        return view('admin.offline_payments.index', $data);
    }

    /**
     * Admin: Show payment details
     */
    public function adminShow($paymentId)
    {
        $payment = OfflinePayment::with(['user', 'webinar', 'sale', 'processedBy'])
            ->findOrFail($paymentId);

        $data = [
            'pageTitle' => 'Payment Details - ' . $payment->utr_number,
            'payment' => $payment,
        ];

        return view('admin.offline_payments.show', $data);
    }

    /**
     * Admin: Approve payment
     */
    public function approve(Request $request, $paymentId)
    {
        $validated = $request->validate([
            'admin_remark' => 'nullable|string|max:1000',
        ]);

        try {
            DB::beginTransaction();

            $payment = OfflinePayment::findOrFail($paymentId);

            if (!$payment->canBeApproved()) {
                return response()->json([
                    'success' => false,
                    'message' => 'This payment cannot be approved.'
                ], 400);
            }

            // Check if user already has access to this course
            $existingSale = Sale::where('buyer_id', $payment->user_id)
                ->where('webinar_id', $payment->webinar_id)
                ->whereNull('refund_at')
                ->first();

            if ($existingSale) {
                // Update payment status but don't create new sale
                $payment->update([
                    'status' => OfflinePayment::$approved,
                    'admin_remark' => $validated['admin_remark'],
                    'processed_by' => Auth::id(),
                    'processed_at' => now(),
                    'sale_id' => $existingSale->id,
                ]);

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Payment approved. User already had access to the course.',
                    'action' => 'approved_existing'
                ]);
            }

            // Create new sale/order
            $sale = Sale::create([
                'buyer_id' => $payment->user_id,
                'seller_id' => $payment->webinar->creator_id,
                'webinar_id' => $payment->webinar_id,
                'type' => 'webinar',
                'payment_method' => 'offline',
                'amount' => $payment->amount,
                'tax' => 0,
                'commission' => 0,
                'discount' => 0,
                'total_amount' => $payment->amount,
                'status' => 'success',
                'created_at' => time(),
                'reference_id' => 'OFFLINE-' . $payment->id,
            ]);

            // Create financial entry
            Financial::create([
                'user_id' => $payment->user_id,
                'webinar_id' => $payment->webinar_id,
                'type' => 'sale',
                'amount' => $payment->amount,
                'payment_method' => 'offline',
                'status' => 'success',
                'created_at' => time(),
                'reference_id' => $sale->id,
                'description' => 'Offline payment approved - Payment #' . $payment->id,
            ]);

            // UPE dual-write: create UPE sale + ledger entry
            try {
                $webinar = $payment->webinar;
                $productType = match ($webinar->type ?? 'course') {
                    'webinar' => 'webinar',
                    default => 'course_video',
                };

                $upeProduct = \App\Models\PaymentEngine\UpeProduct::firstOrCreate(
                    ['external_id' => $webinar->id, 'product_type' => $productType],
                    ['name' => $webinar->slug ?? "webinar-{$webinar->id}", 'base_fee' => $payment->amount, 'validity_days' => $webinar->access_days, 'status' => 'active']
                );

                $existingUpeSale = \App\Models\PaymentEngine\UpeSale::where('user_id', $payment->user_id)
                    ->where('product_id', $upeProduct->id)
                    ->whereIn('status', ['active', 'partially_refunded'])
                    ->first();

                if (!$existingUpeSale) {
                    $validFrom = now();
                    $validUntil = $webinar->access_days ? $validFrom->copy()->addDays($webinar->access_days) : null;

                    $upeSale = \App\Models\PaymentEngine\UpeSale::create([
                        'uuid' => (string) \Illuminate\Support\Str::uuid(),
                        'user_id' => $payment->user_id,
                        'product_id' => $upeProduct->id,
                        'sale_type' => 'paid',
                        'pricing_mode' => 'full',
                        'base_fee_snapshot' => $payment->amount,
                        'status' => 'active',
                        'valid_from' => $validFrom,
                        'valid_until' => $validUntil,
                        'metadata' => json_encode(['legacy_sale_id' => $sale->id, 'source' => 'offline_payment_approve', 'offline_payment_id' => $payment->id]),
                    ]);

                    app(\App\Services\PaymentEngine\PaymentLedgerService::class)->append(
                        saleId: $upeSale->id,
                        entryType: \App\Models\PaymentEngine\UpeLedgerEntry::TYPE_PAYMENT,
                        direction: \App\Models\PaymentEngine\UpeLedgerEntry::DIR_CREDIT,
                        amount: (float) $payment->amount,
                        paymentMethod: 'offline',
                        description: "Offline payment approved #{$payment->id}",
                        idempotencyKey: "offline_payment_{$payment->id}",
                    );

                    \Illuminate\Support\Facades\Cache::forget(\App\Services\PaymentEngine\AccessEngine::CACHE_PREFIX . "{$payment->user_id}_{$upeProduct->id}");
                }
            } catch (\Exception $e) {
                Log::warning('UPE dual-write failed in offline approve, legacy sale preserved', [
                    'payment_id' => $payment->id, 'error' => $e->getMessage(),
                ]);
            }

            // Update payment status
            $payment->update([
                'status' => OfflinePayment::$approved,
                'admin_remark' => $validated['admin_remark'],
                'sale_id' => $sale->id,
                'processed_by' => Auth::id(),
                'processed_at' => now(),
            ]);

            // Send notification to user
            $this->sendPaymentNotification($payment, 'approved');

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Payment approved successfully. Course access granted to user.',
                'sale_id' => $sale->id,
                'action' => 'approved_new'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error approving offline payment: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error approving payment. Please try again.'
            ], 500);
        }
    }

    /**
     * Admin: Reject payment
     */
    public function reject(Request $request, $paymentId)
    {
        $validated = $request->validate([
            'admin_remark' => 'required|string|max:1000',
        ]);

        try {
            $payment = OfflinePayment::findOrFail($paymentId);

            if (!$payment->canBeApproved()) {
                return response()->json([
                    'success' => false,
                    'message' => 'This payment cannot be rejected.'
                ], 400);
            }

            $payment->update([
                'status' => OfflinePayment::$failed,
                'admin_remark' => $validated['admin_remark'],
                'processed_by' => Auth::id(),
                'processed_at' => now(),
            ]);

            // Send notification to user
            $this->sendPaymentNotification($payment, 'rejected');

            Log::info('Offline payment rejected', [
                'payment_id' => $paymentId,
                'processed_by' => Auth::id(),
                'remark' => $validated['admin_remark'],
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Payment rejected successfully.'
            ]);

        } catch (\Exception $e) {
            Log::error('Error rejecting offline payment: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error rejecting payment. Please try again.'
            ], 500);
        }
    }

    /**
     * Send payment notification to user
     */
    private function sendPaymentNotification($payment, $status)
    {
        try {
            $user = $payment->user;
            $webinar = $payment->webinar;

            if ($user && $webinar) {
                $notifyOptions = [
                    '[c.title]' => $webinar->title,
                    '[payment.amount]' => $payment->getFormattedAmount(),
                    '[utr.number]' => $payment->getUtrNumber(),
                    '[payment.id]' => $payment->id,
                ];

                if ($status === 'approved') {
                    sendNotification('offline_payment_approved', $notifyOptions, $user->id);
                } else {
                    sendNotification('offline_payment_rejected', $notifyOptions, $user->id);
                }
            }
        } catch (\Exception $e) {
            Log::error('Error sending payment notification: ' . $e->getMessage());
        }
    }
}
