<?php

declare(strict_types=1);

function get_pdo(): PDO
{
    static $pdo;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $config = require __DIR__ . '/config.php';

    // Check if MySQL PDO driver is available
    if (!in_array('mysql', PDO::getAvailableDrivers(), true)) {
        $errorMsg = 'MySQL PDO driver (pdo_mysql) is not installed or enabled in PHP.';
        error_log("[CrickHub DB] ERROR: {$errorMsg}");
        error_log("[CrickHub DB] Available PDO drivers: " . implode(', ', PDO::getAvailableDrivers()));
        error_log("[CrickHub DB] To fix: Enable extension=pdo_mysql in php.ini");
        
        json_response([
            'error' => 'Database driver not available',
            'details' => $config['app']['debug'] ? $errorMsg . ' Available drivers: ' . implode(', ', PDO::getAvailableDrivers()) : 'Database driver error. Check server logs.',
            'fix' => 'Enable extension=pdo_mysql in php.ini and restart PHP server'
        ], 500);
    }

    $databaseUrl = getenv('DATABASE_URL') ?: ($_ENV['DATABASE_URL'] ?? null);
    $connectionSource = $databaseUrl ? 'DATABASE_URL' : 'config.php';
    error_log("[CrickHub DB] Attempting connection using: {$connectionSource}");

    try {
        if ($databaseUrl) {
            // Parse DATABASE_URL: mysql://user:pass@host:port/dbname
            $parts = parse_url($databaseUrl);
            if ($parts === false || !isset($parts['scheme'], $parts['host'], $parts['path'])) {
                error_log("[CrickHub DB] ERROR: Invalid DATABASE_URL format");
                throw new PDOException('Invalid DATABASE_URL');
            }

            $user = urldecode($parts['user'] ?? '');
            $pass = urldecode($parts['pass'] ?? '');
            $host = $parts['host'];
            $port = $parts['port'] ?? '3306';
            $dbname = ltrim($parts['path'] ?? '', '/');

            $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4', $host, $port, $dbname);

            $sslInfo = '';
            $isTiDB = (strpos($host, 'tidbcloud.com') !== false || strpos($host, 'tidb') !== false);
            if ($isTiDB) {
                $sslInfo = ' (SSL required)';
            }
            error_log("[CrickHub DB] Connecting to: {$host}:{$port}/{$dbname} (user: {$user}{$sslInfo})");

            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_TIMEOUT => 10,
                PDO::ATTR_PERSISTENT => false,
            ];
            
            // Enable SSL for TiDB Cloud (required for secure connections)
            if ($isTiDB) {
                $options[PDO::MYSQL_ATTR_SSL_CA] = '';
                $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
            }
            
            $pdo = new PDO($dsn, $user, $pass, $options);

            error_log("[CrickHub DB] SUCCESS: Connected to database");
        } else {
            $host = $config['database']['host'];
            $port = $config['database']['port'] ?? '3306';
            $dbname = $config['database']['database'];
            $user = $config['database']['username'];

            $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4', $host, $port, $dbname);

            error_log("[CrickHub DB] Connecting to: {$host}:{$port}/{$dbname} (user: {$user})");

            $pdo = new PDO($dsn, $config['database']['username'], $config['database']['password'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_TIMEOUT => 10,
                PDO::ATTR_PERSISTENT => false,
            ]);

            error_log("[CrickHub DB] SUCCESS: Connected to database");
        }
    } catch (PDOException $exception) {
        $errorMsg = $exception->getMessage();
        $errorCode = $exception->getCode();
        error_log("[CrickHub DB] ERROR: Connection failed - {$errorMsg} (Code: {$errorCode})");
        error_log("[CrickHub DB] DSN used: " . ($dsn ?? 'N/A'));
        
        // Provide more helpful error messages
        $userFriendlyMsg = $errorMsg;
        if (strpos($errorMsg, 'could not find driver') !== false) {
            $userFriendlyMsg = 'MySQL PDO driver not enabled. Enable extension=pdo_mysql in php.ini';
        } elseif (strpos($errorMsg, 'could not connect') !== false || strpos($errorMsg, 'timeout') !== false) {
            $userFriendlyMsg = 'Cannot reach database server. Check network, firewall, and credentials.';
        } elseif (strpos($errorMsg, 'authentication failed') !== false || strpos($errorMsg, 'Access denied') !== false) {
            $userFriendlyMsg = 'Database authentication failed. Check username and password.';
        } elseif (strpos($errorMsg, 'does not exist') !== false || strpos($errorMsg, "Unknown database") !== false) {
            $userFriendlyMsg = 'Database does not exist. Create the database first.';
        }
        
        json_response([
            'error' => 'Database connection failed',
            'details' => $config['app']['debug'] ? $userFriendlyMsg : 'Database connection error. Check server logs.',
            'code' => $errorCode,
        ], 500);
    }

    return $pdo;
}
