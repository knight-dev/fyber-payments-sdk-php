<?php

declare(strict_types=1);

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;
use Fyber\Webhooks;
use Fyber\Exceptions\SignatureVerificationException;

class WebhooksController
{
    private const LOG_FILE = __DIR__ . '/../../storage/webhooks.json';

    public function __construct(
        private Twig $view
    ) {}

    public function handle(Request $request, Response $response): Response
    {
        $payload = (string) $request->getBody();
        $signature = $request->getHeaderLine('Fyber-Signature');
        $webhookSecret = $_ENV['FYBER_WEBHOOK_SECRET'] ?? '';

        try {
            // Verify signature
            $event = Webhooks::constructEvent($payload, $signature, $webhookSecret);

            // Log the event
            $this->logEvent($event);

            // Handle specific event types
            $this->handleEvent($event);

            $response->getBody()->write(json_encode(['received' => true]));
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(200);

        } catch (SignatureVerificationException $e) {
            $this->logEvent([
                'type' => 'signature_verification_failed',
                'error' => $e->getMessage(),
                'timestamp' => time(),
            ]);

            $response->getBody()->write(json_encode(['error' => 'Invalid signature']));
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(400);
        }
    }

    private function handleEvent(array $event): void
    {
        $type = $event['type'] ?? 'unknown';

        switch ($type) {
            case 'payment.completed':
                // Fulfill the order
                error_log("Payment completed: " . ($event['data']['object']['id'] ?? 'unknown'));
                break;

            case 'payment.failed':
                // Notify the customer
                error_log("Payment failed: " . ($event['data']['object']['id'] ?? 'unknown'));
                break;

            case 'refund.completed':
                // Update inventory
                error_log("Refund completed: " . ($event['data']['object']['id'] ?? 'unknown'));
                break;

            case 'customer.created':
                // Welcome email
                error_log("Customer created: " . ($event['data']['object']['id'] ?? 'unknown'));
                break;

            default:
                error_log("Unhandled event type: {$type}");
        }
    }

    private function logEvent(array $event): void
    {
        $storageDir = dirname(self::LOG_FILE);
        if (!is_dir($storageDir)) {
            mkdir($storageDir, 0755, true);
        }

        $events = [];
        if (file_exists(self::LOG_FILE)) {
            $content = file_get_contents(self::LOG_FILE);
            $events = json_decode($content, true) ?? [];
        }

        // Add timestamp if not present
        if (!isset($event['received_at'])) {
            $event['received_at'] = date('Y-m-d H:i:s');
        }

        // Prepend new event (most recent first)
        array_unshift($events, $event);

        // Keep only last 100 events
        $events = array_slice($events, 0, 100);

        file_put_contents(self::LOG_FILE, json_encode($events, JSON_PRETTY_PRINT));
    }

    public function showLog(Request $request, Response $response): Response
    {
        $events = [];
        if (file_exists(self::LOG_FILE)) {
            $content = file_get_contents(self::LOG_FILE);
            $events = json_decode($content, true) ?? [];
        }

        return $this->view->render($response, 'webhooks.twig', [
            'title' => 'Webhook Events',
            'events' => $events,
        ]);
    }
}
