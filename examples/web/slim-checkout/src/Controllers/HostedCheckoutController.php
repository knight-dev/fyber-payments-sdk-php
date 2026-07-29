<?php

declare(strict_types=1);

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;
use Fyber\Fyber;
use Fyber\Exceptions\FyberException;

class HostedCheckoutController
{
    public function __construct(
        private Twig $view,
        private Fyber $fyber
    ) {}

    public function showForm(Request $request, Response $response): Response
    {
        return $this->view->render($response, 'hosted-checkout.twig', [
            'title' => 'Hosted Checkout',
            'defaultAmount' => 50.00,
            'defaultName' => 'Test Customer',
            'defaultEmail' => 'test@example.com',
        ]);
    }

    public function createSession(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();

        try {
            $uri = $request->getUri();
            $baseUrl = $uri->getScheme() . '://' . $uri->getHost();
            if ($uri->getPort() && $uri->getPort() !== 80 && $uri->getPort() !== 443) {
                $baseUrl .= ':' . $uri->getPort();
            }

            $amount = (int) round((float) ($data['amount'] ?? 50) * 100);

            $session = $this->fyber->checkout->sessions->create([
                'mode' => 'payment',
                'intent' => 'sale',
                'amount' => $amount,
                'currency' => 'JMD',
                'successUrl' => $baseUrl . '/hosted-checkout/return?session_id={SESSION_ID}',
                'cancelUrl' => $baseUrl . '/hosted-checkout',
                'lineItems' => [
                    [
                        'name' => $data['description'] ?? 'Demo Payment',
                        'quantity' => 1,
                        'unitAmount' => $amount,
                    ],
                ],
                'customerEmail' => $data['customer_email'] ?? null,
                'customerName' => $data['customer_name'] ?? null,
                'metadata' => [
                    'source' => 'slim_checkout_example',
                    'demo' => 'hosted_checkout',
                ],
            ]);

            return $response
                ->withHeader('Location', $session['url'])
                ->withStatus(302);

        } catch (FyberException $e) {
            return $this->view->render($response->withStatus(400), 'hosted-checkout.twig', [
                'title' => 'Hosted Checkout',
                'error' => $e->getMessage(),
                'defaultAmount' => (float) ($data['amount'] ?? 50),
                'defaultName' => $data['customer_name'] ?? 'Test Customer',
                'defaultEmail' => $data['customer_email'] ?? 'test@example.com',
                'data' => $data,
            ]);
        }
    }

    public function handleReturn(Request $request, Response $response): Response
    {
        $params = $request->getQueryParams();
        $sessionId = $params['session_id'] ?? null;

        if (!$sessionId) {
            return $this->view->render($response->withStatus(400), 'error.twig', [
                'title' => 'Error',
                'message' => 'No session ID provided',
            ]);
        }

        try {
            $session = $this->fyber->checkout->sessions->getBySessionId($sessionId);

            return $this->view->render($response, 'hosted-checkout-return.twig', [
                'title' => $session['status'] === 'complete' ? 'Payment Successful' : 'Payment Status',
                'session' => $session,
                'success' => $session['status'] === 'complete',
            ]);

        } catch (FyberException $e) {
            return $this->view->render($response->withStatus(400), 'error.twig', [
                'title' => 'Error',
                'message' => $e->getMessage(),
            ]);
        }
    }
}
