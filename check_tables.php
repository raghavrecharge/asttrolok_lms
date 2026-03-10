<?php
try {
    $pdo = new PDO("mysql:host=127.0.0.1;port=3306;dbname=asttrolok_live", 'root', 'root123');
    
    echo "Database connection: SUCCESS\n";
    
    // Check if settings table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'settings'");
    $result = $stmt->fetchAll();
    
    if (count($result) > 0) {
        echo "Settings table: EXISTS\n";
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM settings");
        $count = $stmt->fetch();
        echo "Settings records: " . $count['count'] . "\n";
    } else {
        echo "Settings table: NOT FOUND\n";
        
        // Show all tables
        $stmt = $pdo->query("SHOW TABLES");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        echo "Total tables: " . count($tables) . "\n";
        echo "First 10 tables:\n";
        for ($i = 0; $i < min(10, count($tables)); $i++) {
            echo "- " . $tables[$i] . "\n";
        }
    }
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
