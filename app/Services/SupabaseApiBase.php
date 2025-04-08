<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;

class SupabaseApiBase
{
    protected $client;
    protected $baseUrl;
    protected $anonKey;
    protected $serviceKey;

    public function __construct()
    {
        $this->baseUrl = env('SUPABASE_URL') . '/rest/v1/';
        $this->anonKey = env('SUPABASE_ANON_KEY', env('SUPABASE_SERVICE_KEY')); // Fallback to service key
        $this->serviceKey = env('SUPABASE_SERVICE_KEY');
        $this->client = new Client();
    }

    /**
     * Execute a GET request to the Supabase REST API.
     */
    public function get(string $endpoint, array $queryParams = [], ?string $authToken = null): array
    {
        return $this->request('GET', $endpoint, [
            'query' => $queryParams,
            'authToken' => $authToken,
        ]);
    }

    /**
     * Execute a POST request to the Supabase REST API.
     */
    public function post(string $endpoint, array $data, ?string $authToken = null): ?array
    {
        return $this->request('POST', $endpoint, [
            'json' => $data,
            'authToken' => $authToken,
            'headers' => ['Prefer' => 'return=representation'],
        ]);
    }

    /**
     * Execute a PATCH request to the Supabase REST API.
     */
    public function patch(string $endpoint, array $data, array $queryParams = [], ?string $authToken = null): ?array
    {
        return $this->request('PATCH', $endpoint, [
            'json' => $data,
            'query' => $queryParams,
            'authToken' => $authToken,
        ]);
    }

    /**
     * Execute a DELETE request to the Supabase REST API.
     */
    public function delete(string $endpoint, array $queryParams = [], ?string $authToken = null): bool
    {
        return $this->request('DELETE', $endpoint, [
            'query' => $queryParams,
            'authToken' => $authToken,
        ], true);
    }

    /**
     * Generic request handler for all HTTP methods.
     */
    protected function request(string $method, string $endpoint, array $options = [], bool $expectNoContent = false): mixed
    {
        $headers = array_merge(
            [
                'apikey' => $this->anonKey,
                'Authorization' => 'Bearer ' . ($options['authToken'] ?? $this->serviceKey),
                'Content-Type' => 'application/json',
            ],
            $options['headers'] ?? []
        );

        $requestOptions = [
            'headers' => $headers,
            'query' => $options['query'] ?? [],
        ];

        if (isset($options['json'])) {
            $requestOptions['json'] = $options['json'];
        }

        try {
            $response = $this->client->request($method, $this->baseUrl . $endpoint, $requestOptions);
            if ($expectNoContent) {
                return $response->getStatusCode() === 204;
            }
            $data = json_decode($response->getBody(), true);
            return is_array($data) ? $data : [];
        } catch (RequestException $e) {
            Log::error("Supabase API request failed", [
                'method' => $method,
                'endpoint' => $endpoint,
                'error' => $e->getMessage(),
                'response' => $e->hasResponse() ? (string) $e->getResponse()->getBody() : null,
            ]);
            // Return an empty array for GET requests instead of null
            return $expectNoContent ? false : [];
        }
    }

    /**
     * Get the anon key.
     */
    public function getKey(): string
    {
        return $this->anonKey;
    }

    /**
     * Get the base URL.
     */
    public function getUrl(): string
    {
        return $this->baseUrl;
    }
}