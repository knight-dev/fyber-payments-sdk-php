<?php

declare(strict_types=1);

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;
use Fyber\Fyber;
use Fyber\Exceptions\FyberException;
use App\Services\TestCardService;

class CheckoutController
{
    public function __construct(
        private Twig $view,
        private Fyber $fyber,
        private TestCardService $testCardService
    ) {}

    public function showForm(Request $request, Response $response): Response
    {
        $testCardData = $this->testCardService->getTestCards();

        return $this->view->render($response, 'checkout.twig', [
            'title' => 'Direct Checkout',
            'intents' => [
                'sale' => 'Sale (Immediate Capture)',
                'authorize' => 'Authorize Only',
                'verify' => 'Verify Card',
            ],
            'testCards' => $testCardData['cards'],
            'cvvHint' => $testCardData['cvvHint'],
            'expiryHint' => $testCardData['expiryHint'],
        ]);
    }

    public function processPayment(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();

        try {
            // Validate required fields
            $required = ['card_number', 'exp_month', 'exp_year', 'cvv', 'name', 'email', 'amount'];
            foreach ($required as $field) {
                if (empty($data[$field])) {
                    throw new \InvalidArgumentException("Missing required field: {$field}");
                }
            }

            $intent = $data['intent'] ?? 'sale';
            $amount = (int) round((float) $data['amount'] * 100);

            // Create payment
            $paymentData = [
                'amount' => $intent === 'verify' ? 0 : $amount,
                'currency' => 'JMD',
                'intent' => $intent,
                'source' => [
                    'type' => 'card',
                    'number' => preg_replace('/\s+/', '', $data['card_number']),
                    'expMonth' => (int) $data['exp_month'],
                    'expYear' => (int) $data['exp_year'],
                    'cvv' => $data['cvv'],
                    'name' => $data['name'],
                ],
                'customer' => [
                    'email' => $data['email'],
                    'name' => $data['name'],
                ],
                'description' => $data['description'] ?? 'Web checkout payment',
            ];

            if ($intent === 'authorize') {
                $paymentData['capture'] = false;
            }

            $payment = $this->fyber->payments->create($paymentData);

            // Handle 3DS if required
            if (($payment['status'] ?? '') === 'requires_action') {
                return $this->view->render($response, '3ds.twig', [
                    'title' => '3D Secure Verification',
                    'payment' => $payment,
                    'redirectUrl' => $payment['threeDsResponse']['redirectUrl'] ?? null,
                ]);
            }

            // Redirect to success page
            return $response
                ->withHeader('Location', '/checkout/success?id=' . $payment['id'])
                ->withStatus(302);

        } catch (FyberException $e) {
            return $response
                ->withHeader('Location', '/checkout/failure?error=' . urlencode($e->getMessage()))
                ->withStatus(302);
        } catch (\InvalidArgumentException $e) {
            $testCardData = $this->testCardService->getTestCards();
            return $this->view->render($response->withStatus(400), 'checkout.twig', [
                'title' => 'Direct Checkout',
                'error' => $e->getMessage(),
                'intents' => [
                    'sale' => 'Sale (Immediate Capture)',
                    'authorize' => 'Authorize Only',
                    'verify' => 'Verify Card',
                ],
                'testCards' => $testCardData['cards'],
                'cvvHint' => $testCardData['cvvHint'],
                'expiryHint' => $testCardData['expiryHint'],
                'data' => $data,
            ]);
        }
    }

    public function success(Request $request, Response $response): Response
    {
        $params = $request->getQueryParams();
        $paymentId = $params['id'] ?? null;

        $payment = null;
        if ($paymentId) {
            try {
                $payment = $this->fyber->payments->get($paymentId);
            } catch (\Exception $e) {
                // Ignore errors fetching payment details
            }
        }

        return $this->view->render($response, 'success.twig', [
            'title' => 'Payment Successful',
            'payment' => $payment,
        ]);
    }

    public function failure(Request $request, Response $response): Response
    {
        $params = $request->getQueryParams();

        return $this->view->render($response, 'failure.twig', [
            'title' => 'Payment Failed',
            'error' => $params['error'] ?? 'An unknown error occurred',
        ]);
    }
}
