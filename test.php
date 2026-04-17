<?php
try {
    $dbConfig = require __DIR__ . '/config/database.php';
    $pdo = new PDO(
        "mysql:host={$dbConfig['host']};port={$dbConfig['port']};dbname={$dbConfig['database']};charset={$dbConfig['charset']}",
        $dbConfig['username'],
        $dbConfig['password'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    echo "Connection successful";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>