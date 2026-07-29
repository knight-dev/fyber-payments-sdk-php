<?php

declare(strict_types=1);

use Slim\App;
use App\Controllers\HomeController;
use App\Controllers\CheckoutController;
use App\Controllers\HostedCheckoutController;
use App\Controllers\WebhooksController;
use App\Controllers\CustomersController;

/** @var App $app */

// Home
$app->get('/', HomeController::class . ':index');

// Direct Checkout routes
$app->get('/checkout', CheckoutController::class . ':showForm');
$app->post('/checkout', CheckoutController::class . ':processPayment');
$app->get('/checkout/success', CheckoutController::class . ':success');
$app->get('/checkout/failure', CheckoutController::class . ':failure');

// Hosted Checkout routes
$app->get('/hosted-checkout', HostedCheckoutController::class . ':showForm');
$app->post('/hosted-checkout', HostedCheckoutController::class . ':createSession');
$app->get('/hosted-checkout/return', HostedCheckoutController::class . ':handleReturn');

// Webhooks
$app->post('/webhooks', WebhooksController::class . ':handle');
$app->get('/webhooks', WebhooksController::class . ':showLog');

// Customers
$app->get('/customers', CustomersController::class . ':index');
$app->get('/customers/new', CustomersController::class . ':showForm');
$app->post('/customers', CustomersController::class . ':create');
$app->get('/customers/{id}', CustomersController::class . ':show');
$app->post('/customers/{id}', CustomersController::class . ':update');
$app->post('/customers/{id}/delete', CustomersController::class . ':delete');
