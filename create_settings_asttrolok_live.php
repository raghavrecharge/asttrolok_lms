<?php
try {
    $pdo = new PDO("mysql:host=127.0.0.1;port=3306;dbname=asttrolok_live", 'root', 'root123');
    
    echo "Database connected successfully!\n";
    
    // Create settings table
    $pdo->exec("
        CREATE TABLE `settings` (
            `id` int UNSIGNED NOT NULL,
            `page` enum('general','financial','sidebanner','personalization','notifications','seo','customization','other') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'other',
            `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
            `updated_at` int DEFAULT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC
    ");
    
    echo "Settings table created!\n";
    
    // Insert basic settings data
    $pdo->exec("
        INSERT INTO `settings` (`id`, `page`, `name`, `updated_at`) VALUES 
        (1, 'seo', 'seo_metas', 1762851475),
        (2, 'general', 'socials', 1703835212),
        (4, 'other', 'footer', 1632071275),
        (5, 'general', 'general', 1763030992),
        (6, 'financial', 'financial', 1692343149),
        (8, 'personalization', 'home_hero', 1673069870),
        (12, 'customization', 'custom_css_js', 1636119881),
        (14, 'personalization', 'page_background', 1704719947),
        (15, 'personalization', 'home_hero2', 1712990386)
    ");
    
    echo "Basic settings data inserted!\n";
    
    // Verify
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM settings");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Settings records: " . $result['count'] . "\n";
    
    echo "✅ Settings table created successfully!\n";
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
