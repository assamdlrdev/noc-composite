<?php

function successResponse(string $message, $data = null) {
    return [
        'success' => 1,
        'message' => $message,
        'data' => $data
    ];
}

function errorResponse(string $message, $data = null) {
    return [
        'success' => 0,
        'message' => $message,
        'data' => $data
    ];
}