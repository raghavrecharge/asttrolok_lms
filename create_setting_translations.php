<?php
try {
    $pdo = new PDO("mysql:host=127.0.0.1;port=3306;dbname=asttrolok_live", 'root', 'root123');
    
    echo "Database connected successfully!\n";
    
    // Create setting_translations table
    $pdo->exec("
        CREATE TABLE `setting_translations` (
            `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
            `setting_id` int UNSIGNED NOT NULL,
            `locale` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
            `value` longtext COLLATE utf8mb4_unicode_ci,
            `created_at` timestamp NULL DEFAULT NULL,
            `updated_at` timestamp NULL DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `setting_translations_setting_id_foreign` (`setting_id`),
            KEY `setting_translations_locale_index` (`locale`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    
    echo "Setting_translations table created!\n";
    
    // Verify
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM setting_translations");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Setting_translations records: " . $result['count'] . "\n";
    
    echo "✅ Setting_translations table created successfully!\n";
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
