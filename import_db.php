<?php
try {
    $pdo = new PDO("mysql:host=127.0.0.1", 'root', 'root123');
    $pdo->exec("CREATE DATABASE IF NOT EXISTS asttrolok_live_db");
    $pdo->exec("USE asttrolok_live_db");
    
    echo "Database created/connected successfully!\n";
    
    // Read and import SQL file
    $sql = file_get_contents('asttrolok_live_db.sql');
    
    // Split SQL statements
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    
    foreach ($statements as $statement) {
        if (!empty($statement)) {
            try {
                $pdo->exec($statement);
                echo "Executed statement successfully\n";
            } catch (PDOException $e) {
                echo "Error: " . $e->getMessage() . "\n";
            }
        }
    }
    
    echo "Database import completed!\n";
    
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage() . "\n";
}
?>
