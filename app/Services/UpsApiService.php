<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Response as Psr7Response;
use Illuminate\Http\Client\Response;

class UpsApiService
{
    protected Client $client;

    public function __construct()
    {
        $this->client = new Client([
            'base_uri' => rtrim(config('constants.ELRS_BASE_URL') . config('constants.UPS_BASE_URL'), '/') . '/',
            'timeout' => 30,
            'http_errors' => false,
        ]);
    }

    public function getMe(string $token): Response
    {
        $request = $this->request('GET', 'ups/api/v1/dev/me', $token);
        // dd($request->json());
        return $request;
        // return $this->request('GET', 'ups/api/v1/dev/me', $token);
    }

    public function getMyPostings(string $token): Response
    {
        return $this->request('GET', 'ups/api/v1/runtime/my-postings', $token);
    }

    public function getMyScope(string $token, int $posting_id, string $service_code): Response
    {
        return $this->request('GET', 'ups/api/v1/runtime/my-scope', $token, [
            'posting_id'   => $posting_id,
            'service_code' => $service_code,
        ]);
    }

    private function request(string $method, string $uri, string $token, array $query = []): Response
    {
        try {
            $response = $this->client->request($method, $uri, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $token,
                    'Accept' => 'application/json',
                ],
                'query' => $query,
            ]);
            // dd($response);
            return new Response($response);
        } catch (GuzzleException $e) {
            return new Response(new Psr7Response(
                503,
                ['Content-Type' => 'application/json'],
                json_encode([
                    'status' => false,
                    'error' => true,
                    'message' => $e->getMessage(),
                ])
            ));
        }
    }
}
