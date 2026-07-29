# Fyber PHP SDK

Official PHP SDK for the Fyber Payment API.

## Requirements

- PHP 8.1 or higher
- Composer

## Installation

```bash
composer require fyber/sdk-php
```

## Quick Start

```php
<?php

require 'vendor/autoload.php';

use Fyber\Fyber;

// Initialize the client
$fyber = new Fyber('sk_test_your_api_key', [
    'environment' => 'test', // or 'live' for production
]);

// Create a checkout session (recommended)
$session = $fyber->checkout->sessions->create([
    'mode' => 'payment',
    'amount' => 5000, // $50.00 in cents
    'currency' => 'JMD',
    'successUrl' => 'https://yoursite.com/success?session_id={SESSION_ID}',
    'cancelUrl' => 'https://yoursite.com/cancel',
    'lineItems' => [
        ['name' => 'Pro Plan', 'quantity' => 1, 'unitAmount' => 5000]
    ],
    'customerEmail' => 'customer@example.com',
]);

// Redirect customer to hosted checkout
header('Location: ' . $session['url']);
exit;
```

## Hosted Checkout (Recommended)

The easiest way to accept payments. Redirect customers to a Fyber-hosted checkout page that handles card input, validation, and 3DS authentication.

```php
// Create a checkout session
$session = $fyber->checkout->sessions->create([
    'mode' => 'payment',
    'amount' => 5000, // $50.00 in cents
    'currency' => 'JMD',
    'successUrl' => 'https://yoursite.com/success?session_id={SESSION_ID}',
    'cancelUrl' => 'https://yoursite.com/cancel',
    'lineItems' => [
        ['name' => 'Pro Plan', 'quantity' => 1, 'unitAmount' => 5000]
    ],
    'customerEmail' => 'customer@example.com',
    'metadata' => [
        'orderId' => '1234'
    ]
]);

// Redirect customer to checkout
header('Location: ' . $session['url']);
exit;
```

After payment, the customer is redirected to your success URL. Use webhooks to confirm payment:

```php
// Retrieve the session to verify payment
$session = $fyber->checkout->sessions->getBySessionId('cs_test_...');

if ($session['status'] === 'complete') {
    // Payment successful, fulfill the order
    echo "Payment ID: " . $session['paymentId'] . "\n";
}
```

**Checkout modes:**
- `payment` - One-time payment (default)
- `setup` - Save card for future use without charging
- `subscription` - Start a recurring subscription

**Payment intents:**
- `sale` - Charge immediately (default)
- `authorize` - Authorize only, capture later
- `verify` - Validate card with $0 auth

```php
// Expire a session manually
$fyber->checkout->sessions->expire($session['id']);

// List all sessions
$sessions = $fyber->checkout->sessions->list([
    'status' => 'complete',
    'limit' => 20
]);
```

## Payments

### Create a Payment

```php
$payment = $fyber->payments->create([
    'amount' => 5000,
    'currency' => 'USD',
    'source' => [
        'type' => 'card',
        'number' => '4111111111111111',
        'expMonth' => 12,
        'expYear' => 2025,
        'cvv' => '123',
    ],
    'customer' => [
        'email' => 'customer@example.com',
        'name' => 'John Doe',
    ],
    'capture' => true, // Capture immediately (default)
    'metadata' => [
        'orderId' => '12345',
    ],
]);
```

### Authorize and Capture Separately

```php
// Authorize only
$payment = $fyber->payments->create([
    'amount' => 5000,
    'currency' => 'USD',
    'source' => [...],
    'capture' => false,
]);

// Capture later
$captured = $fyber->payments->capture($payment['id']);

// Or capture a partial amount
$captured = $fyber->payments->capture($payment['id'], [
    'amount' => 3000, // Capture $30 of the $50 authorized
]);
```

### Retrieve a Payment

```php
$payment = $fyber->payments->get('pay_abc123');
```

### List Payments

```php
$payments = $fyber->payments->list([
    'limit' => 10,
    'status' => 'succeeded',
]);

foreach ($payments['data'] as $payment) {
    echo $payment['id'] . ': ' . $payment['amount'] . "\n";
}
```

### Cancel a Payment

```php
$fyber->payments->cancel('pay_abc123');
```

## Refunds

### Create a Refund

```php
// Full refund
$refund = $fyber->refunds->create([
    'paymentId' => 'pay_abc123',
]);

// Partial refund
$refund = $fyber->refunds->create([
    'paymentId' => 'pay_abc123',
    'amount' => 500, // Refund $5.00
    'reason' => 'Customer request',
]);
```

### Retrieve a Refund

```php
$refund = $fyber->refunds->get('ref_abc123');
```

### List Refunds

```php
$refunds = $fyber->refunds->list([
    'paymentId' => 'pay_abc123',
]);
```

## Customers

### Create a Customer

```php
$customer = $fyber->customers->create([
    'email' => 'customer@example.com',
    'name' => 'John Doe',
    'phone' => '+1234567890',
    'address' => [
        'line1' => '123 Main St',
        'city' => 'Kingston',
        'postalCode' => '12345',
        'country' => 'JM',
    ],
]);
```

### Update a Customer

```php
$customer = $fyber->customers->update('cus_abc123', [
    'name' => 'Jane Doe',
    'phone' => '+0987654321',
]);
```

### Delete a Customer

```php
$fyber->customers->delete('cus_abc123');
```

### List Customers

```php
$customers = $fyber->customers->list([
    'limit' => 20,
    'email' => 'customer@example.com',
]);
```

## Tokens (Saved Cards)

Save cards for future payments using hosted checkout with `mode: 'setup'`:

```php
// Create a setup session to save a card
$session = $fyber->checkout->sessions->create([
    'mode' => 'setup',
    'customerId' => 'cus_123',
    'successUrl' => 'https://yoursite.com/card-saved?session_id={SESSION_ID}',
    'cancelUrl' => 'https://yoursite.com/cancel',
]);

// After checkout completes, the token is available via webhook
// or by retrieving the session
$completedSession = $fyber->checkout->sessions->getBySessionId('cs_test_...');
$tokenId = $completedSession['tokenId'];
```

```php
// List customer's saved cards
$tokens = $fyber->tokens->list([
    'customerId' => 'cus_123'
]);

// Get a specific token
$token = $fyber->tokens->get('tok_123');

// Set as default payment method
$fyber->tokens->setDefault('tok_123');

// Delete a saved card
$fyber->tokens->delete('tok_123');

// Use a saved card for payment
$payment = $fyber->payments->create([
    'amount' => 5000,
    'currency' => 'JMD',
    'tokenId' => 'tok_123',
]);
```

## Subscriptions

Create recurring billing subscriptions:

```php
// First, save a card using hosted checkout (mode: 'setup')
// Then create a subscription with the token

$subscription = $fyber->subscriptions->create([
    'customerId' => 'cus_123',
    'tokenId' => 'tok_123', // From saved card
    'amount' => 999, // $9.99/month
    'currency' => 'JMD',
    'interval' => 'month', // 'day', 'week', 'month', 'year'
    'trialDays' => 14, // Optional trial period
    'metadata' => [
        'plan' => 'pro'
    ]
]);

// Get subscription
$sub = $fyber->subscriptions->get('sub_123');

// List subscriptions
$subs = $fyber->subscriptions->list([
    'customerId' => 'cus_123',
    'status' => 'active'
]);

// Pause a subscription
$fyber->subscriptions->pause('sub_123');

// Resume a paused subscription
$fyber->subscriptions->resume('sub_123');

// Cancel subscription
$fyber->subscriptions->cancel('sub_123', [
    'cancelAtPeriodEnd' => true, // Cancel at end of billing period
    'reason' => 'Customer requested'
]);

// Get subscription stats
$stats = $fyber->subscriptions->stats();
echo "Active: {$stats['activeCount']}, MRR: $" . ($stats['mrr'] / 100) . "\n";
```

**Subscription webhook events:**
- `subscription.created` - Subscription activated
- `subscription.payment_succeeded` - Recurring payment successful
- `subscription.payment_failed` - Recurring payment failed
- `subscription.trial_ending` - Trial ends in 3 days
- `subscription.canceled` - Subscription canceled

## Installments (BNPL)

Split payments into installment plans:

```php
// Check customer eligibility
$eligibility = $fyber->installments->checkEligibility([
    'customerId' => 'cus_123',
    'amount' => 50000, // $500.00
]);

if ($eligibility['eligible']) {
    echo "Available options: " . implode(', ', $eligibility['availableOptions']) . " installments\n";
    echo "Available credit: $" . ($eligibility['availableCredit'] / 100) . "\n";
}

// Create an installment plan (requires saved card)
$plan = $fyber->installments->create([
    'customerId' => 'cus_123',
    'tokenId' => 'tok_123',
    'totalAmount' => 50000, // $500.00 total
    'installmentCount' => 4, // 4 payments
    'frequency' => 'biweekly', // 'weekly', 'biweekly', 'monthly'
    'metadata' => [
        'orderId' => '1234'
    ]
]);

// Get installment plan
$plan = $fyber->installments->get('inst_123');

// List installment plans
$plans = $fyber->installments->list([
    'customerId' => 'cus_123',
    'status' => 'active'
]);

// Cancel an installment plan
$fyber->installments->cancel('inst_123');

// Get installment stats
$stats = $fyber->installments->stats();
echo "Active plans: {$stats['activeCount']}\n";
echo "Default rate: " . ($stats['defaultRate'] * 100) . "%\n";
```

**Installment webhook events:**
- `installment.plan_created` - Plan activated, first payment charged
- `installment.payment_succeeded` - Scheduled payment completed
- `installment.payment_failed` - Payment failed (may retry)
- `installment.plan_completed` - All payments completed
- `installment.plan_defaulted` - Customer defaulted

## Webhooks

### Verify Webhook Signature

```php
<?php
// In your webhook handler

$payload = file_get_contents('php://input');
$signature = $_SERVER['HTTP_FYBER_SIGNATURE'];
$secret = 'whsec_your_webhook_secret';

try {
    $event = Fyber::verifyWebhook($payload, $signature, $secret);

    // Handle the event
    switch ($event['type']) {
        case 'checkout.session.completed':
            $session = $event['data'];
            // Checkout payment successful - fulfill order
            break;
        case 'payment.succeeded':
            $payment = $event['data'];
            // Handle successful payment
            break;
        case 'payment.failed':
            $payment = $event['data'];
            // Handle failed payment
            break;
        case 'refund.created':
            $refund = $event['data'];
            // Handle refund
            break;
    }

    http_response_code(200);
} catch (Fyber\Exceptions\FyberException $e) {
    // Invalid signature
    http_response_code(400);
    echo 'Invalid signature';
}
```

## Error Handling

```php
use Fyber\Exceptions\FyberException;

try {
    $payment = $fyber->payments->create([...]);
} catch (FyberException $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Type: " . $e->getType() . "\n";
    echo "Code: " . $e->getErrorCode() . "\n";
    echo "Status: " . $e->getStatusCode() . "\n";
}
```

## Configuration

```php
$fyber = new Fyber('sk_test_xxx', [
    'environment' => 'live',        // 'test' or 'live'
    'baseUrl' => 'https://...',     // Custom API URL (optional)
    'timeout' => 60,                // Request timeout in seconds
]);
```

## License

MIT
