#!/usr/bin/env php
<?php
/**
 * Example 08: Customers CRUD
 *
 * Demonstrates: Customer create, read, update, delete operations
 */

require_once __DIR__ . '/vendor/autoload.php';

use Dotenv\Dotenv;
use Fyber\Fyber;
use Fyber\Exceptions\FyberException;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

echo "╔═══════════════════════════════════════════════════════════╗\n";
echo "║     Fyber SDK Example: Customers CRUD                     ║\n";
echo "╚═══════════════════════════════════════════════════════════╝\n\n";

$fyber = new Fyber($_ENV['FYBER_API_KEY'], [
    'environment' => $_ENV['FYBER_ENVIRONMENT'] ?? 'test',
    'baseUrl' => $_ENV['FYBER_BASE_URL'] ?: null,
]);

try {
    // Step 1: Create customer
    echo "Step 1: Creating customer...\n";
    $uniqueEmail = 'crud-test-' . time() . '@example.com';

    $customer = $fyber->customers->create([
        'email' => $uniqueEmail,
        'firstName' => 'CRUD',
        'lastName' => 'Test User',
        'phone' => '+1-876-555-0100',
        'metadata' => [
            'source' => 'sdk-example',
            'tier' => 'standard',
        ],
    ]);

    echo "  ✓ Customer created!\n";
    echo "  ID: {$customer['id']}\n";
    echo "  Email: {$customer['email']}\n";
    echo "  Name: {$customer['firstName']} {$customer['lastName']}\n";

    // Step 2: Retrieve customer
    echo "\nStep 2: Retrieving customer...\n";
    $retrieved = $fyber->customers->get($customer['id']);
    echo "  ✓ Customer retrieved!\n";
    echo "  ID: {$retrieved['id']}\n";
    echo "  Email: {$retrieved['email']}\n";

    // Step 3: Update customer
    echo "\nStep 3: Updating customer...\n";
    $updated = $fyber->customers->update($customer['id'], [
        'firstName' => 'Updated',
        'lastName' => 'CRUD User',
        'phone' => '+1-876-555-0200',
        'metadata' => [
            'source' => 'sdk-example',
            'tier' => 'premium',
            'updated' => 'true',
        ],
    ]);

    echo "  ✓ Customer updated!\n";
    echo "  New Name: {$updated['firstName']} {$updated['lastName']}\n";
    echo "  New Phone: " . ($updated['phone'] ?? 'N/A') . "\n";

    // Step 4: List customers
    echo "\nStep 4: Listing customers...\n";
    $customers = $fyber->customers->list(['limit' => 5]);

    $count = count($customers['data'] ?? []);
    echo "  ✓ Found {$count} customer(s)\n";

    foreach (($customers['data'] ?? []) as $i => $c) {
        echo "  " . ($i + 1) . ". {$c['email']} ({$c['id']})\n";
    }

    // Step 5: Create payment for customer
    echo "\nStep 5: Creating payment for customer...\n";
    $payment = $fyber->payments->create([
        'amount' => 1500,
        'currency' => 'JMD',
        'intent' => 'sale',
        'customerId' => $customer['id'],
        'source' => [
            'type' => 'card',
            'number' => '4242424242424242',
            'expMonth' => 12,
            'expYear' => 2030,
            'cvv' => '123',
            'name' => $updated['firstName'] . ' ' . $updated['lastName'],
        ],
        'description' => 'Payment linked to customer',
    ]);

    echo "  ✓ Payment created!\n";
    echo "  Payment ID: {$payment['id']}\n";
    echo "  Customer ID: " . ($payment['customerId'] ?? $customer['id']) . "\n";

    // Step 6: Delete customer (optional - comment out to keep customer)
    echo "\nStep 6: Deleting customer...\n";
    $deleted = $fyber->customers->delete($customer['id']);
    echo "  ✓ Customer deleted!\n";

    echo "\n╔═══════════════════════════════════════════════════════════╗\n";
    echo "║            Example completed successfully!                ║\n";
    echo "╚═══════════════════════════════════════════════════════════╝\n";

} catch (FyberException $e) {
    echo "\n❌ Error: {$e->getMessage()}\n";
    exit(1);
}
