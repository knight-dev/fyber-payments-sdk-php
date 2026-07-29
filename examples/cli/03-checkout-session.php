#!/usr/bin/env php
<?php
/**
 * Example 11: Checkout Session (Hosted Checkout)
 *
 * Demonstrates: Creating a hosted checkout session and retrieving its status
 *
 * Run: php 11-checkout-session.php
 */

require_once __DIR__ . '/vendor/autoload.php';

use Dotenv\Dotenv;
use Fyber\Fyber;
use Fyber\Exceptions\FyberException;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

echo "╔═══════════════════════════════════════════════════════════╗\n";
echo "║     Fyber SDK Example: Checkout Session (Hosted)          ║\n";
echo "╚═══════════════════════════════════════════════════════════╝\n\n";

echo "This example demonstrates creating a hosted checkout session.\n";
echo "In a real application, you would redirect customers to the checkout URL.\n\n";

$fyber = new Fyber($_ENV['FYBER_API_KEY'], [
    'environment' => $_ENV['FYBER_ENVIRONMENT'] ?? 'test',
    'baseUrl' => $_ENV['FYBER_BASE_URL'] ?: null,
]);

try {
    // Step 1: Create checkout session
    echo "Step 1: Creating checkout session...\n";

    $session = $fyber->checkout->sessions->create([
        'mode' => 'payment',
        'intent' => 'sale',
        'amount' => 5000,
        'currency' => 'JMD',
        'successUrl' => 'https://example.com/success?session_id={SESSION_ID}',
        'cancelUrl' => 'https://example.com/cancel',
        'lineItems' => [
            [
                'name' => 'Demo Product',
                'quantity' => 1,
                'unitAmount' => 5000,
            ],
        ],
        'customerEmail' => 'test@example.com',
        'customerName' => 'Test Customer',
        'metadata' => [
            'example' => 'cli-checkout-session',
            'source' => 'php-sdk',
        ],
    ]);

    echo "\n  ✓ Session created!\n";
    echo "  Session ID: {$session['sessionId']}\n";
    echo "  Status: {$session['status']}\n";
    echo "  Checkout URL: {$session['url']}\n";
    echo "  Expires At: {$session['expiresAt']}\n";

    // Step 2: Retrieve session by session ID
    echo "\nStep 2: Retrieving session by session ID...\n";
    $retrieved = $fyber->checkout->sessions->getBySessionId($session['sessionId']);
    echo "  ✓ Retrieved session: {$retrieved['sessionId']}\n";
    echo "  Mode: {$retrieved['mode']}\n";
    printf("  Amount: %.2f %s\n", $retrieved['amount'] / 100, $retrieved['currency']);

    // Step 3: Expire the session
    echo "\nStep 3: Expiring session...\n";
    $expired = $fyber->checkout->sessions->expire($session['id']);
    echo "  ✓ Session expired: {$expired['status']}\n";

    echo "\n╔═══════════════════════════════════════════════════════════╗\n";
    echo "║            Example completed successfully!                ║\n";
    echo "╚═══════════════════════════════════════════════════════════╝\n\n";

    echo "In production, customers would:\n";
    echo "  1. Be redirected to the checkout URL\n";
    echo "  2. Enter their payment details securely\n";
    echo "  3. Complete payment and be redirected to your success URL\n";
    echo "  4. Your webhook handler receives checkout.session.completed event\n";

} catch (FyberException $e) {
    echo "\n❌ Error: {$e->getMessage()}\n";
    echo "   Code: {$e->getErrorCode()}\n";
    exit(1);
}
