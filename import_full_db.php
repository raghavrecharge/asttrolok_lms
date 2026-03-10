<?php
try {
    $pdo = new PDO("mysql:host=127.0.0.1;port=3306", 'root', 'root123');
    $pdo->exec("CREATE DATABASE IF NOT EXISTS asttrolok_live_db");
    $pdo->exec("USE asttrolok_live_db");
    
    echo "Database connected successfully!\n";
    
    // Read SQL file in chunks
    $file = fopen('asttrolok_live_db.sql', 'r');
    if (!$file) {
        die("Could not open SQL file\n");
    }
    
    $sql = '';
    $inInsert = false;
    $lineCount = 0;
    
    while (($line = fgets($file)) !== false) {
        $line = trim($line);
        $lineCount++;
        
        // Skip comments and empty lines
        if (empty($line) || strpos($line, '--') === 0 || strpos($line, '/*') === 0) {
            continue;
        }
        
        // Handle multi-line INSERT statements
        if (strpos($line, 'INSERT INTO') === 0) {
            $inInsert = true;
        }
        
        $sql .= $line . "\n";
        
        // Execute when we find a semicolon and not in INSERT
        if (strpos($line, ';') !== false && !$inInsert) {
            if (!empty(trim($sql))) {
                try {
                    $pdo->exec($sql);
                    echo "Executed query successfully\n";
                } catch (PDOException $e) {
                    echo "Query error: " . substr($e->getMessage(), 0, 100) . "\n";
                }
                $sql = '';
            }
        }
        
        // End INSERT statement
        if ($inInsert && strpos($line, ';') !== false) {
            $inInsert = false;
            if (!empty(trim($sql))) {
                try {
                    $pdo->exec($sql);
                    echo "Inserted data successfully\n";
                } catch (PDOException $e) {
                    echo "Insert error: " . substr($e->getMessage(), 0, 100) . "\n";
                }
                $sql = '';
            }
        }
        
        // Progress indicator
        if ($lineCount % 1000 === 0) {
            echo "Processed $lineCount lines...\n";
        }
    }
    
    fclose($file);
    echo "Database import completed!\n";
    
} catch (PDOException $e) {
    echo "Connection error: " . $e->getMessage() . "\n";
}
?>
