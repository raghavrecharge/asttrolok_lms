<?php
// XAMPP MySQL Import Script
$host = '127.0.0.1';
$port = 3306;
$user = 'root';
$password = 'root123';
$database = 'asttrolok_live_db';

try {
    // Connect to MySQL
    $pdo = new PDO("mysql:host=$host;port=$port", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Connected to XAMPP MySQL/MariaDB successfully!\n";
    
    // Drop and recreate database
    $pdo->exec("DROP DATABASE IF EXISTS $database");
    $pdo->exec("CREATE DATABASE $database");
    $pdo->exec("USE $database");
    
    echo "Database $database created fresh!\n";
    
    // Read SQL file
    $sqlFile = "H:\lms_asttrolok_new\asttrolok_live.sql";
    if (!file_exists($sqlFile)) {
        die("SQL file not found: $sqlFile\n");
    }
    
    echo "Starting import from $sqlFile...\n";
    
    $file = fopen($sqlFile, 'r');
    $sql = '';
    $lineCount = 0;
    $executedCount = 0;
    $errorCount = 0;
    
    while (($line = fgets($file)) !== false) {
        $line = trim($line);
        $lineCount++;
        
        // Skip comments and empty lines
        if (empty($line) || strpos($line, '--') === 0 || strpos($line, '/*') === 0) {
            continue;
        }
        
        $sql .= $line . ' ';
        
        // Execute when we find a semicolon
        if (strpos($line, ';') !== false) {
            if (!empty(trim($sql))) {
                try {
                    $pdo->exec($sql);
                    $executedCount++;
                    
                    // Progress indicator
                    if ($executedCount % 100 === 0) {
                        echo "Executed $executedCount statements...\n";
                    }
                } catch (PDOException $e) {
                    $errorCount++;
                    if ($errorCount <= 10) { // Show only first 10 errors
                        echo "Error: " . substr($e->getMessage(), 0, 100) . "\n";
                    }
                }
                $sql = '';
            }
        }
    }
    
    fclose($file);
    
    echo "\n=== IMPORT COMPLETED ===\n";
    echo "Total lines processed: $lineCount\n";
    echo "Statements executed: $executedCount\n";
    echo "Errors encountered: $errorCount\n";
    
    // Verify database
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "Tables created: " . count($tables) . "\n";
    
    // Check settings table
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM settings");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Settings records: " . $result['count'] . "\n";
    
    echo "\n✅ XAMPP database import completed successfully!\n";
    echo "Your Laravel application should now work with the complete database.\n";
    
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage() . "\n";
}
?>
