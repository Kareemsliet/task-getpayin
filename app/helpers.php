<?php

if (!function_exists('successJsonResponse')) {
    function successJsonResponse(string|null $message = null, mixed $data = null)
    {
        return response()->json([
            "status" => true,
            "message" => $message,
            "data" => $data,
        ], 200);
    }
}

if (!function_exists('errorJsonResponse')) {
    function errorJsonResponse(string|null $message = null, mixed $data = null, int $code = 422)
    {
        return response()->json([
            "status" => false,
            "message" => $message,
            "data" => $data,
        ], $code);
    }
}