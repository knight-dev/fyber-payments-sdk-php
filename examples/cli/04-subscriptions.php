#!/usr/bin/env php
<?php
/**
 * Example 04: Subscriptions (Recurring Billing)
 *
 * Demonstrates: Creating and managing recurring subscriptions
 * Use case: SaaS subscriptions, membership plans, recurring services
 *
 * IMPORTANT: In production, use Hosted Checkout with mode='setup' to securely
 * save customer cards. The token ID will be available in the webhook response.
 */

require_once __DIR__ . '/vendor/autoload.php';

use Dotenv\Dotenv;
use Fyber\Fyber;
use Fyber\Exceptions\FyberException;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

echo "====================================================\n";
echo "     Fyber SDK Example: Subscriptions               \n";
echo "====================================================\n\n";

$fyber = new Fyber($_ENV['FYBER_API_KEY'], [
    'environment' => $_ENV['FYBER_ENVIRONMENT'] ?? 'test',
    'baseUrl' => $_ENV['FYBER_BASE_URL'] ?: null,
]);

try {
    // Step 1: Create a customer
    echo "Step 1: Creating customer...\n";
    $customer = $fyber->customers->create([
        'email' => 'subscription-test-' . time() . '@example.com',
        'name' => 'Subscription Test Customer',
        'phone' => '+1876555-0123',
        'metadata' => [
            'example' => 'subscriptions',
            'plan' => 'premium',
        ],
    ]);
    echo "  Customer created: {$customer['id']}\n";
    echo "  Email: {$customer['email']}\n\n";

    // Step 2: Explain how to save card for recurring billing
    echo "Step 2: Saving card for recurring billing...\n";
    echo "  In production, create a Hosted Checkout session with mode='setup':\n\n";
    echo "  \$session = \$fyber->checkout->sessions->create([\n";
    echo "    'mode' => 'setup',\n";
    echo "    'customerId' => \$customer['id'],\n";
    echo "    'successUrl' => 'https://example.com/success?session_id={SESSION_ID}',\n";
    echo "    'cancelUrl' => 'https://example.com/cancel',\n";
    echo "  ]);\n";
    echo "  // Redirect customer to \$session['url']\n\n";
    echo "  After checkout completes, your webhook receives the token ID.\n\n";

    // Step 3: Get subscription stats (doesn't require card)
    echo "Step 3: Getting subscription stats...\n";
    $stats = $fyber->subscriptions->stats();
    echo "  Stats retrieved\n";
    echo "    Active: {$stats['activeCount']}\n";
    echo "    Trialing: {$stats['trialingCount']}\n";
    echo "    MRR: $" . number_format($stats['mrr'] / 100, 2) . " {$stats['mrrCurrency']}\n\n";

    // Step 4: List subscriptions
    echo "Step 4: Listing subscriptions...\n";
    $subscriptions = $fyber->subscriptions->list(['limit' => 5]);
    echo "  Found " . count($subscriptions['data']) . " subscriptions\n";
    foreach ($subscriptions['data'] as $i => $s) {
        $num = $i + 1;
        $amount = number_format($s['amount'] / 100, 2);
        echo "    {$num}. {$s['id']} - {$s['status']} - \${$amount}/{$s['interval']}\n";
    }
    echo "\n";

    echo "Subscription API Operations:\n";
    echo "  - \$fyber->subscriptions->create(['customerId' => ..., 'tokenId' => ..., ...])\n";
    echo "  - \$fyber->subscriptions->get(\$id)\n";
    echo "  - \$fyber->subscriptions->list(['customerId' => ..., 'limit' => ...])\n";
    echo "  - \$fyber->subscriptions->pause(\$id)\n";
    echo "  - \$fyber->subscriptions->resume(\$id)\n";
    echo "  - \$fyber->subscriptions->cancel(\$id, ['cancelAtPeriodEnd' => true, 'reason' => ...])\n";
    echo "  - \$fyber->subscriptions->stats()\n\n";

    echo "Subscription Lifecycle:\n";
    echo "  CREATE -> TRIAL -> ACTIVE -> PAUSE -> RESUME -> CANCEL\n\n";

    echo "Webhook Events to Handle:\n";
    echo "  - subscription.created - Subscription activated\n";
    echo "  - subscription.payment_succeeded - Billing succeeded\n";
    echo "  - subscription.payment_failed - Billing failed\n";
    echo "  - subscription.trial_ending - Trial ends in 3 days\n";
    echo "  - subscription.canceled - Subscription canceled\n\n";

    echo "====================================================\n";
    echo "            Example completed successfully!         \n";
    echo "====================================================\n";
    exit(0);

} catch (FyberException $e) {
    echo "\nError: {$e->getMessage()}\n";
    if ($e->getCode()) {
        echo "  Code: {$e->getCode()}\n";
    }
    exit(1);
} catch (Exception $e) {
    echo "\nError: {$e->getMessage()}\n";
    exit(1);
}
