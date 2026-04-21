<?php
declare(strict_types=1);

// Enable error display for debugging
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('log_errors', '1');

require __DIR__ . '/app/bootstrap.php';

$config = require BASE_PATH . '/config/config.php';
$dbConfig = require BASE_PATH . '/config/database.php';

echo "<h1>Debug Information</h1>";

try {
    // Test database connection
    echo "<h2>Database Connection Test</h2>";
    $db = new Database($dbConfig);
    echo "<p style='color: green;'>✓ Database connection successful</p>";
    
    // Test Auth initialization
    echo "<h2>Auth Initialization Test</h2>";
    $pdo = $db->getConnection();
    $auth = new Auth($pdo);
    echo "<p style='color: green;'>✓ Auth initialization successful</p>";
    
    // Test table existence
    echo "<h2>Table Existence Test</h2>";
    $tables = ['admin_users', 'household_data'];
    foreach ($tables as $table) {
        $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
        if ($stmt->rowCount() > 0) {
            echo "<p style='color: green;'>✓ Table '$table' exists</p>";
            
            // Show table structure
            $cols = $pdo->query("DESCRIBE $table");
            echo "<pre style='background: #f0f0f0; padding: 10px; border-radius: 4px;'>";
            foreach ($cols->fetchAll(PDO::FETCH_ASSOC) as $col) {
                echo htmlspecialchars(json_encode($col, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)) . "\n";
            }
            echo "</pre>";
        } else {
            echo "<p style='color: red;'>✗ Table '$table' does NOT exist</p>";
        }
    }
    
    // Test admin user
    echo "<h2>Admin User Test</h2>";
    $stmt = $pdo->query("SELECT id, username, role FROM admin_users LIMIT 1");
    $adminUser = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($adminUser) {
        echo "<p style='color: green;'>✓ Admin user found:</p>";
        echo "<pre style='background: #f0f0f0; padding: 10px; border-radius: 4px;'>";
        echo htmlspecialchars(json_encode($adminUser, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        echo "</pre>";
    } else {
        echo "<p style='color: red;'>✗ No admin users found</p>";
    }
    
    // Test data query
    echo "<h2>Data Query Test</h2>";
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM household_data");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "<p style='color: green;'>✓ household_data query successful. Total records: " . $result['count'] . "</p>";
    
    // Test sample data row
    $stmt = $pdo->query("SELECT * FROM household_data LIMIT 1");
    $sampleData = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($sampleData) {
        echo "<p>Sample data row:</p>";
        echo "<pre style='background: #f0f0f0; padding: 10px; border-radius: 4px;'>";
        echo htmlspecialchars(json_encode($sampleData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        echo "</pre>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red; font-weight: bold;'>✗ ERROR: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<pre style='background: #ffe0e0; padding: 10px; border-radius: 4px; color: red;'>";
    echo htmlspecialchars($e->getTraceAsString());
    echo "</pre>";
}

echo "<h2>Configuration</h2>";
echo "<pre style='background: #f0f0f0; padding: 10px; border-radius: 4px;'>";
echo "PHP Version: " . phpversion() . "\n";
echo "PDO Drivers: " . implode(', ', PDO::getAvailableDrivers()) . "\n";
echo "Error Reporting: " . error_reporting() . "\n";
echo "Display Errors: " . ini_get('display_errors') . "\n";
echo "</pre>";
?>
<hr>
<p><a href="admin/data.php">Try accessing data.php</a> | <a href="admin/users.php">Try accessing users.php</a></p>
