<?php
try {
    $pdo = new PDO("mysql:host=127.0.0.1;port=3306;dbname=asttrolok_live_db", 'root', 'root123');
    
    echo "Database connection: SUCCESS\n";
    
    // Check settings table
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM settings");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Settings table records: " . $result['count'] . "\n";
    
    // Show some settings
    $stmt = $pdo->query("SELECT * FROM settings LIMIT 5");
    $settings = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "Sample settings:\n";
    foreach ($settings as $setting) {
        echo "- {$setting['name']} ({$setting['page']})\n";
    }
    
    // Check users table
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Users table records: " . $result['count'] . "\n";
    
    echo "Database check completed successfully!\n";
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
