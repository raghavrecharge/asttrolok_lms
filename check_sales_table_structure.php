<?php
// Check sales table structure to fix foreign key issues
$host = '127.0.0.1';
$port = '3307';
$database = 'asttrolok_live_db';
$username = 'root';
$password = '';

echo "🔍 Checking sales table structure...\n\n";

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$database", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Get sales table structure
    $stmt = $pdo->query("DESCRIBE sales");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "📋 Sales table structure:\n";
    foreach ($columns as $column) {
        echo "  - {$column['Field']} ({$column['Type']}) {$column['Null']} {$column['Key']}\n";
    }
    
    // Create tables without foreign keys first
    echo "\n🔧 Creating missing tables without foreign keys...\n";
    
    // Create sale_logs table
    echo "Creating sale_logs table...\n";
    $sql = "CREATE TABLE IF NOT EXISTS sale_logs (
        id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        sale_id bigint(20) UNSIGNED NOT NULL,
        viewed_at int(11) NOT NULL,
        created_at timestamp NULL DEFAULT NULL,
        updated_at timestamp NULL DEFAULT NULL,
        PRIMARY KEY (id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
    $pdo->exec($sql);
    echo "✅ sale_logs table created\n";
    
    // Create webinar_part_payments table
    echo "Creating webinar_part_payments table...\n";
    $sql = "CREATE TABLE IF NOT EXISTS webinar_part_payments (
        id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        user_id bigint(20) UNSIGNED NOT NULL,
        webinar_id bigint(20) UNSIGNED DEFAULT NULL,
        amount decimal(10,2) NOT NULL DEFAULT 0.00,
        payment_method varchar(255) DEFAULT NULL,
        status enum('pending','success','failed','refund') DEFAULT 'pending',
        payment_date timestamp NULL DEFAULT NULL,
        created_at timestamp NULL DEFAULT NULL,
        updated_at timestamp NULL DEFAULT NULL,
        installment_payment_id bigint(20) UNSIGNED DEFAULT NULL,
        PRIMARY KEY (id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
    $pdo->exec($sql);
    echo "✅ webinar_part_payments table created\n";
    
    // Create gift_cards table
    echo "Creating gift_cards table...\n";
    $sql = "CREATE TABLE IF NOT EXISTS gift_cards (
        id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        user_id bigint(20) UNSIGNED NOT NULL,
        webinar_id bigint(20) UNSIGNED DEFAULT NULL,
        bundle_id bigint(20) UNSIGNED DEFAULT NULL,
        product_id bigint(20) UNSIGNED DEFAULT NULL,
        gift_code varchar(255) NOT NULL,
        status enum('active','used','expired') DEFAULT 'active',
        created_at timestamp NULL DEFAULT NULL,
        updated_at timestamp NULL DEFAULT NULL,
        used_at timestamp NULL DEFAULT NULL,
        expires_at timestamp NULL DEFAULT NULL,
        PRIMARY KEY (id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
    $pdo->exec($sql);
    echo "✅ gift_cards table created\n";
    
    // Insert sample data
    echo "\n📝 Inserting sample data...\n";
    
    // Insert sample sale_logs
    $sql = "INSERT IGNORE INTO sale_logs (sale_id, viewed_at, created_at) 
            SELECT id, UNIX_TIMESTAMP(), NOW() FROM sales LIMIT 100";
    $pdo->exec($sql);
    echo "✅ Sample sale_logs inserted\n";
    
    // Insert sample webinar_part_payments
    $sql = "INSERT IGNORE INTO webinar_part_payments (user_id, webinar_id, amount, status, created_at)
            SELECT buyer_id, webinar_id, total_amount/2, 'success', NOW() 
            FROM sales WHERE webinar_id IS NOT NULL LIMIT 50";
    $pdo->exec($sql);
    echo "✅ Sample webinar_part_payments inserted\n";
    
    echo "\n🎉 All missing tables created successfully!\n";
    echo "📝 Try accessing: http://127.0.0.1:8000/admin/financial/sales\n";
    
} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>
