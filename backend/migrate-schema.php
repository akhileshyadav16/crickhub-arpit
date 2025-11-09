<?php
/**
 * Automated Schema Migration Script
 * This script reads your .env file and imports the schema automatically
 */

echo "=== CrickHub Schema Migration ===\n\n";

// Load environment
$envFile = __DIR__ . '/.env';
if (!is_file($envFile)) {
    echo "❌ Error: .env file not found at: {$envFile}\n";
    echo "Please create .env file with DATABASE_URL\n";
    exit(1);
}

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

$databaseUrl = $_ENV['DATABASE_URL'] ?? $_SERVER['DATABASE_URL'] ?? getenv('DATABASE_URL') ?? null;
if (!$databaseUrl) {
    echo "❌ Error: DATABASE_URL not found in .env file\n";
    exit(1);
}

// Parse connection details
$parts = parse_url($databaseUrl);
if ($parts === false || !isset($parts['scheme'], $parts['host'], $parts['path'])) {
    echo "❌ Error: Invalid DATABASE_URL format\n";
    exit(1);
}

$user = urldecode($parts['user'] ?? '');
$pass = urldecode($parts['pass'] ?? '');
$host = $parts['host'];
$port = $parts['port'] ?? '3306';
$dbname = ltrim($parts['path'] ?? '', '/');

echo "Connecting to database...\n";
echo "  Host: {$host}\n";
echo "  Port: {$port}\n";
echo "  Database: {$dbname}\n";
echo "  User: {$user}\n\n";

// Check if MySQL driver is available
if (!in_array('mysql', PDO::getAvailableDrivers(), true)) {
    echo "❌ Error: MySQL PDO driver not available\n";
    exit(1);
}

// Connect to database
$dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4', $host, $port, $dbname);
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

// Enable SSL for TiDB Cloud
if (strpos($host, 'tidbcloud.com') !== false || strpos($host, 'tidb') !== false) {
    $options[PDO::MYSQL_ATTR_SSL_CA] = '';
    $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
}

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    echo "✅ Connected successfully!\n\n";
} catch (PDOException $e) {
    echo "❌ Connection failed: " . $e->getMessage() . "\n";
    exit(1);
}

// Read schema file
$schemaFile = __DIR__ . '/schema-mysql.sql';
if (!is_file($schemaFile)) {
    echo "❌ Error: Schema file not found: {$schemaFile}\n";
    exit(1);
}

$schema = file_get_contents($schemaFile);
if (empty($schema)) {
    echo "❌ Error: Schema file is empty\n";
    exit(1);
}

echo "Reading schema file...\n";
echo "  File: {$schemaFile}\n";
echo "  Size: " . number_format(strlen($schema)) . " bytes\n\n";

// Split into individual statements (handle multi-line statements)
$statements = [];
$current = '';
$lines = explode("\n", $schema);
foreach ($lines as $line) {
    $line = trim($line);
    if (empty($line) || str_starts_with($line, '--')) {
        continue;
    }
    $current .= $line . "\n";
    if (str_ends_with(trim($line), ';')) {
        $stmt = trim($current);
        if (!empty($stmt)) {
            $statements[] = $stmt;
        }
        $current = '';
    }
}

echo "Found " . count($statements) . " SQL statements\n\n";

// Execute statements
$successCount = 0;
$errorCount = 0;

foreach ($statements as $index => $statement) {
    $statement = trim($statement);
    if (empty($statement) || str_starts_with($statement, '--')) {
        continue;
    }
    
    try {
        $pdo->exec($statement);
        $successCount++;
        
        // Show progress for CREATE statements
        if (preg_match('/CREATE\s+(TABLE|INDEX)/i', $statement)) {
            if (preg_match('/CREATE\s+TABLE\s+(?:IF\s+NOT\s+EXISTS\s+)?`?(\w+)`?/i', $statement, $matches)) {
                echo "  ✓ Created table: {$matches[1]}\n";
            } elseif (preg_match('/CREATE\s+INDEX\s+(?:IF\s+NOT\s+EXISTS\s+)?`?(\w+)`?/i', $statement, $matches)) {
                echo "  ✓ Created index: {$matches[1]}\n";
            }
        } elseif (preg_match('/INSERT\s+INTO/i', $statement)) {
            echo "  ✓ Inserted seed data\n";
        }
    } catch (PDOException $e) {
        $errorCount++;
        $errorMsg = $e->getMessage();
        
        // Ignore "already exists" errors for IF NOT EXISTS
        if (strpos($errorMsg, 'already exists') !== false || 
            strpos($errorMsg, 'Duplicate') !== false) {
            echo "  ⚠ Skipped (already exists)\n";
            $errorCount--; // Don't count as error
        } else {
            echo "  ❌ Error: {$errorMsg}\n";
            echo "    Statement: " . substr($statement, 0, 100) . "...\n";
        }
    }
}

echo "\n=== Migration Summary ===\n";
echo "  Successful: {$successCount}\n";
echo "  Errors: {$errorCount}\n\n";

// Verify tables
echo "Verifying tables...\n";
try {
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    $expectedTables = ['users', 'teams', 'players', 'matches'];
    $foundTables = array_intersect($expectedTables, $tables);
    
    foreach ($expectedTables as $table) {
        if (in_array($table, $foundTables)) {
            $countStmt = $pdo->query("SELECT COUNT(*) as count FROM `{$table}`");
            $count = $countStmt->fetch()['count'];
            echo "  ✓ Table '{$table}' exists ({$count} rows)\n";
        } else {
            echo "  ❌ Table '{$table}' missing\n";
        }
    }
    
    echo "\n✅ Migration completed successfully!\n";
    echo "\nDefault login credentials:\n";
    echo "  Admin: admin@crickhub.local / admin123\n";
    echo "  Viewer: viewer@crickhub.local / viewer123\n";
    
} catch (PDOException $e) {
    echo "  ❌ Verification failed: " . $e->getMessage() . "\n";
    exit(1);
}

