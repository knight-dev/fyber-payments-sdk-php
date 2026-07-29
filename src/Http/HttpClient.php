<?php

namespace Fyber\Http;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Fyber\Exceptions\FyberException;

class HttpClient
{
    private Client $client;
    private string $apiKey;
    private string $baseUrl;

    public function __construct(
        string $apiKey,
        string $environment = 'test',
        ?string $baseUrl = null,
        int $timeout = 30
    ) {
        $this->apiKey = $apiKey;

        // Determine base URL
        if ($baseUrl) {
            $this->baseUrl = $baseUrl;
        } else {
            $this->baseUrl = $environment === 'live'
                ? 'https://api.fyber.one/v1'
                : 'https://api.sandbox.fyber.one/v1';
        }

        $this->client = new Client([
            'base_uri' => rtrim($this->baseUrl, '/') . '/',
            'timeout' => $timeout,
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'X-Api-Key' => $this->apiKey,
                'User-Agent' => 'Fyber-PHP-SDK/1.0.2',
            ],
        ]);
    }

    /**
     * Make a GET request
     */
    public function get(string $path, ?array $params = null): array
    {
        return $this->request('GET', $path, null, $params);
    }

    /**
     * Make a POST request
     */
    public function post(string $path, ?array $data = null): array
    {
        return $this->request('POST', $path, $data);
    }

    /**
     * Make a PUT request
     */
    public function put(string $path, ?array $data = null): array
    {
        return $this->request('PUT', $path, $data);
    }

    /**
     * Make a DELETE request
     */
    public function delete(string $path): array
    {
        return $this->request('DELETE', $path);
    }

    /**
     * Normalize path by removing leading slash for proper URI resolution
     */
    private function normalizePath(string $path): string
    {
        return ltrim($path, '/');
    }

    /**
     * Make an HTTP request
     */
    private function request(string $method, string $path, ?array $data = null, ?array $params = null): array
    {
        $options = [];

        if ($data !== null) {
            $options['json'] = $data;
        }

        if ($params !== null) {
            $options['query'] = $params;
        }

        try {
            $response = $this->client->request($method, $this->normalizePath($path), $options);
            $body = $response->getBody()->getContents();

            return [
                'data' => json_decode($body, true),
                'status' => $response->getStatusCode(),
                'headers' => $response->getHeaders(),
            ];
        } catch (RequestException $e) {
            if ($e->hasResponse()) {
                $response = $e->getResponse();
                $bodyContent = $response->getBody()->getContents();
                $body = json_decode($bodyContent, true);
                $statusCode = $response->getStatusCode();

                // Handle empty response body (common with 401 auth failures)
                if (empty($bodyContent) || $body === null) {
                    $type = $statusCode === 401 ? 'authentication_error' : ($statusCode === 403 ? 'authorization_error' : 'api_error');
                    $code = $statusCode === 401 ? 'invalid_api_key' : ($statusCode === 403 ? 'access_denied' : 'empty_response');
                    throw new FyberException(
                        "API returned {$statusCode} with no response body. This may indicate invalid API credentials.",
                        $type,
                        $code,
                        $statusCode
                    );
                }

                throw FyberException::fromResponse($body, $statusCode);
            }
            throw new FyberException($e->getMessage(), 'network_error');
        }
    }
}
