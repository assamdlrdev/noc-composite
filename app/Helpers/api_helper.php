<?php
use Illuminate\Support\Facades\Log;

function tokenize($pvtKey, $expTime) {
    $header = ['alg' => 'HS256', 'typ' => 'JWT'];
    $payload = [
        'sub' => 'API_Call',
        'iat' => 1516239022,
        'exp' => time() + $expTime
    ];
    // $key = 'olkhnmnbgfdsaqwertgnjjmlgpvdhdfagsjsdfqwaspojwqaxsplnbdlydrnvfi'; // shared secret
    $key = $pvtKey;

    $base64UrlHeader = rtrim(strtr(base64_encode(json_encode($header)), '+/', '-_'), '=');
    $base64UrlPayload = rtrim(strtr(base64_encode(json_encode($payload)), '+/', '-_'), '=');
    $signature = hash_hmac('sha256', "$base64UrlHeader.$base64UrlPayload", $key, true);
    $base64UrlSignature = rtrim(strtr(base64_encode($signature), '+/', '-_'), '=');

    $token = "$base64UrlHeader.$base64UrlPayload.$base64UrlSignature";
    return $token;
}

function callApi($url, $method, $data = null)
{
    $key = config('constants.LANDHUB_DEMO_PVT_KEY');
    $expTime = 31536000;
    $token = tokenize($key, $expTime);

    $curl = curl_init();

    curl_setopt_array($curl, array(
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        // CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_CUSTOMREQUEST => $method,
        CURLOPT_SSL_VERIFYHOST => 2,
        CURLOPT_POSTFIELDS => http_build_query($data),
        CURLOPT_VERBOSE => 1,
        CURLOPT_HTTPHEADER => config('constants.IS_PRODUCTION') == 0 ? array(
            'Content-Type: application/x-www-form-urlencoded',
            'X-Api-Key: LOC_API',
            'Authorization: Bearer ' . $token
        ) : array(
            'Content-Type: application/x-www-form-urlencoded'
        ),
    ));

    $response = curl_exec($curl);
    $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

    Log::info("API REQUEST", [
        'url' => $url,
        'method' => $method,
        'headers' => [
            'Content-Type: application/x-www-form-urlencoded'
        ],
        'data' => $data
    ]);

    // Check if the cURL request was successful or failed
    if(curl_errno($curl)) {
        $error_msg = curl_error($curl);
        Log::error("cURL Error: " . $error_msg);  // Log any cURL errors
    }

    curl_close($curl);
    Log::info("API RESPONSE: ", [
        'http_code' => $httpcode,
        'response' => $response
    ]);
    
    if ($httpcode != 200) {
        // log_message("error", 'API FAIL');
        return [
            'status' => 'n',
            'data' => $response,
            'error_code' => $httpcode
        ];
    }

    return [
        'status' => 'y',
        'data' => $response
    ];
}




function callLandhubAPI($method, $url, $data)
{
    $key = config('constants.BHUNAKSHA_PRIVATE_KEY');
    $expTime = 900;
    $token = tokenize($key, $expTime);

    $curl = curl_init();
    $jsonData = json_encode($data);

    curl_setopt_array($curl, array(
        CURLOPT_URL => config('constants.LANDHUB_BASE_URL') . $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST =>  true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        // CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_CUSTOMREQUEST => $method,
        CURLOPT_SSL_VERIFYHOST => 2,
        CURLOPT_POSTFIELDS => $jsonData,
        CURLOPT_HTTPHEADER => array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($jsonData),
            'Authorization: Bearer ' . $token
        ),
    ));

    $response = curl_exec($curl);
    $resp = [];
    if (curl_errno($curl)) {
        $resp = [
            'error' => curl_error($curl),
            'http_status' => curl_getinfo($curl, CURLINFO_HTTP_CODE),
            'data' => ''
        ];
    } else {
        $resp = [
            'error' => '',
            'http_status' => curl_getinfo($curl, CURLINFO_HTTP_CODE),
            'data' => $response
        ];
    }
    curl_close($curl);
    return $resp;
}

function smsApi($method, $data) {
    $pvt_key = config('constants.SMS_PVT_KEY');
    $period = 31536000;
    $token = tokenize($pvt_key, $period);

    $curl = curl_init();
    $jsonData = json_encode($data);

    curl_setopt_array($curl, array(
        CURLOPT_URL => config('constants.SMS_PROD_LINK'),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST =>  true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        // CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_CUSTOMREQUEST => $method,
        CURLOPT_SSL_VERIFYHOST => 2,
        CURLOPT_POSTFIELDS => $jsonData,
        CURLOPT_HTTPHEADER => array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($jsonData),
            'x-api-key: SMS-PRO-LIVE',
            'Authorization: Bearer ' . $token
        ),
    ));
    $response = curl_exec($curl);
    
    $resp = [];
    if (curl_errno($curl)) {
        $resp = [
            'error' => curl_error($curl),
            'http_status' => curl_getinfo($curl, CURLINFO_HTTP_CODE),
            'data' => ''
        ];
    } else {
        $resp = [
            'error' => '',
            'http_status' => curl_getinfo($curl, CURLINFO_HTTP_CODE),
            'data' => $response
        ];
    }
    curl_close($curl);
    return $resp;
}