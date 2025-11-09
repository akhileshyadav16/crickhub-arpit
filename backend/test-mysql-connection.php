<?php
/**
 * Quick MySQL Connection Tester
 * Run this to find your MySQL connection details
 */

echo "=== MySQL Connection Finder ===\n\n";

// Test common local configurations
$configs = [
    ['host' => '127.0.0.1', 'port' => 3306, 'user' => 'root', 'pass' => ''],
    ['host' => 'localhost', 'port' => 3306, 'user' => 'root', 'pass' => ''],
    ['host' => '127.0.0.1', 'port' => 3306, 'user' => 'root', 'pass' => 'root'],
    ['host' => 'localhost', 'port' => 3306, 'user' => 'root', 'pass' => 'root'],
];

echo "Testing common local configurations...\n\n";

$found = false;
foreach ($configs as $config) {
    echo "Testing: {$config['user']}@{$config['host']}:{$config['port']} (pass: " . ($config['pass'] ?: 'empty') . ")\n";
    
    try {
        $dsn = "mysql:host={$config['host']};port={$config['port']}";
        $pdo = new PDO($dsn, $config['user'], $config['pass'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_TIMEOUT => 2,
        ]);
        
        echo "  ✅ SUCCESS! Connection works!\n";
        echo "  📋 Use these settings in your .env:\n";
        echo "     CRICKHUB_DB_HOST={$config['host']}\n";
        echo "     CRICKHUB_DB_PORT={$config['port']}\n";
        echo "     CRICKHUB_DB_USER={$config['user']}\n";
        echo "     CRICKHUB_DB_PASSWORD=" . ($config['pass'] ?: '') . "\n\n";
        
        // Get MySQL version
        $stmt = $pdo->query("SELECT VERSION() as version");
        $result = $stmt->fetch();
        echo "  MySQL Version: {$result['version']}\n\n";
        
        // List databases
        echo "  Available databases:\n";
        $stmt = $pdo->query("SHOW DATABASES");
        while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            echo "    - {$row[0]}\n";
        }
        
        $found = true;
        break;
        
    } catch (PDOException $e) {
        $error = $e->getMessage();
        if (strpos($error, 'Access denied') !== false) {
            echo "  ⚠️  Wrong password\n";
        } elseif (strpos($error, 'could not find driver') !== false) {
            echo "  ❌ MySQL PDO driver not enabled!\n";
            echo "     Enable extension=pdo_mysql in php.ini\n";
            break;
        } elseif (strpos($error, 'Connection refused') !== false || strpos($error, 'No connection') !== false) {
            echo "  ⚠️  MySQL not running or wrong host/port\n";
        } else {
            echo "  ❌ Error: {$error}\n";
        }
    }
    echo "\n";
}

if (!$found) {
    echo "\n❌ Could not connect with any default configuration.\n\n";
    echo "Try these steps:\n";
    echo "1. Make sure MySQL is running (XAMPP Control Panel → Start MySQL)\n";
    echo "2. Check your MySQL username/password\n";
    echo "3. Check MySQL port (default: 3306)\n";
    echo "4. For custom setup, edit this script and add your config\n\n";
    
    echo "To find your MySQL details:\n";
    echo "- XAMPP: Usually root with empty password\n";
    echo "- Check phpMyAdmin (XAMPP → Admin → MySQL)\n";
    echo "- Check MySQL config: C:\\xampp\\mysql\\bin\\my.ini\n";
}

echo "\n=== Test Complete ===\n";



