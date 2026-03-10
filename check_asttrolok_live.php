<?php
try {
    $pdo = new PDO("mysql:host=127.0.0.1;port=3306;dbname=asttrolok_live", 'root', 'root123');
    
    echo "Database connection: SUCCESS\n";
    
    // Check tables
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "Tables created: " . count($tables) . "\n";
    
    // Check settings table
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM settings");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Settings records: " . $result['count'] . "\n";
    
    echo "✅ asttrolok_live database ready for XAMPP!\n";
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
