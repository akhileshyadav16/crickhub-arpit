<?php

declare(strict_types=1);

// Configure session cookie for cross-origin support
ini_set('session.cookie_httponly', '1');
ini_set('session.cookie_samesite', 'Lax');
ini_set('session.use_strict_mode', '1');

// Start session with proper configuration
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$config = require __DIR__ . '/config.php';

if ($config['app']['debug']) {
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
}

// Lightweight .env loader (key=value per line)
$envFile = __DIR__ . '/.env';
if (is_file($envFile)) {
    error_log("[CrickHub] Loading .env file: {$envFile}");
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];
    $loaded = 0;
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) continue;
        $parts = explode('=', $line, 2);
        if (count($parts) === 2) {
            [$k, $v] = $parts;
            $k = trim($k);
            $v = trim($v);
            putenv("$k=$v");
            $_ENV[$k] = $v;
            $_SERVER[$k] = $v;
            $loaded++;
        }
    }
    error_log("[CrickHub] Loaded {$loaded} environment variables from .env");
} else {
    error_log("[CrickHub] No .env file found at: {$envFile}");
}

require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';

