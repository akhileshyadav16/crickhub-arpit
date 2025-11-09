<?php
/**
 * Server Startup Script with Database Connection Test
 * Run this to test database connection before starting the server
 */

echo "=== CrickHub Backend - Database Connection Test ===\n\n";

// Load environment
$envFile = __DIR__ . '/.env';
if (is_file($envFile)) {
    $content = file_get_contents($envFile);
    if (substr($content, 0, 3) === "\xEF\xBB\xBF") {
        $content = substr($content, 3);
    }
    $lines = preg_split('/\r?\n/', $content);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) continue;
        $parts = explode('=', $line, 2);
        if (count($parts) === 2) {
            [$k, $v] = $parts;
            $k = trim(preg_replace('/^\xEF\xBB\xBF/', '', trim($k)));
            $v = trim($v);
            putenv("$k=$v");
            $_ENV[$k] = $v;
            $_SERVER[$k] = $v;
        }
    }
    echo "✓ Loaded environment variables from .env\n";
} else {
    echo "⚠ No .env file found\n";
}

// Test database connection
echo "\nTesting database connection...\n";
require_once __DIR__ . '/bootstrap.php';

try {
    $pdo = get_pdo();
    $stmt = $pdo->query("SELECT VERSION() as version, DATABASE() as db");
    $result = $stmt->fetch();
    echo "✓ Database connected successfully!\n";
    echo "  Database: " . ($result['db'] ?: 'N/A') . "\n";
    echo "  MySQL Version: " . substr($result['version'], 0, 40) . "\n";
    
    // Check tables
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM information_schema.tables WHERE table_schema = DATABASE()");
    $tableCount = $stmt->fetch()['count'];
    echo "  Tables: {$tableCount}\n";
    
    if ($tableCount === 0) {
        echo "\n⚠ Warning: No tables found. Import schema:\n";
        echo "   mysql -h [host] -P [port] -u [user] -p [database] < backend/schema-mysql.sql\n";
    } else {
        echo "\n✓ Database is ready!\n";
    }
    
} catch (Exception $e) {
    echo "✗ Database connection failed!\n";
    echo "  Error: " . $e->getMessage() . "\n";
    echo "\nPlease check your .env file and database settings.\n";
    echo "Run: php test-db.php for detailed diagnostics\n";
    exit(1);
}

echo "\n=== Ready to Start Server ===\n";
echo "To start the server, run:\n";
echo "  php -S localhost:8000 public/index.php\n\n";
echo "Or use the health check endpoint after starting:\n";
echo "  http://localhost:8000/api/health\n\n";
echo "The database connection will be established automatically\n";
echo "when the first API request is made.\n";
