<?php

declare(strict_types=1);

namespace App\Services;

class TestCardService
{
    private string $baseUrl;
    private string $apiKey;

    private const FALLBACK_CARDS = [
        ['number' => '4242424242424242', 'brand' => 'Visa', 'scenario' => 'Success'],
        ['number' => '5555555555554444', 'brand' => 'Mastercard', 'scenario' => 'Success'],
        ['number' => '4000000000000002', 'brand' => 'Visa', 'scenario' => 'Decline'],
        ['number' => '4000002760003184', 'brand' => 'Visa', 'scenario' => '3DS Required'],
    ];

    public function __construct(string $baseUrl, string $apiKey)
    {
        $this->baseUrl = rtrim($baseUrl, '/');
        $this->apiKey = $apiKey;
    }

    public function getTestCards(): array
    {
        try {
            $context = stream_context_create([
                'http' => [
                    'method' => 'GET',
                    'header' => "X-Api-Key: {$this->apiKey}\r\nContent-Type: application/json\r\n",
                    'timeout' => 5,
                    'ignore_errors' => true,
                ],
            ]);

            $response = @file_get_contents("{$this->baseUrl}/api/config/test-cards", false, $context);

            if ($response !== false) {
                $data = json_decode($response, true);
                if (isset($data['testCards']) && is_array($data['testCards'])) {
                    return [
                        'cards' => array_map(function ($card) {
                            return [
                                'number' => $card['number'] ?? '',
                                'brand' => $card['brand'] ?? '',
                                'scenario' => $card['scenario'] ?? '',
                            ];
                        }, $data['testCards']),
                        'cvvHint' => $data['cvvHint'] ?? 'Any 3 digits',
                        'expiryHint' => $data['expiryHint'] ?? 'Any future date',
                    ];
                }
            }
        } catch (\Exception $e) {
            // Fall back to hardcoded cards
        }

        return [
            'cards' => self::FALLBACK_CARDS,
            'cvvHint' => 'Any 3 digits',
            'expiryHint' => 'Any future date',
        ];
    }
}
