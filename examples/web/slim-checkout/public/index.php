<?php

declare(strict_types=1);

use DI\Container;
use Slim\Factory\AppFactory;
use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;
use Dotenv\Dotenv;
use Fyber\Fyber;
use App\Services\TestCardService;

require __DIR__ . '/../vendor/autoload.php';

// Load environment variables
$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->safeLoad();

// Create Container
$container = new Container();

// Configure Fyber client
$container->set(Fyber::class, function () {
    return new Fyber($_ENV['FYBER_API_KEY'], [
        'environment' => $_ENV['FYBER_ENVIRONMENT'] ?? 'test',
        'baseUrl' => $_ENV['FYBER_BASE_URL'] ?: null,
    ]);
});

// Configure TestCardService
$container->set(TestCardService::class, function () {
    $baseUrl = $_ENV['FYBER_BASE_URL'] ?? 'http://localhost:5000';
    $apiKey = $_ENV['FYBER_API_KEY'] ?? '';
    return new TestCardService($baseUrl, $apiKey);
});

// Configure Twig
$container->set(Twig::class, function () {
    return Twig::create(__DIR__ . '/../templates', [
        'cache' => false, // Set to a path in production
        'auto_reload' => true,
    ]);
});

// Create App
AppFactory::setContainer($container);
$app = AppFactory::create();

// Add Twig middleware
$app->add(TwigMiddleware::createFromContainer($app, Twig::class));

// Add error middleware
$app->addErrorMiddleware(
    $_ENV['APP_DEBUG'] === 'true',
    true,
    true
);

// Add body parsing middleware
$app->addBodyParsingMiddleware();

// Routes
require __DIR__ . '/../src/routes.php';

$app->run();
