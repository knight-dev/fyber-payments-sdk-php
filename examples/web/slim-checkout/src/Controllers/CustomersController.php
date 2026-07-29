<?php

declare(strict_types=1);

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;
use Fyber\Fyber;
use Fyber\Exceptions\FyberException;

class CustomersController
{
    public function __construct(
        private Twig $view,
        private Fyber $fyber
    ) {}

    public function index(Request $request, Response $response): Response
    {
        try {
            $params = $request->getQueryParams();
            $customers = $this->fyber->customers->list([
                'limit' => (int) ($params['limit'] ?? 20),
            ]);

            return $this->view->render($response, 'customers/index.twig', [
                'title' => 'Customers',
                'customers' => $customers['data'] ?? [],
            ]);
        } catch (FyberException $e) {
            return $this->view->render($response, 'customers/index.twig', [
                'title' => 'Customers',
                'error' => $e->getMessage(),
                'customers' => [],
            ]);
        }
    }

    public function showForm(Request $request, Response $response): Response
    {
        return $this->view->render($response, 'customers/form.twig', [
            'title' => 'New Customer',
            'customer' => null,
        ]);
    }

    public function create(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();

        try {
            $customerData = [
                'email' => $data['email'] ?? '',
                'name' => $data['name'] ?? '',
            ];

            if (!empty($data['phone'])) {
                $customerData['phone'] = $data['phone'];
            }

            $customer = $this->fyber->customers->create($customerData);

            return $response
                ->withHeader('Location', '/customers/' . $customer['id'])
                ->withStatus(302);

        } catch (FyberException $e) {
            return $this->view->render($response->withStatus(400), 'customers/form.twig', [
                'title' => 'New Customer',
                'error' => $e->getMessage(),
                'customer' => $data,
            ]);
        }
    }

    public function show(Request $request, Response $response, array $args): Response
    {
        try {
            $customer = $this->fyber->customers->get($args['id']);

            // Try to get customer's payments
            $payments = [];
            try {
                $result = $this->fyber->payments->list([
                    'customerId' => $args['id'],
                    'limit' => 10,
                ]);
                $payments = $result['data'] ?? [];
            } catch (\Exception $e) {
                // Ignore errors fetching payments
            }

            return $this->view->render($response, 'customers/show.twig', [
                'title' => 'Customer Details',
                'customer' => $customer,
                'payments' => $payments,
            ]);

        } catch (FyberException $e) {
            return $this->view->render($response->withStatus(404), 'error.twig', [
                'title' => 'Not Found',
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function update(Request $request, Response $response, array $args): Response
    {
        $data = $request->getParsedBody();

        try {
            $updateData = [];

            if (!empty($data['name'])) {
                $updateData['name'] = $data['name'];
            }
            if (!empty($data['phone'])) {
                $updateData['phone'] = $data['phone'];
            }

            $customer = $this->fyber->customers->update($args['id'], $updateData);

            return $response
                ->withHeader('Location', '/customers/' . $customer['id'])
                ->withStatus(302);

        } catch (FyberException $e) {
            $customer = ['id' => $args['id'], ...$data];
            return $this->view->render($response->withStatus(400), 'customers/show.twig', [
                'title' => 'Customer Details',
                'error' => $e->getMessage(),
                'customer' => $customer,
            ]);
        }
    }

    public function delete(Request $request, Response $response, array $args): Response
    {
        try {
            $this->fyber->customers->delete($args['id']);

            return $response
                ->withHeader('Location', '/customers')
                ->withStatus(302);

        } catch (FyberException $e) {
            return $response
                ->withHeader('Location', '/customers/' . $args['id'] . '?error=' . urlencode($e->getMessage()))
                ->withStatus(302);
        }
    }
}
