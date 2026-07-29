<?php

namespace Fyber\Resources;

use Fyber\Http\HttpClient;

/**
 * Checkout Sessions resource
 *
 * Use checkout sessions to redirect customers to a Fyber-hosted checkout page.
 * This handles all payment complexity including 3DS authentication.
 *
 * @example
 * // Create a checkout session
 * $session = $fyber->checkout->sessions->create([
 *     'amount' => 5000, // $50.00 in cents
 *     'currency' => 'JMD',
 *     'successUrl' => 'https://example.com/success?session_id={SESSION_ID}',
 *     'cancelUrl' => 'https://example.com/cancel',
 *     'lineItems' => [
 *         ['name' => 'Pro Plan', 'quantity' => 1, 'unitAmount' => 5000]
 *     ]
 * ]);
 *
 * // Redirect customer to checkout
 * header('Location: ' . $session['url']);
 */
class CheckoutSessions
{
    private HttpClient $http;

    public function __construct(HttpClient $http)
    {
        $this->http = $http;
    }

    /**
     * Create a new checkout session
     *
     * @param array $params Session configuration:
     *   - mode: string (optional) 'payment' | 'setup' | 'subscription' (default: 'payment')
     *   - intent: string (optional) 'sale' | 'authorize' | 'verify' (default: 'sale')
     *   - amount: int (required for payment mode) Amount in smallest currency unit (cents)
     *   - currency: string (optional) Currency code (default: 'JMD')
     *   - lineItems: array (optional) Line items for display in checkout
     *   - customerId: string (optional) Existing customer ID
     *   - customerEmail: string (optional) Customer email for guest checkout
     *   - customerName: string (optional) Customer name
     *   - customerPhone: string (optional) Customer phone
     *   - subscriptionId: string (optional) Link to subscription
     *   - installmentPlanId: string (optional) Link to installment plan
     *   - successUrl: string (required) URL for successful payment
     *   - cancelUrl: string (required) URL if customer cancels
     *   - themeId: string (optional) Theme ID to apply
     *   - themeOverrides: array (optional) Inline theme overrides
     *   - metadata: array (optional) Custom metadata
     *   - expiresInMinutes: int (optional) Session expiration (default: 30)
     * @return array The created checkout session with URL for redirect
     */
    public function create(array $params): array
    {
        $response = $this->http->post('/checkout/sessions', $params);
        return $response['data'];
    }

    /**
     * Retrieve a checkout session by ID
     *
     * @param string $id The session's internal ID (UUID)
     * @return array The checkout session
     */
    public function get(string $id): array
    {
        $response = $this->http->get("/checkout/sessions/{$id}");
        return $response['data'];
    }

    /**
     * Retrieve a checkout session by session ID (cs_xxx format)
     *
     * @param string $sessionId The public session ID (e.g., cs_test_xxx)
     * @return array The checkout session
     */
    public function getBySessionId(string $sessionId): array
    {
        $response = $this->http->get("/checkout/sessions/by-session-id/{$sessionId}");
        return $response['data'];
    }

    /**
     * List checkout sessions
     *
     * @param array|null $params Filtering and pagination options:
     *   - status: string (optional) Filter by status ('open' | 'complete' | 'expired')
     *   - customerId: string (optional) Filter by customer
     *   - dateFrom: string (optional) Filter by date range start
     *   - dateTo: string (optional) Filter by date range end
     *   - limit: int (optional) Number of results per page
     *   - page: int (optional) Page number
     * @return array Paginated list of checkout sessions
     */
    public function list(?array $params = null): array
    {
        $response = $this->http->get('/checkout/sessions', $params);
        return $response['data'];
    }

    /**
     * Expire a checkout session
     *
     * Manually expires an open session. Cannot expire sessions that are already complete or expired.
     *
     * @param string $id The session's internal ID (UUID)
     * @return array The expired checkout session
     */
    public function expire(string $id): array
    {
        $response = $this->http->post("/checkout/sessions/{$id}/expire");
        return $response['data'];
    }
}

/**
 * Checkout resource - wrapper around sessions
 */
class Checkout
{
    /**
     * Checkout session operations
     */
    public readonly CheckoutSessions $sessions;

    public function __construct(HttpClient $http)
    {
        $this->sessions = new CheckoutSessions($http);
    }
}
