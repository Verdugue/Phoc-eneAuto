<?php
require_once 'config/database.php';

try {
    // Supprimer les tables existantes
    $pdo->exec("DROP TABLE IF EXISTS `parking_spots`");
    $pdo->exec("DROP TABLE IF EXISTS `vehicle_images`");
    
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
        `coordinates` varchar(50) DEFAULT NULL,
        `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
        `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        UNIQUE KEY `spot_number` (`spot_number`),
        KEY `vehicle_id` (`vehicle_id`)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci";

    $pdo->exec($sql);

    // Insérer les emplacements par défaut avec leurs coordonnées
    $sql = "INSERT INTO `parking_spots` (`spot_number`, `status`, `coordinates`) VALUES
        ('A1', 'available', '10,10'),
        ('A2', 'available', '10,20'),
        ('A3', 'available', '10,30'),
        ('B1', 'available', '20,10'),
        ('B2', 'available', '20,20'),
        ('B3', 'available', '20,30'),
        ('C1', 'available', '30,10'),
        ('C2', 'available', '30,20'),
        ('C3', 'available', '30,30'),
        ('D1', 'available', '40,10'),
        ('D2', 'available', '40,20'),
        ('D3', 'available', '40,30')";

    $pdo->exec($sql);
    
    echo "Table parking_spots créée avec succès !";

    // Ajouter la colonne coordinates si elle n'existe pas
    $sql = "SHOW COLUMNS FROM parking_spots LIKE 'coordinates'";
    $result = $pdo->query($sql);
    
    if ($result->rowCount() == 0) {
        // La colonne n'existe pas, on l'ajoute
        $sql = "ALTER TABLE parking_spots ADD COLUMN coordinates varchar(50) DEFAULT NULL";
        $pdo->exec($sql);
        
        // Mettre à jour les coordonnées pour les emplacements existants
        $sql = "UPDATE parking_spots SET coordinates = CASE 
            WHEN spot_number = 'A1' THEN '10,10'
            WHEN spot_number = 'A2' THEN '10,20'
            WHEN spot_number = 'A3' THEN '10,30'
            WHEN spot_number = 'B1' THEN '20,10'
            WHEN spot_number = 'B2' THEN '20,20'
            WHEN spot_number = 'B3' THEN '20,30'
            WHEN spot_number = 'C1' THEN '30,10'
            WHEN spot_number = 'C2' THEN '30,20'
            WHEN spot_number = 'C3' THEN '30,30'
            WHEN spot_number = 'D1' THEN '40,10'
            WHEN spot_number = 'D2' THEN '40,20'
            WHEN spot_number = 'D3' THEN '40,30'
            END";
        $pdo->exec($sql);
        
        echo "Colonne coordinates ajoutée et mise à jour avec succès !";
    } else {
        echo "La colonne coordinates existe déjà.";
    }

} catch(PDOException $e) {
    die('Erreur : ' . $e->getMessage());
} 