<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Services\PaymentEngine\WalletService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Razorpay\Api\Api;

class WalletController extends Controller
{
    protected $walletService;
    protected $razorpayApi;

    public function __construct(WalletService $walletService)
    {
        $this->walletService = $walletService;
        $this->razorpayApi = new Api(
            env('RAZORPAY_API_KEY'),
            env('RAZORPAY_API_SECRET')
        );
    }

    /**
     * Show wallet page with balance and transaction history.
     */
    public function index()
    {
        $user = auth()->user();
        $wallet = $this->walletService->getOrCreateWallet($user->id);
        $transactions = $this->walletService->getTransactions($user->id, 20);

        $data = [
            'pageTitle' => 'My Wallet',
            'wallet' => $wallet,
            'transactions' => $transactions,
        ];

        return view('web.default.panel.wallet.index', $data);
    }

    /**
     * Create a Razorpay order for wallet top-up.
     */
    public function addFunds(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1|max:500000',
        ]);

        $user = auth()->user();
        $amount = (float) $request->input('amount');

        try {
            $razorpayOrder = $this->razorpayApi->order->create([
                'receipt' => 'wallet_topup_' . $user->id . '_' . time(),
                'amount' => (int) round($amount * 100),
                'currency' => currency(),
                'notes' => [
                    'purpose' => 'wallet_topup',
                    'user_id' => $user->id,
                    'amount' => $amount,
                ],
            ]);

            return response()->json([
                'success' => true,
                'razorpay_order_id' => $razorpayOrder['id'],
                'amount' => $razorpayOrder['amount'],
                'currency' => $razorpayOrder['currency'],
                'key' => env('RAZORPAY_API_KEY'),
                'user_name' => $user->full_name,
                'user_email' => $user->email,
                'user_contact' => $user->mobile,
            ]);
        } catch (\Exception $e) {
            Log::error('Wallet top-up order creation failed: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to create payment order'], 500);
        }
    }

    /**
     * Verify Razorpay payment and credit wallet.
     */
    public function verifyTopUp(Request $request)
    {
        $request->validate([
            'razorpay_payment_id' => 'required|string',
            'razorpay_order_id' => 'required|string',
            'razorpay_signature' => 'required|string',
        ]);

        $user = auth()->user();

        try {
            // Verify signature
            $attributes = [
                'razorpay_order_id' => $request->razorpay_order_id,
                'razorpay_payment_id' => $request->razorpay_payment_id,
                'razorpay_signature' => $request->razorpay_signature,
            ];

            $this->razorpayApi->utility->verifyPaymentSignature($attributes);

            // Fetch payment details from Razorpay
            $payment = $this->razorpayApi->payment->fetch($request->razorpay_payment_id);
            $amountInRupees = (float) $payment->amount / 100;

            // Check for duplicate processing
            $existingTxn = \App\Models\PaymentEngine\WalletTransaction::where('gateway_transaction_id', $request->razorpay_payment_id)->first();
            if ($existingTxn) {
                return response()->json([
                    'success' => true,
                    'message' => 'Payment already processed',
                    'balance' => $this->walletService->balance($user->id),
                ]);
            }

            // Credit wallet
            $txn = $this->walletService->topUp($user->id, $amountInRupees, $request->razorpay_payment_id);

            Log::info('Wallet top-up successful', [
                'user_id' => $user->id,
                'amount' => $amountInRupees,
                'razorpay_payment_id' => $request->razorpay_payment_id,
                'txn_id' => $txn->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Wallet topped up successfully',
                'balance' => $this->walletService->balance($user->id),
                'amount_added' => $amountInRupees,
            ]);
        } catch (\Exception $e) {
            Log::error('Wallet top-up verification failed: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'razorpay_payment_id' => $request->razorpay_payment_id,
            ]);
            return response()->json(['error' => 'Payment verification failed: ' . $e->getMessage()], 500);
        }
    }

    /**
     * API endpoint to get wallet balance (used by checkout pages).
     */
    public function getBalance()
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json(['balance' => 0]);
        }

        return response()->json([
            'balance' => $this->walletService->balance($user->id),
        ]);
    }
}
