<?php
try {
    $pdo = new PDO("mysql:host=127.0.0.1;port=3306", 'root', 'root123');
    $pdo->exec("CREATE DATABASE IF NOT EXISTS asttrolok_live_db");
    $pdo->exec("USE asttrolok_live_db");
    
    echo "Database connected successfully!\n";
    
    // Drop the incorrect table first
    $pdo->exec("DROP TABLE IF EXISTS settings");
    
    // Create the correct settings table
    $pdo->exec("
        CREATE TABLE `settings` (
            `id` int UNSIGNED NOT NULL,
            `page` enum('general','financial','sidebanner','personalization','notifications','seo','customization','other') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'other',
            `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
            `updated_at` int DEFAULT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC
    ");
    
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
    
    echo "Settings table created and populated!\n";
    
    // Create other essential tables
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `users` (
            `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
            `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
            `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
            `email_verified_at` timestamp NULL DEFAULT NULL,
            `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
            `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
            `created_at` timestamp NULL DEFAULT NULL,
            `updated_at` timestamp NULL DEFAULT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `users_email_unique` (`email`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `migrations` (
            `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
            `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
            `batch` int NOT NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    
    echo "Essential tables created successfully!\n";
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
