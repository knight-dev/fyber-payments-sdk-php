<?php

namespace Fyber\Resources;

use Fyber\Http\HttpClient;

/**
 * Installments resource for managing BNPL installment plans
 */
class Installments
{
    private HttpClient $http;

    public function __construct(HttpClient $http)
    {
        $this->http = $http;
    }

    /**
     * Check BNPL eligibility for a customer
     *
     * @param array $params Eligibility check parameters:
     *   - customerId: string (required) Customer ID
     *   - amount: int (required) Amount in cents
     * @return array Eligibility result
     */
    public function checkEligibility(array $params): array
    {
        $response = $this->http->post('/installment-plans/check-eligibility', $params);
        return $response['data'];
    }

    /**
     * Create a new installment plan
     *
     * @param array $params Plan parameters:
     *   - customerId: string (required) Customer ID
     *   - tokenId: string (required) Saved card token ID
     *   - totalAmount: int (required) Total amount in cents
     *   - currency: string (optional, default: JMD)
     *   - installmentCount: int (required) Number of installments
     *   - frequency: string (optional) weekly, biweekly, or monthly
     *   - orderReference: string (optional)
     *   - description: string (optional)
     * @return array Installment plan object
     */
    public function create(array $params): array
    {
        $response = $this->http->post('/installment-plans', $params);
        return $response['data'];
    }

    /**
     * Retrieve an installment plan by ID
     *
     * @param string $id Plan ID
     * @return array Installment plan object
     */
    public function get(string $id): array
    {
        $response = $this->http->get("/installment-plans/{$id}");
        return $response['data'];
    }

    /**
     * List installment plans
     *
     * @param array|null $params List parameters:
     *   - limit: int (optional, default: 20)
     *   - customerId: string (optional) Filter by customer
     *   - status: string (optional) Filter by status
     * @return array Paginated list of plans
     */
    public function list(?array $params = null): array
    {
        $response = $this->http->get('/installment-plans', $params);
        return $response['data'];
    }

    /**
     * List installment plans for a specific customer
     *
     * @param string $customerId Customer ID
     * @return array List of plans
     */
    public function listByCustomer(string $customerId): array
    {
        $response = $this->http->get("/installment-plans/customer/{$customerId}");
        return $response['data'];
    }

    /**
     * Cancel an installment plan
     *
     * @param string $id Plan ID
     * @return array Canceled plan object
     */
    public function cancel(string $id): array
    {
        $response = $this->http->post("/installment-plans/{$id}/cancel", []);
        return $response['data'];
    }

    /**
     * Get installment plan statistics
     *
     * @return array Plan stats
     */
    public function stats(): array
    {
        $response = $this->http->get('/installment-plans/stats');
        return $response['data'];
    }
}
