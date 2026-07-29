# Fyber PHP SDK - CLI Examples

Command-line examples demonstrating all features of the Fyber PHP SDK.

## Prerequisites

- PHP 8.1+
- Composer
- Fyber API credentials

## Setup

1. Install dependencies:
```bash
composer install
```

2. Configure environment:
```bash
cp .env.example .env
```

3. Edit `.env` with your credentials:
```bash
FYBER_API_KEY=sk_test_your_api_key_here
FYBER_ENVIRONMENT=test
FYBER_BASE_URL=                          # Optional: override API URL
FYBER_WEBHOOK_SECRET=whsec_your_secret   # For webhook examples
```

## Running Examples

### Run a specific example:
```bash
php 01-basic-payment.php
```

### Run all examples:
```bash
php run-all.php
```

## Examples

| # | File | Intent | Description |
|---|------|--------|-------------|
| 01 | `01-basic-payment.php` | sale | Standard payment with immediate capture |
| 02 | `02-authorize-capture.php` | authorize | Pre-authorize then capture later |
| 03 | `03-verify-card.php` | verify | Validate card without charging |
| 04 | `04-setup-tokenization.php` | setup | Save card for future payments |
| 05 | `05-mit-recurring.php` | mit_recurring | Subscription/recurring billing |
| 06 | `06-mit-installment.php` | mit_installment | Buy Now Pay Later (BNPL) |
| 07 | `07-mit-unscheduled.php` | mit_unscheduled | Ad-hoc stored card charges |
| 08 | `08-customers-crud.php` | - | Customer CRUD operations |
| 09 | `09-refunds.php` | - | Full and partial refunds |
| 10 | `10-webhooks-test.php` | - | Webhook signature verification |

## Payment Intents

| Intent | Use Case |
|--------|----------|
| `sale` | Standard purchase, immediate capture |
| `authorize` | Reserve funds, capture later (hotels, rentals) |
| `verify` | Card validation, $0 auth with auto-void |
| `setup` | Save card without charging (tokenization) |
| `mit_recurring` | Subscription billing (merchant-initiated) |
| `mit_installment` | BNPL payments with fixed schedule |
| `mit_unscheduled` | Ad-hoc charges on stored cards |

## Test Cards

| Card Number | Result |
|-------------|--------|
| 4242424242424242 | Success |
| 4000000000000002 | Decline |
| 4000002760003184 | 3DS Required |

## Environment Configurations

### Local Development
```bash
FYBER_API_KEY=sk_test_...
FYBER_ENVIRONMENT=test
FYBER_BASE_URL=http://localhost:5000
```

### Staging
```bash
FYBER_API_KEY=sk_test_...
FYBER_ENVIRONMENT=test
FYBER_BASE_URL=https://staging-api.fyber.one
```

### Production
```bash
FYBER_API_KEY=sk_live_...
FYBER_ENVIRONMENT=live
FYBER_BASE_URL=https://api.fyber.one
```

## Troubleshooting

### "Class not found" errors
Make sure you've run `composer install` to install dependencies.

### API connection errors
Check your `FYBER_BASE_URL` is correct and the API is accessible.

### Authentication errors
Verify your `FYBER_API_KEY` is correct and has the necessary permissions.
