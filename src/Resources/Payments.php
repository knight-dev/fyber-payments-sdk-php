<?php

namespace Fyber\Resources;

use Fyber\Http\HttpClient;

class Payments
{
    private HttpClient $http;

    public function __construct(HttpClient $http)
    {
        $this->http = $http;
    }

    /**
     * Create a new payment
     *
     * @param array $params Payment parameters:
     *   - amount: int (required) Amount in cents
     *   - currency: string (required) Currency code (e.g., 'USD', 'JMD')
     *   - source: array (required) Card source object
     *     - type: 'card'
     *     - number: string Card number
     *     - expMonth: int Expiration month
     *     - expYear: int Expiration year
     *     - cvv: string CVV
     *     - name: string (optional) Cardholder name
     *     - address: array (optional) Billing address
     *   - customer: array (optional) Customer info
     *   - description: string (optional)
     *   - capture: bool (optional, default: true)
     *   - metadata: array (optional)
     * @return array Payment object
     */
    public function create(array $params): array
    {
        $response = $this->http->post('/payments', $params);
        return $response['data'];
    }

    /**
     * Retrieve a payment by ID
     *
     * @param string $id Payment ID
     * @return array Payment object
     */
    public function get(string $id): array
    {
        $response = $this->http->get("/payments/{$id}");
        return $response['data'];
    }

    /**
     * List all payments
     *
     * @param array|null $params List parameters:
     *   - limit: int (optional, default: 20)
     *   - startingAfter: string (optional) Cursor for pagination
     *   - endingBefore: string (optional) Cursor for pagination
     *   - status: string (optional) Filter by status
     *   - customerId: string (optional) Filter by customer
     *   - startDate: string (optional) Filter by date range
     *   - endDate: string (optional) Filter by date range
     * @return array Paginated list of payments
     */
    public function list(?array $params = null): array
    {
        $response = $this->http->get('/payments', $params);
        return $response['data'];
    }

    /**
     * Capture an authorized payment
     *
     * @param string $id Payment ID
     * @param array|null $params Capture parameters:
     *   - amount: int (optional) Amount to capture (for partial capture)
     * @return array Payment object
     */
    public function capture(string $id, ?array $params = null): array
    {
        $response = $this->http->post("/payments/{$id}/capture", $params);
        return $response['data'];
    }

    /**
     * Cancel/void a payment
     *
     * @param string $id Payment ID
     * @return array Payment object
     */
    public function cancel(string $id): array
    {
        $response = $this->http->post("/payments/{$id}/cancel");
        return $response['data'];
    }
}
