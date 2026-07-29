# Fyber PHP SDK - Slim Checkout Example

A complete checkout flow example using Slim Framework 4 and the Fyber PHP SDK.

## Features

- Payment checkout with multiple intents (sale, authorize, verify)
- 3D Secure authentication support
- Customer management (CRUD operations)
- Webhook endpoint with signature verification
- Webhook event logging

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
FYBER_WEBHOOK_SECRET=whsec_your_secret
APP_ENV=development
APP_DEBUG=true
```

## Running the Application

### Development Server
```bash
composer start
# or
php -S localhost:8080 -t public
```

### Docker
```bash
docker build -t fyber-slim-checkout .
docker run -p 8080:80 --env-file .env fyber-slim-checkout
```

## Routes

| Method | Path | Description |
|--------|------|-------------|
| GET | `/` | Home page |
| GET | `/checkout` | Checkout form |
| POST | `/checkout` | Process payment |
| GET | `/checkout/success` | Success page |
| GET | `/checkout/failure` | Failure page |
| POST | `/webhooks` | Webhook endpoint |
| GET | `/webhooks` | View webhook events |
| GET | `/customers` | List customers |
| GET | `/customers/new` | New customer form |
| POST | `/customers` | Create customer |
| GET | `/customers/{id}` | View customer |
| POST | `/customers/{id}` | Update customer |
| POST | `/customers/{id}/delete` | Delete customer |

## Webhook Configuration

Configure your Fyber dashboard to send webhooks to:
```
POST https://your-domain.com/webhooks
```

For local development, use ngrok:
```bash
ngrok http 8080
```

## Test Cards

| Card Number | Result |
|-------------|--------|
| 4242 4242 4242 4242 | Success |
| 4000 0000 0000 0002 | Decline |
| 4000 0027 6000 3184 | 3DS Required |

## Project Structure

```
slim-checkout/
├── public/
│   ├── index.php       # Entry point
│   └── .htaccess       # Apache rewrite rules
├── src/
│   ├── Controllers/
│   │   ├── HomeController.php
│   │   ├── CheckoutController.php
│   │   ├── WebhooksController.php
│   │   └── CustomersController.php
│   ├── routes.php
│   └── storage/        # Webhook logs (auto-created)
├── templates/
│   ├── layout.twig
│   ├── home.twig
│   ├── checkout.twig
│   ├── success.twig
│   ├── failure.twig
│   ├── 3ds.twig
│   ├── webhooks.twig
│   ├── error.twig
│   └── customers/
│       ├── index.twig
│       ├── form.twig
│       └── show.twig
├── composer.json
├── .env.example
├── Dockerfile
└── README.md
```

## Environment Configurations

### Local Development
```bash
FYBER_API_KEY=sk_test_...
FYBER_ENVIRONMENT=test
FYBER_BASE_URL=http://localhost:5000
APP_DEBUG=true
```

### Production
```bash
FYBER_API_KEY=sk_live_...
FYBER_ENVIRONMENT=live
FYBER_BASE_URL=https://api.fyber.one
APP_DEBUG=false
```
