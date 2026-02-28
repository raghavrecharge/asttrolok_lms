<?php

namespace App\Services\PaymentEngine;

use App\Models\PaymentEngine\Wallet;
use App\Models\PaymentEngine\WalletTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WalletService
{
    /**
     * Get or create a wallet for a user.
     */
    public function getOrCreateWallet(int $userId): Wallet
    {
        return Wallet::firstOrCreate(
            ['user_id' => $userId],
            ['balance' => 0, 'currency' => Wallet::CURRENCY_INR]
        );
    }

    /**
     * Get the current balance for a user.
     */
    public function balance(int $userId): float
    {
        $wallet = Wallet::where('user_id', $userId)->first();
        return $wallet ? (float) $wallet->balance : 0.00;
    }

    /**
     * Check if user can afford a given amount.
     */
    public function canAfford(int $userId, float $amount): bool
    {
        return $this->balance($userId) >= $amount;
    }

    /**
     * Credit (add) funds to a user's wallet.
     */
    public function credit(
        int $userId,
        float $amount,
        string $transactionType,
        ?string $description = null,
        ?string $referenceType = null,
        ?int $referenceId = null,
        ?string $gatewayTransactionId = null,
        ?array $metadata = null
    ): WalletTransaction {
        if ($amount <= 0) {
            throw new \InvalidArgumentException('Credit amount must be positive. Got: ' . $amount);
        }

        return DB::transaction(function () use ($userId, $amount, $transactionType, $description, $referenceType, $referenceId, $gatewayTransactionId, $metadata) {
            $wallet = $this->getOrCreateWallet($userId);

            // Lock the wallet row for update
            $wallet = Wallet::where('id', $wallet->id)->lockForUpdate()->first();

            $newBalance = (float) $wallet->balance + $amount;
            $wallet->update(['balance' => $newBalance]);

            $txn = WalletTransaction::create([
                'wallet_id' => $wallet->id,
                'user_id' => $userId,
                'type' => WalletTransaction::TYPE_CREDIT,
                'amount' => $amount,
                'balance_after' => $newBalance,
                'transaction_type' => $transactionType,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'gateway_transaction_id' => $gatewayTransactionId,
                'description' => $description,
                'metadata' => $metadata,
            ]);

            Log::info('WalletService: credit', [
                'user_id' => $userId,
                'amount' => $amount,
                'txn_type' => $transactionType,
                'new_balance' => $newBalance,
                'txn_id' => $txn->id,
            ]);

            return $txn;
        });
    }

    /**
     * Debit (deduct) funds from a user's wallet.
     */
    public function debit(
        int $userId,
        float $amount,
        string $transactionType,
        ?string $description = null,
        ?string $referenceType = null,
        ?int $referenceId = null,
        ?string $gatewayTransactionId = null,
        ?array $metadata = null
    ): WalletTransaction {
        if ($amount <= 0) {
            throw new \InvalidArgumentException('Debit amount must be positive. Got: ' . $amount);
        }

        return DB::transaction(function () use ($userId, $amount, $transactionType, $description, $referenceType, $referenceId, $gatewayTransactionId, $metadata) {
            $wallet = $this->getOrCreateWallet($userId);

            // Lock the wallet row for update
            $wallet = Wallet::where('id', $wallet->id)->lockForUpdate()->first();

            if ((float) $wallet->balance < $amount) {
                throw new \RuntimeException("Insufficient wallet balance. Available: {$wallet->balance}, Requested: {$amount}");
            }

            $newBalance = (float) $wallet->balance - $amount;
            $wallet->update(['balance' => $newBalance]);

            $txn = WalletTransaction::create([
                'wallet_id' => $wallet->id,
                'user_id' => $userId,
                'type' => WalletTransaction::TYPE_DEBIT,
                'amount' => $amount,
                'balance_after' => $newBalance,
                'transaction_type' => $transactionType,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'gateway_transaction_id' => $gatewayTransactionId,
                'description' => $description,
                'metadata' => $metadata,
            ]);

            Log::info('WalletService: debit', [
                'user_id' => $userId,
                'amount' => $amount,
                'txn_type' => $transactionType,
                'new_balance' => $newBalance,
                'txn_id' => $txn->id,
            ]);

            return $txn;
        });
    }

    /**
     * Top up wallet via Razorpay (after payment verification).
     */
    public function topUp(int $userId, float $amount, string $razorpayPaymentId): WalletTransaction
    {
        return $this->credit(
            $userId,
            $amount,
            WalletTransaction::TXN_TOP_UP,
            "Wallet top-up via Razorpay",
            'razorpay',
            null,
            $razorpayPaymentId,
            ['source' => 'razorpay', 'razorpay_payment_id' => $razorpayPaymentId]
        );
    }

    /**
     * Refund amount to user's wallet.
     */
    public function refundToWallet(int $userId, float $amount, ?int $saleId = null, ?string $description = null): WalletTransaction
    {
        return $this->credit(
            $userId,
            $amount,
            WalletTransaction::TXN_REFUND,
            $description ?? "Refund credited to wallet",
            'upe_sale',
            $saleId,
            null,
            ['source' => 'refund', 'sale_id' => $saleId]
        );
    }

    /**
     * Pay for a purchase from wallet balance.
     * Returns the wallet transaction on success.
     */
    public function payFromWallet(int $userId, float $amount, ?int $saleId = null, ?string $description = null): WalletTransaction
    {
        return $this->debit(
            $userId,
            $amount,
            WalletTransaction::TXN_WALLET_PAYMENT,
            $description ?? "Payment from wallet",
            'upe_sale',
            $saleId,
            null,
            ['source' => 'purchase', 'sale_id' => $saleId]
        );
    }

    /**
     * Get paginated transaction history for a user.
     */
    public function getTransactions(int $userId, int $perPage = 20)
    {
        $wallet = Wallet::where('user_id', $userId)->first();
        if (!$wallet) {
            return collect();
        }

        return WalletTransaction::where('wallet_id', $wallet->id)
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }
}
