<?php
require_once 'config/database.php';

try {
    // Créer la table vehicle_images
    $sql = "CREATE TABLE IF NOT EXISTS `vehicle_images` (
        `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
        `vehicle_id` bigint UNSIGNED NOT NULL,
        `file_name` varchar(255) NOT NULL,
        `file_path` varchar(255) NOT NULL,
        `is_primary` tinyint(1) DEFAULT '0',
        `uploaded_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        KEY `vehicle_id` (`vehicle_id`)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci";

    $pdo->exec($sql);
    echo "Table vehicle_images créée avec succès !<br>";

    // Créer la table parking_spots
    $sql = "CREATE TABLE IF NOT EXISTS `parking_spots` (
        `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
        `spot_number` varchar(10) NOT NULL,
        `vehicle_id` bigint UNSIGNED DEFAULT NULL,
        `status` varchar(20) DEFAULT 'available',
        `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
        `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        UNIQUE KEY `spot_number` (`spot_number`),
        KEY `vehicle_id` (`vehicle_id`)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci";

    $pdo->exec($sql);

    // Insérer les emplacements par défaut
    $sql = "INSERT INTO `parking_spots` (`spot_number`, `status`) VALUES
        ('A1', 'available'), ('A2', 'available'), ('A3', 'available'),
        ('B1', 'available'), ('B2', 'available'), ('B3', 'available'),
        ('C1', 'available'), ('C2', 'available'), ('C3', 'available'),
        ('D1', 'available'), ('D2', 'available'), ('D3', 'available')";

    $pdo->exec($sql);
    
    echo "Table parking_spots créée avec succès !";
} catch(PDOException $e) {
    die('Erreur : ' . $e->getMessage());
} 