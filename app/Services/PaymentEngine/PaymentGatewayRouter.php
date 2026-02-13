<?php

namespace App\Services\PaymentEngine;

use App\Services\PaymentEngine\Contracts\PaymentGatewayDriver;

class PaymentGatewayRouter
{
    /**
     * Registered gateway drivers keyed by method name.
     * @var array<string, PaymentGatewayDriver>
     */
    private array $drivers = [];

    /**
     * Register a gateway driver.
     */
    public function register(string $method, PaymentGatewayDriver $driver): void
    {
        $this->drivers[$method] = $driver;
    }

    /**
     * Get the driver for a given payment method.
     *
     * @throws \RuntimeException if method not registered
     */
    public function route(string $method): PaymentGatewayDriver
    {
        if (!isset($this->drivers[$method])) {
            throw new \RuntimeException("No payment gateway driver registered for method: {$method}");
        }

        return $this->drivers[$method];
    }

    /**
     * Check if a payment method has a registered driver.
     */
    public function supports(string $method): bool
    {
        return isset($this->drivers[$method]);
    }

    /**
     * Get all registered method names.
     */
    public function availableMethods(): array
    {
        return array_keys($this->drivers);
    }

    /**
     * Initiate payment via the appropriate gateway.
     */
    public function initiate(string $method, float $amount, string $currency, array $metadata = []): array
    {
        return $this->route($method)->initiate($amount, $currency, $metadata);
    }

    /**
     * Verify a payment via the appropriate gateway.
     */
    public function verify(string $method, string $transactionId): array
    {
        return $this->route($method)->verify($transactionId);
    }

    /**
     * Process a webhook via the appropriate gateway.
     */
    public function handleWebhook(string $method, array $payload): array
    {
        return $this->route($method)->handleWebhook($payload);
    }

    /**
     * Initiate a refund via the appropriate gateway.
     */
    public function refund(string $method, string $transactionId, float $amount): array
    {
        return $this->route($method)->refund($transactionId, $amount);
    }
}
