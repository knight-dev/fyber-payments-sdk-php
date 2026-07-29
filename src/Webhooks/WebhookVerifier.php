<?php

namespace Fyber\Webhooks;

use Fyber\Exceptions\FyberException;

class WebhookVerifier
{
    /**
     * Verify a webhook signature
     *
     * @param string|array $payload The raw webhook payload (JSON string or array)
     * @param string $signature The signature from the 'fyber-signature' header
     * @param string $secret Your webhook secret from the dashboard
     * @return array The verified webhook event
     * @throws FyberException If signature is invalid
     */
    public static function verify(string|array $payload, string $signature, string $secret): array
    {
        $payloadString = is_array($payload) ? json_encode($payload) : $payload;

        // Parse the signature header (format: t=timestamp,v1=signature)
        $signatureParts = self::parseSignature($signature);

        if (isset($signatureParts['t']) && isset($signatureParts['v1'])) {
            $timestamp = $signatureParts['t'];
            $expectedSignature = $signatureParts['v1'];

            // Check timestamp is within tolerance (5 minutes)
            $tolerance = 300; // 5 minutes in seconds
            $currentTime = time();
            if (abs($currentTime - (int)$timestamp) > $tolerance) {
                throw new FyberException('Webhook timestamp too old', 'invalid_signature');
            }

            // Compute expected signature
            $signedPayload = $timestamp . '.' . $payloadString;
            $computedSignature = hash_hmac('sha256', $signedPayload, $secret);

            // Constant-time comparison
            if (!hash_equals($computedSignature, $expectedSignature)) {
                throw new FyberException('Invalid webhook signature', 'invalid_signature');
            }
        } else {
            // Legacy format from older Fyber servers: bare base64 digest, signed
            // timestamp is the event's "created" field. Retried deliveries reuse the
            // original event time, so no freshness tolerance is applied.
            self::verifyLegacyBase64($payloadString, trim($signature), $secret);
        }

        // Parse and return the event
        $event = is_array($payload) ? $payload : json_decode($payload, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new FyberException('Invalid JSON payload', 'invalid_payload');
        }

        return $event;
    }

    /**
     * Verify the legacy bare-base64 signature format
     *
     * @param string $payload Raw payload string
     * @param string $signature Bare base64 HMAC digest
     * @param string $secret Webhook signing secret
     * @throws FyberException If signature is invalid
     */
    private static function verifyLegacyBase64(string $payload, string $signature, string $secret): void
    {
        $body = json_decode($payload, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new FyberException('Invalid JSON payload', 'invalid_payload');
        }

        $created = is_array($body) && isset($body['created']) && is_string($body['created'])
            ? strtotime($body['created'])
            : false;
        if ($created === false) {
            throw new FyberException('Invalid signature format', 'invalid_signature');
        }

        $provided = base64_decode($signature, true);
        if ($provided === false) {
            throw new FyberException('Invalid signature format', 'invalid_signature');
        }

        $computed = hash_hmac('sha256', $created . '.' . $payload, $secret, true);

        if (!hash_equals($computed, $provided)) {
            throw new FyberException('Invalid webhook signature', 'invalid_signature');
        }
    }

    /**
     * Construct a webhook event from payload (no signature verification)
     * Use only for testing purposes
     *
     * @param string|array $payload The webhook payload
     * @return array The webhook event
     */
    public static function constructEvent(string|array $payload): array
    {
        return is_array($payload) ? $payload : json_decode($payload, true);
    }

    /**
     * Parse signature header into components
     *
     * @param string $signature Signature header value
     * @return array Parsed components
     */
    private static function parseSignature(string $signature): array
    {
        $parts = [];
        foreach (explode(',', $signature) as $item) {
            $kv = explode('=', $item, 2);
            if (count($kv) === 2) {
                $parts[$kv[0]] = $kv[1];
            }
        }
        return $parts;
    }

    /**
     * Generate a webhook signature for testing purposes
     *
     * @param string $payload The payload to sign
     * @param string $secret The signing secret
     * @param int|null $timestamp Optional timestamp (defaults to current time)
     * @return string The generated signature header
     */
    public static function generateTestSignature(string $payload, string $secret, ?int $timestamp = null): string
    {
        $timestamp = $timestamp ?? time();
        $signedPayload = $timestamp . '.' . $payload;
        $signature = hash_hmac('sha256', $signedPayload, $secret);
        return "t={$timestamp},v1={$signature}";
    }
}
