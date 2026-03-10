<?php
try {
    $dsn = "mysql:host=127.0.0.1;port=3307;dbname=asttrolok_live_db;charset=utf8mb4";
    $pdo = new PDO($dsn, 'root', 'root123');
    echo "SUCCESS: Connected to MySQL on port 3307\n";
    
    // Check if settings table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'settings'");
    if ($stmt->rowCount() > 0) {
        echo "SUCCESS: Settings table exists\n";
    } else {
        echo "INFO: Settings table does not exist\n";
    }
    
} catch (PDOException $e) {
    echo "FAILED: " . $e->getMessage() . "\n";
}
?>
