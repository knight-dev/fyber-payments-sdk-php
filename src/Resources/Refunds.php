<?php

namespace Fyber\Resources;

use Fyber\Http\HttpClient;

class Refunds
{
    private HttpClient $http;

    public function __construct(HttpClient $http)
    {
        $this->http = $http;
    }

    /**
     * Create a refund
     *
     * @param array $params Refund parameters:
     *   - paymentId: string (required) Payment ID to refund
     *   - amount: int (optional) Amount to refund in cents (for partial refund)
     *   - reason: string (optional) Reason for refund
     *   - metadata: array (optional)
     * @return array Refund object
     */
    public function create(array $params): array
    {
        $response = $this->http->post('/refunds', $params);
        return $response['data'];
    }

    /**
     * Retrieve a refund by ID
     *
     * @param string $id Refund ID
     * @return array Refund object
     */
    public function get(string $id): array
    {
        $response = $this->http->get("/refunds/{$id}");
        return $response['data'];
    }

    /**
     * List all refunds
     *
     * @param array|null $params List parameters:
     *   - limit: int (optional, default: 20)
     *   - startingAfter: string (optional) Cursor for pagination
     *   - endingBefore: string (optional) Cursor for pagination
     *   - paymentId: string (optional) Filter by payment ID
     * @return array Paginated list of refunds
     */
    public function list(?array $params = null): array
    {
        $response = $this->http->get('/refunds', $params);
        return $response['data'];
    }
}
