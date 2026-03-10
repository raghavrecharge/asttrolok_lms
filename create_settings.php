<?php
try {
    $pdo = new PDO("mysql:host=127.0.0.1;port=3306", 'root', 'root123');
    $pdo->exec("CREATE DATABASE IF NOT EXISTS asttrolok_live_db");
    $pdo->exec("USE asttrolok_live_db");
    
    echo "Database connected successfully!\n";
    
    // Create settings table first (basic structure)
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS settings (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL UNIQUE,
            value TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )
    ");
    
    // Insert basic settings that Laravel needs
    $pdo->exec("
        INSERT INTO settings (name, value) VALUES 
        ('security', 'enabled'),
        ('maintenance_mode', 'disabled'),
        ('app_name', 'Asttrolok LMS')
        ON DUPLICATE KEY UPDATE value = VALUES(value)
    ");
    
    echo "Settings table created and populated!\n";
    
    // Create other essential tables
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255),
            email VARCHAR(255) UNIQUE NOT NULL,
            email_verified_at TIMESTAMP NULL,
            password VARCHAR(255),
            remember_token VARCHAR(100),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )
    ");
    
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS migrations (
            id INT AUTO_INCREMENT PRIMARY KEY,
            migration VARCHAR(255) NOT NULL,
            batch INT NOT NULL
        )
    ");
    
    echo "Essential tables created successfully!\n";
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
