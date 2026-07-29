<?php

namespace Fyber\Resources;

use Fyber\Http\HttpClient;

class Customers
{
    private HttpClient $http;

    public function __construct(HttpClient $http)
    {
        $this->http = $http;
    }

    /**
     * Create a new customer
     *
     * @param array $params Customer parameters:
     *   - email: string (required)
     *   - name: string (optional)
     *   - phone: string (optional)
     *   - address: array (optional)
     *     - line1: string
     *     - line2: string (optional)
     *     - city: string
     *     - state: string (optional)
     *     - postalCode: string
     *     - country: string
     *   - metadata: array (optional)
     * @return array Customer object
     */
    public function create(array $params): array
    {
        $response = $this->http->post('/customers', $params);
        return $response['data'];
    }

    /**
     * Retrieve a customer by ID
     *
     * @param string $id Customer ID
     * @return array Customer object
     */
    public function get(string $id): array
    {
        $response = $this->http->get("/customers/{$id}");
        return $response['data'];
    }

    /**
     * Update a customer
     *
     * @param string $id Customer ID
     * @param array $params Update parameters (all optional):
     *   - email: string
     *   - name: string
     *   - phone: string
     *   - address: array
     *   - metadata: array
     * @return array Updated customer object
     */
    public function update(string $id, array $params): array
    {
        $response = $this->http->put("/customers/{$id}", $params);
        return $response['data'];
    }

    /**
     * Delete a customer
     *
     * @param string $id Customer ID
     * @return void
     */
    public function delete(string $id): void
    {
        $this->http->delete("/customers/{$id}");
    }

    /**
     * List all customers
     *
     * @param array|null $params List parameters:
     *   - limit: int (optional, default: 20)
     *   - startingAfter: string (optional) Cursor for pagination
     *   - endingBefore: string (optional) Cursor for pagination
     *   - email: string (optional) Filter by email
     * @return array Paginated list of customers
     */
    public function list(?array $params = null): array
    {
        $response = $this->http->get('/customers', $params);
        return $response['data'];
    }
}
