<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventPayment;
use App\Models\EventPaymentLink;
use App\Models\EventRegistration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Razorpay\Api\Api;
use App\Services\PaymentEngine\WalletService;
use App\Models\PaymentEngine\WalletTransaction;

class EventPaymentController extends Controller
{
    public function showPaymentPage($eventId, $token)
    {
        try {
            // Validate payment link
            $paymentLink = EventPaymentLink::where('event_id', $eventId)
                ->where('link_token', $token)
                ->first();

            if (!$paymentLink || !$paymentLink->isActive()) {
                return view('web.events.payment-invalid', [
                    'message' => 'Payment link is invalid or expired'
                ]);
            }

            // Increment click count
            $paymentLink->incrementClickCount();

            $event = $paymentLink->event;

            // Check if event is still accepting registrations
            if (!$event->isActive()) {
                return view('web.events.payment-invalid', [
                    'message' => 'Event registration is closed'
                ]);
            }

            return view('web.events.payment', compact('event', 'paymentLink'));

        } catch (\Exception $e) {
            Log::error('Event Payment Page Error: ' . $e->getMessage());
            return view('web.events.payment-invalid', [
                'message' => 'Something went wrong. Please try again.'
            ]);
        }
    }

    public function processPayment(Request $request, $eventId, $token)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|max:255',
                'phone' => 'required|string|max:20',
            ]);

            // Validate payment link
            $paymentLink = EventPaymentLink::where('event_id', $eventId)
                ->where('link_token', $token)
                ->first();

            if (!$paymentLink || !$paymentLink->isActive()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment link is invalid or expired'
                ]);
            }

            $event = $paymentLink->event;

            if (!$event->isActive()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Event registration is closed'
                ]);
            }

            // Initialize Razorpay
            $razorpayKey = config('services.razorpay.key');
            $razorpaySecret = config('services.razorpay.secret');

            if (!$razorpayKey || !$razorpaySecret) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment gateway not configured'
                ]);
            }

            $api = new Api($razorpayKey, $razorpaySecret);

            // ── Wallet-mediated: auto-apply wallet balance ──
            $walletDeduction = 0;
            $originalAmount = (int) round((float) $event->price);

            if (auth()->check() && $originalAmount > 0) {
                $walletService = app(WalletService::class);
                $userId = auth()->id();
                $walletBalance = $walletService->balance($userId);

                if ($walletBalance > 0) {
                    $walletDeduction = (int) min(floor($walletBalance), $originalAmount);
                }
            }

            $gatewayAmount = max($originalAmount - $walletDeduction, 0);

            // ── Full wallet payment ──
            if ($walletDeduction > 0 && $walletDeduction >= $originalAmount) {
                $userId = auth()->id();
                $walletService->purchaseFromWallet(
                    $userId,
                    $originalAmount,
                    null,
                    'Full wallet purchase for event: ' . $event->title
                );

                $payment = EventPayment::create([
                    'event_id' => $event->id,
                    'razorpay_order_id' => 'wallet_evt_' . $event->id . '_' . time(),
                    'amount' => $originalAmount,
                    'status' => 'completed',
                    'name' => $request->name,
                    'email' => $request->email,
                    'phone' => $request->phone,
                ]);

                EventRegistration::create([
                    'event_id' => $event->id,
                    'payment_id' => $payment->id,
                    'name' => $request->name,
                    'email' => $request->email,
                    'phone' => $request->phone,
                    'payment_status' => 'completed',
                ]);

                return response()->json([
                    'success' => true,
                    'wallet_paid' => true,
                    'wallet_deduction' => $walletDeduction,
                    'message' => 'Registration completed using wallet balance!',
                    'redirect_url' => '/events/pay/' . $eventId . '/' . $token . '/success',
                ]);
            }

            // Create Razorpay Order for shortfall only
            $razorpayOrder = $api->order->create([
                'receipt' => 'evt_' . $event->id . '_' . time(),
                'amount' => (int) $gatewayAmount * 100,
                'currency' => 'INR',
                'notes' => [
                    'event_id' => $event->id,
                    'event_name' => $event->title,
                    'customer_name' => $request->name,
                    'customer_email' => $request->email,
                    'customer_phone' => $request->phone,
                    'wallet_deduction' => $walletDeduction,
                    'original_amount' => $originalAmount,
                ]
            ]);

            // Create payment record (store wallet info for verifyPayment)
            $payment = EventPayment::create([
                'event_id' => $event->id,
                'razorpay_order_id' => $razorpayOrder['id'],
                'amount' => $originalAmount,
                'status' => 'pending',
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'payment_response' => json_encode([
                    'wallet_deduction' => $walletDeduction,
                    'original_amount' => $originalAmount,
                ]),
            ]);

            // Create registration record
            EventRegistration::create([
                'event_id' => $event->id,
                'payment_id' => $payment->id,
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'payment_status' => 'pending',
            ]);

            // Wallet NOT debited here — deferred to verifyPayment (wallet-mediated)

            return response()->json([
                'success' => true,
                'order_id' => $razorpayOrder['id'],
                'razorpay_key' => $razorpayKey,
                'amount' => (int) $gatewayAmount * 100,
                'currency' => 'INR',
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'description' => 'Registration for ' . $event->title,
                'wallet_deduction' => $walletDeduction,
            ]);

        } catch (\Exception $e) {
            Log::error('Event Payment Process Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Payment processing failed. Please try again.'
            ]);
        }
    }

    public function verifyPayment(Request $request, $eventId, $token)
    {
        try {
            $request->validate([
                'razorpay_payment_id' => 'required|string',
                'razorpay_order_id' => 'required|string',
                'razorpay_signature' => 'required|string',
            ]);

            // Validate payment link
            $paymentLink = EventPaymentLink::where('event_id', $eventId)
                ->where('link_token', $token)
                ->first();

            if (!$paymentLink) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid payment link'
                ]);
            }

            // Find payment record
            $payment = EventPayment::where('razorpay_order_id', $request->razorpay_order_id)
                ->where('event_id', $eventId)
                ->first();

            if (!$payment) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment record not found'
                ]);
            }

            // Verify Razorpay signature
            $razorpaySecret = config('services.razorpay.secret');
            $generatedSignature = hash_hmac('sha256', $request->razorpay_order_id . '|' . $request->razorpay_payment_id, $razorpaySecret);

            if ($generatedSignature !== $request->razorpay_signature) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment verification failed'
                ]);
            }

            // Wallet-mediated: credit gateway to wallet, debit full amount from wallet
            $walletData = is_string($payment->payment_response)
                ? json_decode($payment->payment_response, true)
                : ($payment->payment_response ?? []);
            $walletDeduction = (float) ($walletData['wallet_deduction'] ?? 0);
            $eventOriginalAmount = (float) ($walletData['original_amount'] ?? $payment->amount ?? 0);

            if ($walletDeduction > 0 && $eventOriginalAmount > 0 && auth()->check()) {
                $walletSvc = app(WalletService::class);
                $userId = auth()->id();
                $gatewayPaid = $eventOriginalAmount - $walletDeduction;

                // Step 1: Credit gateway amount to wallet
                if ($gatewayPaid > 0) {
                    $walletSvc->creditFromGateway($userId, $gatewayPaid, null, $request->razorpay_payment_id);
                }

                // Step 2: Debit full purchase amount from wallet
                $walletSvc->purchaseFromWallet($userId, $eventOriginalAmount, null, 'Event purchase: ' . ($event->title ?? 'Event #' . $eventId));

                Log::info('Event wallet-mediated payment completed', [
                    'user_id' => $userId, 'gateway' => $gatewayPaid,
                    'wallet_used' => $walletDeduction, 'total' => $eventOriginalAmount,
                ]);
            }

            // Update payment record
            $payment->update([
                'razorpay_payment_id' => $request->razorpay_payment_id,
                'razorpay_signature' => $request->razorpay_signature,
                'status' => 'paid',
                'payment_method' => 'razorpay',
                'payment_response' => json_encode(array_merge($walletData, $request->all())),
            ]);

            // Update registration
            if ($payment->registration) {
                $payment->registration->update([
                    'payment_status' => 'paid',
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Payment successful!',
                'redirect_url' => route('events.payment.success', [$eventId, $token])
            ]);

        } catch (\Exception $e) {
            Log::error('Event Payment Verification Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Payment verification failed'
            ]);
        }
    }

    public function paymentSuccess($eventId, $token)
    {
        try {
            $paymentLink = EventPaymentLink::where('event_id', $eventId)
                ->where('link_token', $token)
                ->first();

            if (!$paymentLink) {
                return redirect()->route('home')->with('error', 'Invalid payment link');
            }

            $event = $paymentLink->event;
            $payment = EventPayment::where('event_id', $eventId)
                ->where('status', 'paid')
                ->orderBy('created_at', 'desc')
                ->first();

            if (!$payment) {
                return redirect()->route('home')->with('error', 'Payment not found');
            }

            return view('web.events.payment-success', compact('event', 'payment'));

        } catch (\Exception $e) {
            Log::error('Event Payment Success Error: ' . $e->getMessage());
            return redirect()->route('home')->with('error', 'Something went wrong');
        }
    }

    public function paymentFailed($eventId, $token)
    {
        try {
            $paymentLink = EventPaymentLink::where('event_id', $eventId)
                ->where('link_token', $token)
                ->first();

            if (!$paymentLink) {
                return redirect()->route('home')->with('error', 'Invalid payment link');
            }

            $event = $paymentLink->event;

            return view('web.events.payment-failed', compact('event'));

        } catch (\Exception $e) {
            Log::error('Event Payment Failed Error: ' . $e->getMessage());
            return redirect()->route('home')->with('error', 'Something went wrong');
        }
    }
}
