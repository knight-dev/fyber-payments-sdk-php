<?php

namespace Fyber;

use Fyber\Http\HttpClient;
use Fyber\Resources\Payments;
use Fyber\Resources\Refunds;
use Fyber\Resources\Customers;
use Fyber\Resources\Checkout;
use Fyber\Resources\Subscriptions;
use Fyber\Resources\Installments;
use Fyber\Resources\Tokens;
use Fyber\Webhooks\WebhookVerifier;
use Fyber\Exceptions\FyberException;

/**
 * Fyber PHP SDK
 *
 * The main client for interacting with the Fyber Payment API.
 *
 * @property-read Payments $payments Payment operations
 * @property-read Refunds $refunds Refund operations
 * @property-read Customers $customers Customer operations
 * @property-read Checkout $checkout Checkout operations
 * @property-read Subscriptions $subscriptions Subscription operations
 * @property-read Installments $installments Installment plan operations (BNPL)
 * @property-read Tokens $tokens Token operations (saved payment methods)
 */
class Fyber
{
    /**
     * SDK Version
     */
    public const VERSION = '1.0.2';

    /**
     * Payment operations
     */
    public readonly Payments $payments;

    /**
     * Refund operations
     */
    public readonly Refunds $refunds;

    /**
     * Customer operations
     */
    public readonly Customers $customers;

    /**
     * Checkout operations
     */
    public readonly Checkout $checkout;

    /**
     * Subscription operations
     */
    public readonly Subscriptions $subscriptions;

    /**
     * Installment plan operations (BNPL)
     */
    public readonly Installments $installments;

    /**
     * Token operations (saved payment methods)
     */
    public readonly Tokens $tokens;

    /**
     * HTTP client instance
     */
    private HttpClient $http;

    /**
     * Create a new Fyber client
     *
     * @param string $apiKey Your Fyber API key
     * @param array $options Configuration options:
     *   - environment: 'test' or 'live' (default: 'test')
     *   - baseUrl: Custom API base URL (optional)
     *   - timeout: Request timeout in seconds (default: 30)
     * @throws FyberException If API key is not provided
     */
    public function __construct(string $apiKey, array $options = [])
    {
        if (empty($apiKey)) {
            throw new FyberException('API key is required', 'configuration_error');
        }

        $environment = $options['environment'] ?? 'test';
        $baseUrl = $options['baseUrl'] ?? null;
        $timeout = $options['timeout'] ?? 30;

        $this->http = new HttpClient($apiKey, $environment, $baseUrl, $timeout);

        // Initialize resources
        $this->payments = new Payments($this->http);
        $this->refunds = new Refunds($this->http);
        $this->customers = new Customers($this->http);
        $this->checkout = new Checkout($this->http);
        $this->subscriptions = new Subscriptions($this->http);
        $this->installments = new Installments($this->http);
        $this->tokens = new Tokens($this->http);
    }

    /**
     * Verify a webhook signature
     *
     * @param string|array $payload The raw webhook payload
     * @param string $signature The signature from the 'fyber-signature' header
     * @param string $secret Your webhook secret from the dashboard
     * @return array The verified webhook event
     * @throws FyberException If signature is invalid
     */
    public static function verifyWebhook(string|array $payload, string $signature, string $secret): array
    {
        return WebhookVerifier::verify($payload, $signature, $secret);
    }

    /**
     * Generate a test webhook signature
     *
     * @param string $payload The payload to sign
     * @param string $secret The signing secret
     * @return string The generated signature header
     */
    public static function generateTestWebhookSignature(string $payload, string $secret): string
    {
        return WebhookVerifier::generateTestSignature($payload, $secret);
    }
}
