#!/usr/bin/env php
<?php
/**
 * Example 10: Webhooks Test
 *
 * Demonstrates: Webhook signature verification and event handling
 */

require_once __DIR__ . '/vendor/autoload.php';

use Dotenv\Dotenv;
use Fyber\Fyber;
use Fyber\Webhooks;
use Fyber\Exceptions\SignatureVerificationException;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

echo "╔═══════════════════════════════════════════════════════════╗\n";
echo "║     Fyber SDK Example: Webhooks Test                      ║\n";
echo "╚═══════════════════════════════════════════════════════════╝\n\n";

$webhookSecret = $_ENV['FYBER_WEBHOOK_SECRET'] ?? 'whsec_test_secret_for_demo';

// Sample webhook event
$sampleEvent = [
    'id' => 'evt_' . bin2hex(random_bytes(12)),
    'type' => 'payment.completed',
    'created' => time(),
    'data' => [
        'object' => [
            'id' => 'pay_' . bin2hex(random_bytes(12)),
            'amount' => 5000,
            'currency' => 'JMD',
            'status' => 'completed',
            'customerId' => 'cus_' . bin2hex(random_bytes(12)),
        ],
    ],
];

$payload = json_encode($sampleEvent);

// ========== Test 1: Valid Signature ==========
echo "══════════════════════════════════════════════════════════\n";
echo "  Test 1: Valid Signature Verification\n";
echo "══════════════════════════════════════════════════════════\n\n";

$timestamp = time();
$signedPayload = "{$timestamp}.{$payload}";
$signature = hash_hmac('sha256', $signedPayload, $webhookSecret);
$signatureHeader = "t={$timestamp},v1={$signature}";

echo "Webhook Details:\n";
echo "  Event Type: {$sampleEvent['type']}\n";
echo "  Event ID: {$sampleEvent['id']}\n";
echo "  Timestamp: " . date('Y-m-d H:i:s', $timestamp) . "\n";
echo "  Signature Header: " . substr($signatureHeader, 0, 50) . "...\n\n";

try {
    $event = Webhooks::constructEvent($payload, $signatureHeader, $webhookSecret);
    echo "  ✓ Signature verified successfully!\n";
    echo "  Event Type: {$event['type']}\n";
    echo "  Payment ID: {$event['data']['object']['id']}\n";
    printf("  Amount: $%.2f JMD\n", $event['data']['object']['amount'] / 100);
} catch (SignatureVerificationException $e) {
    echo "  ✗ Signature verification failed: {$e->getMessage()}\n";
}

// ========== Test 2: Invalid Signature ==========
echo "\n══════════════════════════════════════════════════════════\n";
echo "  Test 2: Invalid Signature (Should Fail)\n";
echo "══════════════════════════════════════════════════════════\n\n";

$badSignature = "t={$timestamp},v1=invalid_signature_here";

try {
    Webhooks::constructEvent($payload, $badSignature, $webhookSecret);
    echo "  ✗ Should have failed but didn't!\n";
} catch (SignatureVerificationException $e) {
    echo "  ✓ Correctly rejected invalid signature!\n";
    echo "  Error: {$e->getMessage()}\n";
}

// ========== Test 3: Expired Timestamp ==========
echo "\n══════════════════════════════════════════════════════════\n";
echo "  Test 3: Expired Timestamp (Should Fail)\n";
echo "══════════════════════════════════════════════════════════\n\n";

$oldTimestamp = time() - 400; // 6+ minutes ago
$oldSignedPayload = "{$oldTimestamp}.{$payload}";
$oldSignature = hash_hmac('sha256', $oldSignedPayload, $webhookSecret);
$expiredHeader = "t={$oldTimestamp},v1={$oldSignature}";

try {
    Webhooks::constructEvent($payload, $expiredHeader, $webhookSecret, 300);
    echo "  ✗ Should have failed but didn't!\n";
} catch (SignatureVerificationException $e) {
    echo "  ✓ Correctly rejected expired timestamp!\n";
    echo "  Error: {$e->getMessage()}\n";
}

// ========== Test 4: Event Type Handling ==========
echo "\n══════════════════════════════════════════════════════════\n";
echo "  Test 4: Event Type Handling\n";
echo "══════════════════════════════════════════════════════════\n\n";

$eventTypes = [
    'payment.created' => 'Payment was created',
    'payment.completed' => 'Payment was completed successfully',
    'payment.failed' => 'Payment failed',
    'refund.created' => 'Refund was created',
    'refund.completed' => 'Refund was completed',
    'customer.created' => 'Customer was created',
    'customer.updated' => 'Customer was updated',
];

foreach ($eventTypes as $type => $description) {
    echo "  • {$type}\n";
    echo "    Handler: {$description}\n";
}

// ========== Demo: Webhook Handler Pattern ==========
echo "\n══════════════════════════════════════════════════════════\n";
echo "  Demo: Webhook Handler Pattern\n";
echo "══════════════════════════════════════════════════════════\n\n";

echo "Sample webhook handler code:\n\n";
echo <<<'CODE'
  // In your webhook endpoint:
  $payload = file_get_contents('php://input');
  $signature = $_SERVER['HTTP_FYBER_SIGNATURE'] ?? '';

  try {
      $event = Webhooks::constructEvent(
          $payload,
          $signature,
          $webhookSecret
      );

      switch ($event['type']) {
          case 'payment.completed':
              // Fulfill the order
              break;
          case 'payment.failed':
              // Notify the customer
              break;
          case 'refund.completed':
              // Update inventory
              break;
      }

      http_response_code(200);
  } catch (SignatureVerificationException $e) {
      http_response_code(400);
  }
CODE;

echo "\n\n╔═══════════════════════════════════════════════════════════╗\n";
echo "║            Example completed successfully!                ║\n";
echo "╚═══════════════════════════════════════════════════════════╝\n";
