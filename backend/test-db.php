<?php
/**
 * Database Connection Test Script
 * Run this to verify your MySQL database connection is working
 */

error_reporting(E_ALL);
ini_set('display_errors', '1');

echo "=== CrickHub Database Connection Test ===\n\n";

// Check if MySQL PDO driver is available
echo "1. Checking MySQL PDO driver...\n";
$availableDrivers = PDO::getAvailableDrivers();
if (in_array('mysql', $availableDrivers, true)) {
    echo "   ✓ pdo_mysql is available\n";
} else {
    echo "   ✗ pdo_mysql is NOT available\n";
    echo "   Available drivers: " . implode(', ', $availableDrivers) . "\n";
    echo "\n   To fix: Enable extension=pdo_mysql in php.ini\n";
    echo "   Location: C:\\xampp\\php\\php.ini\n";
    exit(1);
}

// Load environment variables
echo "\n2. Loading environment variables...\n";
$envFile = __DIR__ . '/.env';
if (is_file($envFile)) {
    $content = file_get_contents($envFile);
    // Remove UTF-8 BOM if present
    if (substr($content, 0, 3) === "\xEF\xBB\xBF") {
        $content = substr($content, 3);
    }
    $lines = preg_split('/\r?\n/', $content);
    $loaded = 0;
    $envVars = [];
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) continue;
        $parts = explode('=', $line, 2);
        if (count($parts) === 2) {
            [$k, $v] = $parts;
            $k = trim($k);
            // Remove BOM and other invisible characters
            $k = preg_replace('/^\xEF\xBB\xBF/', '', $k);
            $v = trim($v);
            // Remove any trailing whitespace/newlines from value
            $v = rtrim($v, "\r\n");
            putenv("$k=$v");
            $_ENV[$k] = $v;
            $_SERVER[$k] = $v;
            $envVars[$k] = $v;
            $loaded++;
        }
    }
    // Debug: show what was loaded
    if (isset($envVars['DATABASE_URL'])) {
        echo "   ✓ Found DATABASE_URL: " . substr($envVars['DATABASE_URL'], 0, 60) . "...\n";
    } else {
        echo "   ⚠ DATABASE_URL not in loaded vars. Keys: " . implode(', ', array_keys($envVars)) . "\n";
    }
    echo "   ✓ Loaded {$loaded} variables from .env file\n";
} else {
    echo "   ⚠ .env file not found\n";
}

// Get DATABASE_URL - use the loaded variable directly
$databaseUrl = $_ENV['DATABASE_URL'] ?? $_SERVER['DATABASE_URL'] ?? getenv('DATABASE_URL') ?? null;
if (!$databaseUrl) {
    echo "\n   ✗ DATABASE_URL not found in environment\n";
    echo "   Make sure .env file contains: DATABASE_URL=mysql://...\n";
    exit(1);
}
echo "   ✓ Found DATABASE_URL\n";

echo "\n3. Parsing DATABASE_URL...\n";
$parts = parse_url($databaseUrl);
if ($parts === false || !isset($parts['scheme'], $parts['host'], $parts['path'])) {
    echo "   ✗ Invalid DATABASE_URL format\n";
    exit(1);
}

$user = urldecode($parts['user'] ?? '');
$pass = urldecode($parts['pass'] ?? '');
$host = $parts['host'];
$port = $parts['port'] ?? '3306';
$dbname = ltrim($parts['path'] ?? '', '/');

echo "   Host: {$host}\n";
echo "   Port: {$port}\n";
echo "   Database: {$dbname}\n";
echo "   User: {$user}\n";

// Build DSN
$dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4', $host, $port, $dbname);

echo "\n4. Attempting connection...\n";
try {
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_TIMEOUT => 10,
    ];
    
    // Enable SSL for TiDB Cloud
    if (strpos($host, 'tidbcloud.com') !== false || strpos($host, 'tidb') !== false) {
        echo "   Enabling SSL for TiDB Cloud...\n";
        $options[PDO::MYSQL_ATTR_SSL_CA] = '';
        $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
    }
    
    $pdo = new PDO($dsn, $user, $pass, $options);
    
    echo "   ✓ Connection successful!\n";
    
    // Test query
    echo "\n5. Testing query...\n";
    $stmt = $pdo->query("SELECT VERSION() as version");
    $result = $stmt->fetch();
    echo "   ✓ MySQL version: " . substr($result['version'], 0, 50) . "...\n";
    
    // Check if tables exist
    echo "\n6. Checking database tables...\n";
    $stmt = $pdo->query("SELECT table_name FROM information_schema.tables WHERE table_schema = DATABASE() ORDER BY table_name");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    $requiredTables = ['users', 'teams', 'players', 'matches'];
    $foundTables = [];
    
    foreach ($tables as $table) {
        if (in_array($table, $requiredTables, true)) {
            $foundTables[] = $table;
            echo "   ✓ Table '{$table}' exists\n";
        }
    }
    
    $missingTables = array_diff($requiredTables, $foundTables);
    if (!empty($missingTables)) {
        echo "\n   ⚠ Missing tables: " . implode(', ', $missingTables) . "\n";
        echo "   Run: mysql -h {$host} -P {$port} -u {$user} -p {$dbname} < backend/schema-mysql.sql\n";
    } else {
        echo "\n   ✓ All required tables exist\n";
    }
    
    echo "\n=== Connection test completed successfully! ===\n";
    
} catch (PDOException $e) {
    echo "   ✗ Connection failed!\n";
    echo "   Error: " . $e->getMessage() . "\n";
    echo "\n   Troubleshooting:\n";
    echo "   1. Check if MySQL server is accessible\n";
    echo "   2. Verify credentials in .env file\n";
    echo "   3. Check firewall settings\n";
    echo "   4. For TiDB Cloud: Verify connection string and SSL settings\n";
    exit(1);
}
