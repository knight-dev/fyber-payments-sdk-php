<?php

namespace Fyber\Resources;

use Fyber\Http\HttpClient;

/**
 * Operations for managing saved payment tokens
 */
class Tokens
{
    private HttpClient $http;

    public function __construct(HttpClient $http)
    {
        $this->http = $http;
    }

    /**
     * Create a new token (tokenize a card)
     *
     * @param array $params Token parameters:
     *   - customerId: string (optional) Customer ID to associate the token with
     *   - card: array (required)
     *     - number: string Full card number
     *     - cvv: string (optional) CVV/CVC code
     *     - expMonth: string Expiration month (1-12)
     *     - expYear: string Expiration year (2 or 4 digits)
     *     - holderName: string (optional) Cardholder name
     *   - billingAddress: array (optional)
     *     - line1: string
     *     - line2: string (optional)
     *     - city: string
     *     - state: string (optional)
     *     - postalCode: string
     *     - country: string
     *   - connector: string (optional) Payment gateway connector
     *   - purpose: string (optional) card_on_file, recurring, or installment
     *   - verifyCard: bool (optional, default: true) Perform $0 authorization verification
     *   - autoVoidVerification: bool (optional, default: true) Auto-void the verification auth
     *   - verifyAmount: float (optional, default: 0.00) Amount for verification (some issuers need $1)
     *   - verifyCurrency: string (optional, default: "USD") Currency for verification
     *   - setAsDefault: bool (optional) Set as default payment method
     * @return array Token object
     */
    public function create(array $params): array
    {
        $response = $this->http->post('/tokens', $params);
        return $response['data'];
    }

    /**
     * Retrieve a token by ID
     *
     * @param string $id Token ID
     * @return array Token object
     */
    public function get(string $id): array
    {
        $response = $this->http->get("/tokens/{$id}");
        return $response['data'];
    }

    /**
     * List all tokens
     *
     * @param array|null $params List parameters:
     *   - customerId: string (optional) Filter by customer
     *   - type: string (optional) Filter by type
     *   - cardBrand: string (optional) Filter by card brand
     *   - page: int (optional) Page number
     *   - limit: int (optional) Items per page
     * @return array Paginated list of tokens
     */
    public function list(?array $params = null): array
    {
        $response = $this->http->get('/tokens', $params);
        return $response['data'];
    }

    /**
     * Get tokens for a specific customer
     *
     * @param string $customerId Customer ID
     * @return array List of tokens for the customer
     */
    public function listByCustomer(string $customerId): array
    {
        $response = $this->http->get("/tokens/customer/{$customerId}");
        return $response['data'];
    }

    /**
     * Set a token as the default payment method for a customer
     *
     * @param string $id Token ID
     * @return void
     */
    public function setDefault(string $id): void
    {
        $this->http->post("/tokens/{$id}/set-default");
    }

    /**
     * Delete a saved token
     *
     * @param string $id Token ID
     * @return void
     */
    public function delete(string $id): void
    {
        $this->http->delete("/tokens/{$id}");
    }
}
