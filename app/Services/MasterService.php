<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Response as Psr7Response;
use Illuminate\Http\Client\Response;

class MasterService {
    protected Client $client;
    private string $uri = 'api/master/dynamic/';

    public function __construct()
    {
        $this->client = new Client([
            'base_uri' => rtrim(config('constants.ELRS_BASE_URL') . config('constants.MASTER_BASE_URL'), '/') . '/',
            'timeout' => 30,
            'http_errors' => true,
        ]);
    }

    public function getPattaTypeList(string $dist_code) {
        try {
            $method = 'GET';
            $uri = $this->uri . "patta-list?dist_code=$dist_code";
            $response = $this->client->request($method, $uri, [
                'headers' => [
                    'Authorization' => 'Bearer ' . config('constants.API_SERVICE_TOKEN'),
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

    public function getPattaTypeDetails(string $dist_code, string $type_code) {
        try {
            $method = 'GET';
            $uri = $this->uri . "patta-list?typeCode=$type_code&dist_code=$dist_code";
            $response = $this->client->request($method, $uri, [
                'headers' => [
                    'Authorization' => 'Bearer ' . config('constants.API_SERVICE_TOKEN'),
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

    public function getLandClassList(string $dist_code) {
        try {
            $method = 'GET';
            $uri = $this->uri . "landclass?dist_code=$dist_code";
            $response = $this->client->request($method, $uri, [
                'headers' => [
                    'Authorization' => 'Bearer ' . config('constants.API_SERVICE_TOKEN'),
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