<?php

namespace App\Services\PaymentEngine\Contracts;

interface PaymentGatewayDriver
{
    /**
     * Initiate a payment with the gateway.
     *
     * @param float  $amount   Amount to charge
     * @param string $currency Currency code (INR, USD, etc.)
     * @param array  $metadata Order/sale metadata for the gateway
     * @return array ['payment_url' => string|null, 'transaction_id' => string|null, 'raw_response' => array]
     */
    public function initiate(float $amount, string $currency, array $metadata): array;

    /**
     * Verify a completed payment by its transaction ID.
     *
     * @param string $transactionId Gateway-assigned transaction identifier
     * @return array ['verified' => bool, 'amount' => float, 'currency' => string, 'raw_response' => array]
     */
    public function verify(string $transactionId): array;

    /**
     * Process an incoming webhook payload from the gateway.
     *
     * @param array $payload Raw webhook payload
     * @return array ['event' => string, 'transaction_id' => string, 'amount' => float, 'status' => string, 'raw' => array]
     */
    public function handleWebhook(array $payload): array;

    /**
     * Initiate a refund through the gateway.
     *
     * @param string $transactionId Original payment transaction ID
     * @param float  $amount        Amount to refund
     * @return array ['refund_id' => string, 'status' => string, 'raw_response' => array]
     */
    public function refund(string $transactionId, float $amount): array;

    /**
     * Get the gateway method identifier.
     */
    public function getMethod(): string;
}
