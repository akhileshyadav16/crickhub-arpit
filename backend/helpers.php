<?php

declare(strict_types=1);

function json_response(array $payload, int $status = 200): void
{
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode($payload);
    exit;
}

function get_request_body(): array
{
    $raw = file_get_contents('php://input') ?: '';
    if ($raw === '') {
        return [];
    }

    $data = json_decode($raw, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        json_response([
            'error' => 'Invalid JSON payload',
            'details' => json_last_error_msg(),
        ], 400);
    }

    return $data;
}

function respond_validation(array $errors): void
{
    json_response([
        'error' => 'Validation error',
        'details' => $errors,
    ], 422);
}

function allow_cors(array $origins): void
{
    $requestOrigin = $_SERVER['HTTP_ORIGIN'] ?? null;
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    
    // Normalize localhost variants (127.0.0.1 and localhost are equivalent)
    $normalizeOrigin = function($origin) {
        if (!$origin) return null;
        return str_replace('http://127.0.0.1:', 'http://localhost:', strtolower($origin));
    };
    
    $normalizedRequestOrigin = $normalizeOrigin($requestOrigin);
    
    // Always set CORS headers
    header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');
    
    // Determine if we should allow this origin
    $shouldAllow = false;
    $allowedOrigin = null;
    
    // Handle wildcard or empty origins - allow all origins
    if (empty($origins) || in_array('*', $origins, true)) {
        $shouldAllow = true;
        if ($requestOrigin) {
            $allowedOrigin = $requestOrigin;
        } else {
            $allowedOrigin = '*';
        }
        error_log("[CrickHub CORS] Allowing origin: " . ($allowedOrigin === '*' ? '*' : $requestOrigin) . " (wildcard mode)");
    } else {
        // Specific origins - normalize them too
        $normalisedOrigins = array_map($normalizeOrigin, $origins);

        if ($normalizedRequestOrigin && in_array($normalizedRequestOrigin, $normalisedOrigins, true)) {
            $shouldAllow = true;
            $allowedOrigin = $requestOrigin; // Use original case
            error_log("[CrickHub CORS] Allowing origin: {$allowedOrigin} (whitelist mode)");
        } else {
            // For OPTIONS preflight, always allow localhost variants in development
            if ($method === 'OPTIONS' && $normalizedRequestOrigin && 
                (strpos($normalizedRequestOrigin, 'http://localhost:') === 0 || 
                 strpos($normalizedRequestOrigin, 'http://127.0.0.1:') === 0)) {
                $shouldAllow = true;
                $allowedOrigin = $requestOrigin;
                error_log("[CrickHub CORS] Allowing localhost origin for OPTIONS: {$requestOrigin}");
            } else {
                error_log("[CrickHub CORS] Origin not allowed: " . ($requestOrigin ?? 'none') . " (allowed: " . implode(', ', $origins) . ")");
            }
        }
    }
    
    // Set the Access-Control-Allow-Origin header
    if ($shouldAllow && $allowedOrigin) {
        header('Access-Control-Allow-Origin: ' . $allowedOrigin);
        if ($allowedOrigin !== '*') {
            header('Vary: Origin');
            header('Access-Control-Allow-Credentials: true');
        }
    }

    // Handle preflight OPTIONS request - MUST exit after setting headers
    if ($method === 'OPTIONS') {
        error_log("[CrickHub CORS] Handling OPTIONS preflight - returning 204");
        http_response_code(204);
        exit;
    }
}

function require_fields(array $data, array $fields): array
{
    $errors = [];

    foreach ($fields as $field => $rule) {
        $value = $data[$field] ?? null;
        if ($value === null || $value === '') {
            $errors[$field] = 'This field is required.';
            continue;
        }

        if ($rule === 'int' && !is_numeric($value)) {
            $errors[$field] = 'Must be a number.';
        }

        if ($rule === 'float' && !is_numeric($value)) {
            $errors[$field] = 'Must be a decimal.';
        }
    }

    return $errors;
}

