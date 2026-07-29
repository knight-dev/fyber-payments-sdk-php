<?php

namespace Fyber\Resources;

use Fyber\Http\HttpClient;

/**
 * Subscriptions resource for managing recurring billing
 */
class Subscriptions
{
    private HttpClient $http;

    public function __construct(HttpClient $http)
    {
        $this->http = $http;
    }

    /**
     * Create a new subscription
     *
     * @param array $params Subscription parameters:
     *   - customerId: string (required) Customer ID
     *   - tokenId: string (required) Saved card token ID
     *   - priceId: string (optional) Price ID if using catalog
     *   - amount: int (optional) Amount per billing period in cents
     *   - currency: string (optional, default: JMD)
     *   - interval: string (optional) day, week, month, or year
     *   - intervalCount: int (optional, default: 1)
     *   - trialDays: int (optional) Number of trial days
     *   - description: string (optional)
     *   - metadata: array (optional)
     * @return array Subscription object
     */
    public function create(array $params): array
    {
        $response = $this->http->post('/subscriptions', $params);
        return $response['data'];
    }

    /**
     * Retrieve a subscription by ID
     *
     * @param string $id Subscription ID
     * @return array Subscription object
     */
    public function get(string $id): array
    {
        $response = $this->http->get("/subscriptions/{$id}");
        return $response['data'];
    }

    /**
     * List subscriptions
     *
     * @param array|null $params List parameters:
     *   - limit: int (optional, default: 20)
     *   - customerId: string (optional) Filter by customer
     *   - status: string (optional) Filter by status
     * @return array Paginated list of subscriptions
     */
    public function list(?array $params = null): array
    {
        $response = $this->http->get('/subscriptions', $params);
        return $response['data'];
    }

    /**
     * List subscriptions for a specific customer
     *
     * @param string $customerId Customer ID
     * @return array List of subscriptions
     */
    public function listByCustomer(string $customerId): array
    {
        $response = $this->http->get("/subscriptions/customer/{$customerId}");
        return $response['data'];
    }

    /**
     * Cancel a subscription
     *
     * @param string $id Subscription ID
     * @param array|null $params Cancellation parameters:
     *   - cancelAtPeriodEnd: bool (optional, default: true)
     *   - reason: string (optional)
     * @return array Canceled subscription object
     */
    public function cancel(string $id, ?array $params = null): array
    {
        $response = $this->http->post("/subscriptions/{$id}/cancel", $params ?? []);
        return $response['data'];
    }

    /**
     * Pause a subscription
     *
     * @param string $id Subscription ID
     * @return array Paused subscription object
     */
    public function pause(string $id): array
    {
        $response = $this->http->post("/subscriptions/{$id}/pause", []);
        return $response['data'];
    }

    /**
     * Resume a paused subscription
     *
     * @param string $id Subscription ID
     * @return array Resumed subscription object
     */
    public function resume(string $id): array
    {
        $response = $this->http->post("/subscriptions/{$id}/resume", []);
        return $response['data'];
    }

    /**
     * Get subscription statistics
     *
     * @return array Subscription stats including MRR
     */
    public function stats(): array
    {
        $response = $this->http->get('/subscriptions/stats');
        return $response['data'];
    }
}
