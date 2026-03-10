<?php
// Check tables needed for financial/sales page
$host = '127.0.0.1';
$port = '3307';
$database = 'asttrolok_live_db';
$username = 'root';
$password = '';

echo "🔍 Checking tables needed for Financial Sales page on port $port...\n\n";

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$database", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✅ Database connected successfully\n\n";
    
    // Get all tables
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    // Tables needed by SaleController
    $requiredTables = [
        'sales',
        'webinars', 
        'users',
        'orders',
        'reserve_meetings',
        'sale_logs',
        'webinar_part_payments',
        'accounting',
        'subscriptions',
        'bundles',
        'promotions',
        'registration_packages',
        'gift_cards',
        'installment_order_payments',
        'webinar_translations',
        'bundle_translations'
    ];
    
    echo "📋 Required Tables Status:\n";
    echo "========================\n";
    
    $missingTables = [];
    $existingTables = [];
    
    foreach ($requiredTables as $table) {
        if (in_array($table, $tables)) {
            $existingTables[] = $table;
            echo "✅ $table\n";
            
            // Check record count
            $stmt = $pdo->query("SELECT COUNT(*) as count FROM `$table`");
            $count = $stmt->fetch(PDO::FETCH_ASSOC);
            echo "   Records: " . $count['count'] . "\n";
        } else {
            $missingTables[] = $table;
            echo "❌ $table (MISSING)\n";
        }
    }
    
    echo "\n📊 Summary:\n";
    echo "===========\n";
    echo "Existing tables: " . count($existingTables) . "\n";
    echo "Missing tables: " . count($missingTables) . "\n";
    
    if (!empty($missingTables)) {
        echo "\n❌ Missing tables that will cause errors:\n";
        foreach ($missingTables as $table) {
            echo "  - $table\n";
        }
        
        echo "\n🔧 The financial/sales page will fail because these tables are missing.\n";
        echo "📝 You need to run migrations or import the complete database.\n";
    } else {
        echo "\n✅ All required tables exist! The page should work.\n";
    }
    
    // Test the main query from SaleController
    echo "\n🧪 Testing main sales query...\n";
    try {
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM sales WHERE product_order_id IS NULL AND status IS NULL");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "✅ Main sales query works: " . $result['count'] . " records\n";
    } catch (Exception $e) {
        echo "❌ Main sales query failed: " . $e->getMessage() . "\n";
    }
    
} catch (PDOException $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "\n";
}
?>
