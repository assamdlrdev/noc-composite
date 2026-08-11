<?php

use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;

function jwtencode($payload) {
    $token = JWT::encode($payload, config('constants.JWT_SECRET'), 'HS256');
    return $token;
}

function jwtdecode(string $token) {
    $decodedToken = JWT::decode(
        $token,
        new Key(config('constants.JWT_SECRET'), 'HS256')
    );
    if(!isset($decodedToken) || empty($decodedToken)) {
        return false;
    }

    return $decodedToken;
}

