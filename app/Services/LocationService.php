<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Response as Psr7Response;
use Illuminate\Http\Client\Response;

class LocationService {

    protected Client $client;
    private string $uri = 'api/v1.0/locations/';

    public function __construct()
    {
        $this->client = new Client([
            'base_uri' => rtrim(config('constants.ELRS_BASE_URL') . config('constants.LOCATION_BASE_URL'), '/') . '/',
            'timeout' => 30,
            'http_errors' => true,
        ]);
    }

    public function getLocationFromVillageUuid($village_uuid) {
       try {
            $method = 'GET';
            $uri = $this->uri . $village_uuid;
            $response = $this->client->request($method, $uri, [
                'headers' => [
                    // 'Authorization' => 'Bearer ' . config('consta'),
                    // 'Accept' => 'application/json',
                ],
                'query' => [],
            ]);
            return (new Response($response))->json();
        } catch (GuzzleException $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }

    }


}