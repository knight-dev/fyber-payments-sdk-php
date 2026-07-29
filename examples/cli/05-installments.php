#!/usr/bin/env php
<?php
/**
 * Example 05: BNPL Installment Plans (Buy Now, Pay Later)
 *
 * Demonstrates: Creating and managing installment payment plans
 * Use case: Split payments, layaway, financing large purchases
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
echo "     Fyber SDK Example: BNPL Installment Plans      \n";
echo "====================================================\n\n";

$fyber = new Fyber($_ENV['FYBER_API_KEY'], [
    'environment' => $_ENV['FYBER_ENVIRONMENT'] ?? 'test',
    'baseUrl' => $_ENV['FYBER_BASE_URL'] ?: null,
]);

try {
    // Step 1: Create a customer
    echo "Step 1: Creating customer...\n";
    $customer = $fyber->customers->create([
        'email' => 'bnpl-test-' . time() . '@example.com',
        'name' => 'BNPL Test Customer',
        'phone' => '+1876555-0456',
        'metadata' => [
            'example' => 'installments',
            'creditTier' => 'standard',
        ],
    ]);
    echo "  Customer created: {$customer['id']}\n";
    echo "  Email: {$customer['email']}\n\n";

    // Step 2: Check BNPL eligibility
    echo "Step 2: Checking BNPL eligibility...\n";
    echo "  Purchase amount: \$500.00\n\n";

    $eligibility = $fyber->installments->checkEligibility([
        'customerId' => $customer['id'],
        'amount' => 50000, // $500.00 in cents
    ]);

    echo "  Eligibility check results:\n";
    echo "    Eligible: " . ($eligibility['eligible'] ? 'true' : 'false') . "\n";
    echo "    Available Credit: $" . number_format($eligibility['availableCredit'] / 100, 2) . "\n";
    echo "    Available Options: " . implode(', ', $eligibility['availableOptions']) . " installments\n";
    echo "    Min Amount: $" . number_format($eligibility['minAmount'] / 100, 2) . "\n";
    echo "    Max Amount: $" . number_format($eligibility['maxAmount'] / 100, 2) . "\n\n";

    // Step 3: Explain how to save card for installment payments
    echo "Step 3: Saving card for installment payments...\n";
    echo "  In production, create a Hosted Checkout session with mode='setup':\n\n";
    echo "  \$session = \$fyber->checkout->sessions->create([\n";
    echo "    'mode' => 'setup',\n";
    echo "    'customerId' => \$customer['id'],\n";
    echo "    'successUrl' => 'https://example.com/success?session_id={SESSION_ID}',\n";
    echo "    'cancelUrl' => 'https://example.com/cancel',\n";
    echo "  ]);\n";
    echo "  // Redirect customer to \$session['url']\n\n";
    echo "  After checkout completes, your webhook receives the token ID.\n\n";

    // Step 4: Get installment stats (doesn't require card)
    echo "Step 4: Getting installment stats...\n";
    $stats = $fyber->installments->stats();
    echo "  Stats retrieved\n";
    echo "    Active Plans: {$stats['activeCount']}\n";
    echo "    Completed: {$stats['completedCount']}\n";
    echo "    Default Rate: " . number_format($stats['defaultRate'] * 100, 1) . "%\n";
    echo "    Total Outstanding: $" . number_format($stats['totalOutstanding'] / 100, 2) . "\n\n";

    // Step 5: List installment plans
    echo "Step 5: Listing installment plans...\n";
    $plans = $fyber->installments->list(['limit' => 5]);
    echo "  Found " . count($plans['data']) . " installment plans\n";
    foreach ($plans['data'] as $i => $p) {
        $num = $i + 1;
        $amount = number_format($p['totalAmount'] / 100, 2);
        echo "    {$num}. {$p['id']} - {$p['status']} - \${$amount} ({$p['installmentCount']} payments)\n";
    }
    echo "\n";

    echo "Installment API Operations:\n";
    echo "  - \$fyber->installments->checkEligibility(['customerId' => ..., 'amount' => ...])\n";
    echo "  - \$fyber->installments->create(['customerId' => ..., 'tokenId' => ..., 'totalAmount' => ..., ...])\n";
    echo "  - \$fyber->installments->get(\$id)\n";
    echo "  - \$fyber->installments->list(['customerId' => ..., 'limit' => ...])\n";
    echo "  - \$fyber->installments->cancel(\$id)\n";
    echo "  - \$fyber->installments->stats()\n\n";

    echo "Installment Plan Lifecycle:\n";
    echo "  CHECK ELIGIBILITY -> CREATE PLAN -> PAYMENTS -> COMPLETE\n\n";

    echo "Webhook Events to Handle:\n";
    echo "  - installment.plan_created - Plan activated, first payment charged\n";
    echo "  - installment.payment_succeeded - Scheduled payment completed\n";
    echo "  - installment.payment_failed - Payment failed (may retry)\n";
    echo "  - installment.plan_completed - All payments completed\n";
    echo "  - installment.plan_defaulted - Customer defaulted on payments\n\n";

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
