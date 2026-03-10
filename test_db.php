<?php
$passwords = ['', 'root', 'password', 'root123', 'admin', 'asttrolok', 'MySQL84', 'Admin@123'];
foreach ($passwords as $pwd) {
    try {
        $dsn = "mysql:host=127.0.0.1;dbname=asttrolok_live_db;charset=utf8mb4";
        $pdo = new PDO($dsn, 'root', $pwd);
        echo "SUCCESS: Password is '$pwd'\n";
        exit;
    }
    catch (PDOException $e) {
        echo "FAILED: '$pwd' - " . $e->getMessage() . "\n";
    }
}
