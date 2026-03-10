<?php
try {
    $pdo = new PDO("mysql:host=127.0.0.1;port=3306", 'root', 'root123');
    
    // Drop and recreate database completely
    $pdo->exec("DROP DATABASE IF EXISTS asttrolok_live_db");
    $pdo->exec("CREATE DATABASE asttrolok_live_db");
    $pdo->exec("USE asttrolok_live_db");
    
    echo "Fresh database created successfully!\n";
    
    // Read and execute SQL file
    $file = fopen('asttrolok_live_db.sql', 'r');
    if (!$file) {
        die("Could not open SQL file\n");
    }
    
    $sql = '';
    $lineCount = 0;
    
    while (($line = fgets($file)) !== false) {
        $line = trim($line);
        $lineCount++;
        
        // Skip comments and empty lines
        if (empty($line) || strpos($line, '--') === 0 || strpos($line, '/*') === 0) {
            continue;
        }
        
        $sql .= $line . "\n";
        
        // Execute when we find a semicolon
        if (strpos($line, ';') !== false) {
            if (!empty(trim($sql))) {
                try {
                    $pdo->exec($sql);
                    if ($lineCount % 100 === 0) {
                        echo "Imported $lineCount lines...\n";
                    }
                } catch (PDOException $e) {
                    echo "Error at line $lineCount: " . substr($e->getMessage(), 0, 80) . "\n";
                }
                $sql = '';
            }
        }
    }
    
    fclose($file);
    echo "Fresh database import completed successfully!\n";
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
