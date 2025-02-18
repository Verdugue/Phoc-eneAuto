-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1:3306
-- Généré le : lun. 17 fév. 2025 à 15:21
-- Version du serveur : 8.3.0
-- Version de PHP : 8.2.18

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `phoceenne_auto`
--

-- --------------------------------------------------------

--
-- Structure de la table `customers`
--

DROP TABLE IF EXISTS `customers`;
CREATE TABLE IF NOT EXISTS `customers` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text,
  `postal_code` varchar(10) DEFAULT NULL,
  `city` varchar(50) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `is_active` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=MyISAM AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `customers`
--

INSERT INTO `customers` (`id`, `first_name`, `last_name`, `email`, `phone`, `address`, `postal_code`, `city`, `created_at`, `updated_at`, `is_active`) VALUES
(1, 'Jean', 'Dupont', 'jean.dupont@email.com', '0601020304', '123 rue de la République', '13001', 'Marseille', '2025-02-17 10:05:43', '2025-02-17 10:05:43', 1),
(2, 'Marie', 'Martin', 'marie.martin@email.com', '0607080910', '45 avenue du Prado', '13008', 'Marseille', '2025-02-17 10:05:43', '2025-02-17 10:05:43', 1),
(3, 'Sophie', 'Laurent', 'sophie.laurent@email.com', '0607080910', '78 avenue des Gobelins', '75013', 'Paris', '2025-02-17 10:05:43', '2025-02-17 10:05:43', 1),
(4, 'Thomas', 'Bernard', 'thomas.bernard@email.com', '0708091011', '45 rue de la Paix', '69002', 'Lyon', '2025-02-17 10:05:43', '2025-02-17 10:05:43', 1),
(5, 'Julie', 'Moreau', 'julie.moreau@email.com', '0809101112', '23 boulevard des Fleurs', '33000', 'Bordeaux', '2025-02-17 10:05:43', '2025-02-17 10:05:43', 1),
(6, 'Nicolas', 'Petit', 'nicolas.petit@email.com', '0910111213', '12 rue du Commerce', '44000', 'Nantes', '2025-02-17 10:05:43', '2025-02-17 10:05:43', 1),
(7, 'Emma', 'Leroy', 'emma.leroy@email.com', '0607080910', '56 avenue Foch', '67000', 'Strasbourg', '2025-02-17 10:05:43', '2025-02-17 10:05:43', 1),
(11, 'Eliott', 'Bellais', 'swazed2004@gmail.com', '0768019719', '225A Les Plantiers II', '13510', 'Éguilles', '2025-02-17 13:42:27', '2025-02-17 13:42:27', 1);

-- --------------------------------------------------------

--
-- Structure de la table `customer_documents`
--

DROP TABLE IF EXISTS `customer_documents`;
CREATE TABLE IF NOT EXISTS `customer_documents` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `customer_id` int DEFAULT NULL,
  `document_type` varchar(50) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `uploaded_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `customer_documents`
--

INSERT INTO `customer_documents` (`id`, `customer_id`, `document_type`, `file_name`, `file_path`, `uploaded_at`) VALUES
(1, 8, 'identity_document', '67b33b4114785.jpg', '/uploads/customers/8/67b33b4114785.jpg', '2025-02-17 13:36:01'),
(2, 9, 'identity_document', '67b33c638174e.jpg', '/uploads/customers/9/67b33c638174e.jpg', '2025-02-17 13:40:51'),
(3, 11, 'identity_document', '67b33cc38b576.jpg', '/uploads/customers/11/67b33cc38b576.jpg', '2025-02-17 13:42:27');

-- --------------------------------------------------------

--
-- Structure de la table `documents`
--

DROP TABLE IF EXISTS `documents`;
CREATE TABLE IF NOT EXISTS `documents` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `transaction_id` int DEFAULT NULL,
  `document_type` varchar(20) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `uploaded_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `documents`
--

INSERT INTO `documents` (`id`, `transaction_id`, `document_type`, `file_name`, `file_path`, `uploaded_at`) VALUES
(1, 30, 'facture', '67b33cda7fd0f.pdf', '/uploads/transactions/30/67b33cda7fd0f.pdf', '2025-02-17 13:42:50'),
(2, 31, 'carte_grise', '67b34848cb289.pdf', '/uploads/transactions/31/67b34848cb289.pdf', '2025-02-17 14:31:36');

-- --------------------------------------------------------

--
-- Structure de la table `suppliers`
--

DROP TABLE IF EXISTS `suppliers`;
CREATE TABLE IF NOT EXISTS `suppliers` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `contact_name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text,
  `postal_code` varchar(10) DEFAULT NULL,
  `city` varchar(50) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `is_active` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `suppliers`
--

INSERT INTO `suppliers` (`id`, `name`, `contact_name`, `email`, `phone`, `address`, `postal_code`, `city`, `created_at`, `updated_at`, `is_active`) VALUES
(1, 'Auto Premium Import', 'Pierre Dubois', 'contact@autopremium.fr', '0491234567', '123 Avenue des Importateurs', '13008', 'Marseille', '2025-02-17 10:05:44', '2025-02-17 10:05:44', 1),
(2, 'Luxury Cars Europe', 'Marie Lambert', 'info@luxurycars.eu', '0478901234', '45 Rue du Commerce', '69002', 'Lyon', '2025-02-17 10:05:44', '2025-02-17 10:05:44', 1),
(3, 'Electric Vehicle Direct', 'Jean Martin', 'sales@evdirect.fr', '0155667788', '78 Boulevard Voltaire', '75011', 'Paris', '2025-02-17 10:05:44', '2025-02-17 10:05:44', 1),
(4, 'Sport Auto Distribution', 'Sophie Moreau', 'contact@sportauto.fr', '0607080910', '12 Rue des Sports', '13006', 'Marseille', '2025-02-17 10:05:44', '2025-02-17 10:05:44', 1),
(5, 'Eco Motors France', 'Lucas Bernard', 'info@ecomotors.fr', '0456789012', '34 Avenue Écologique', '33000', 'Bordeaux', '2025-02-17 10:05:44', '2025-02-17 10:12:06', 1),
(6, 'Eliott', 'Bellais', 'swazed2004@gmail.com', '0768019719', '225A Les Plantiers II', '13510', 'Éguilles', '2025-02-17 10:08:57', '2025-02-17 10:10:34', 1);

-- --------------------------------------------------------

--
-- Structure de la table `transactions`
--

DROP TABLE IF EXISTS `transactions`;
CREATE TABLE IF NOT EXISTS `transactions` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `customer_id` int DEFAULT NULL,
  `vehicle_id` int DEFAULT NULL,
  `user_id` int DEFAULT NULL,
  `transaction_type` varchar(20) DEFAULT NULL,
  `transaction_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `price` decimal(10,2) NOT NULL,
  `payment_method` varchar(20) DEFAULT NULL,
  `invoice_number` varchar(50) DEFAULT NULL,
  `notes` text,
  `status` VARCHAR(20) DEFAULT 'completed',
  `identifier` varchar(50) DEFAULT NULL,
  `brand` varchar(50) DEFAULT NULL,
  `model` varchar(50) DEFAULT NULL,
  `version` varchar(100) DEFAULT NULL,
  `color` varchar(50) DEFAULT NULL,
  `vin_number` varchar(17) DEFAULT NULL,
  `registration_number` varchar(20) DEFAULT NULL,
  `mileage` int DEFAULT NULL,
  `registration_date` date DEFAULT NULL,
  `fees` decimal(10,2) DEFAULT 0.00,
  `location` varchar(100) DEFAULT NULL,
  `options` text DEFAULT NULL,
  `payment_type` varchar(20) DEFAULT 'full' COMMENT 'full, monthly',
  `installments` int DEFAULT 1 COMMENT 'nombre de mensualités',
  `first_payment_date` date DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`),
  UNIQUE KEY `invoice_number` (`invoice_number`),
  UNIQUE KEY `vin_number` (`vin_number`),
  UNIQUE KEY `registration_number` (`registration_number`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `transactions`
--

INSERT INTO `transactions` (`id`, `customer_id`, `vehicle_id`, `user_id`, `transaction_type`, `transaction_date`, `price`, `payment_method`, `invoice_number`, `notes`, `status`, `identifier`, `brand`, `model`, `version`, `color`, `vin_number`, `registration_number`, `mileage`, `registration_date`, `fees`, `location`, `options`, `payment_type`, `installments`, `first_payment_date`) VALUES
(1, 1, 3, 1, 'sale', '2023-07-05 08:30:00', 89900.00, 'card', 'INV-2024-002', 'Vente BMW M4', 'completed', NULL, 'BMW', 'M4 Competition', NULL, 'Bleu San Marino', 'WBA3R1C58EK234567', 'AB-123-CD', 15000, '2023-07-05', 0.00, NULL, NULL, 'full', 1, NULL),
(2, 2, 5, 2, 'sale', '2023-07-12 12:15:00', 189900.00, 'transfer', 'INV-2024-003', 'Vente Porsche Taycan', 'completed', NULL, 'Porsche', 'Taycan Turbo S', NULL, 'Rouge Carmin', 'WP0ZZZ29ZPS123456', 'QR-345-ST', 1200, '2023-07-12', 0.00, NULL, NULL, 'full', 1, NULL),
(3, 1, 7, 1, 'sale', '2023-07-20 09:45:00', 24900.00, 'card', 'INV-2024-004', 'Vente Clio RS', 'completed', NULL, 'Renault', 'Clio RS Line', NULL, 'Orange Valencia', 'VF15RJL0H12345678', 'YZ-901-AB', 8500, '2023-07-20', 0.00, NULL, NULL, 'full', 1, NULL),
(4, 2, 9, 2, 'sale', '2023-07-28 14:20:00', 149900.00, 'transfer', 'INV-2024-005', 'Vente Range Rover', 'completed', NULL, 'Range Rover', 'Sport P530 V8', NULL, 'Noir Santorini', 'SALGA2BG7EA123456', 'GH-567-IJ', 100, '2023-07-28', 0.00, NULL, NULL, 'full', 1, NULL),
(5, 1, 1, 1, 'sale', '2023-08-03 07:30:00', 158900.00, 'transfer', 'INV-2024-006', 'Vente Mercedes AMG GT', 'completed', NULL, 'Mercedes-Benz', 'AMG GT', NULL, 'Noir Obsidienne', 'WDDLJ7GB5EA123457', 'EF-456-GH', 1500, '2023-08-03', 0.00, NULL, NULL, 'full', 1, NULL),
(6, 2, 4, 2, 'sale', '2023-08-10 13:45:00', 129900.00, 'card', 'INV-2024-007', 'Vente Tesla Model S', 'completed', NULL, 'Tesla', 'Model S Plaid', NULL, 'Blanc Nacré', '5YJSA1E47MF123457', 'MN-012-OP', 500, '2023-08-10', 0.00, NULL, NULL, 'full', 1, NULL),
(7, 1, 6, 1, 'sale', '2023-08-18 11:20:00', 38900.00, 'card', 'INV-2024-008', 'Vente Golf GTI', 'completed', NULL, 'Volkswagen', 'Golf 8 GTI', NULL, 'Gris Dauphin', 'WVWZZZAUZMP123457', 'UV-678-WX', 25000, '2023-08-18', 0.00, NULL, NULL, 'full', 1, NULL),
(8, 2, 11, 2, 'sale', '2023-08-25 08:15:00', 42900.00, 'cash', 'INV-2024-009', 'Vente Toyota RAV4', 'completed', NULL, 'Toyota', 'RAV4 Hybride', NULL, 'Blanc Lunaire', 'JTMDDREV20D123457', 'OP-124-QR', 12000, '2023-08-25', 0.00, NULL, NULL, 'full', 1, NULL),
(9, 1, 2, 1, 'sale', '2023-09-02 09:30:00', 189500.00, 'transfer', 'INV-2024-010', 'Vente Porsche 911', 'completed', NULL, 'Porsche', '911 GT3', NULL, 'Gris Argent', 'WP0ZZZ99ZTS392818', 'EF-457-GH', 0, '2023-09-02', 0.00, NULL, NULL, 'full', 1, NULL),
(10, 2, 8, 2, 'sale', '2023-09-09 12:45:00', 32900.00, 'card', 'INV-2024-011', 'Vente Peugeot 308', 'completed', NULL, 'Peugeot', '308 GT', NULL, 'Bleu Vertigo', 'VF3LBHZMGHS123457', 'CD-235-EF', 18000, '2023-09-09', 0.00, NULL, NULL, 'full', 1, NULL),
(11, 1, 10, 1, 'sale', '2023-09-16 14:20:00', 159900.00, 'transfer', 'INV-2024-012', 'Vente Audi RS Q8', 'completed', NULL, 'Audi', 'RS Q8', NULL, 'Gris Nardo', 'WAUZZZF18N123457', 'KL-891-MN', 5000, '2023-09-16', 0.00, NULL, NULL, 'full', 1, NULL),
(12, 2, 12, 2, 'sale', '2023-09-23 07:15:00', 69900.00, 'card', 'INV-2024-013', 'Vente Lexus NX', 'completed', NULL, 'Lexus', 'NX 450h+', NULL, 'Gris Mercure', 'JTJBARBZ502123457', 'ST-457-UV', 8000, '2023-09-23', 0.00, NULL, NULL, 'full', 1, NULL),
(13, 1, 13, 1, 'sale', '2023-09-30 11:45:00', 79900.00, 'transfer', 'INV-2024-014', 'Vente Alpine A110', 'completed', NULL, 'Alpine', 'A110 S', NULL, 'Bleu Alpine', 'VF3MLVF00N1123457', 'WX-790-YZ', 3500, '2023-09-30', 0.00, NULL, NULL, 'full', 1, NULL),
(14, 2, 14, 2, 'sale', '2023-10-06 08:30:00', 219900.00, 'transfer', 'INV-2024-015', 'Vente Nissan GT-R', 'completed', NULL, 'Nissan', 'GT-R Nismo', NULL, 'Blanc Pearl', 'JN1GANR35U0123457', 'AB-013-CD', 9000, '2023-10-06', 0.00, NULL, NULL, 'full', 1, NULL),
(15, 1, 15, 1, 'sale', '2023-10-13 13:20:00', 42900.00, 'card', 'INV-2024-016', 'Vente VW Transporter', 'completed', NULL, 'Volkswagen', 'Transporter T6.1', NULL, 'Blanc Candy', 'WV1ZZZ7HZNH123457', 'EF-347-GH', 15000, '2023-10-13', 0.00, NULL, NULL, 'full', 1, NULL),
(16, 2, 16, 2, 'sale', '2023-10-20 09:45:00', 32900.00, 'cash', 'INV-2024-017', 'Vente Ford Transit', 'completed', NULL, 'Ford', 'Transit Custom', NULL, 'Gris Magnetic', 'WF0VXXBDFV1234568', 'IJ-679-KL', 28000, '2023-10-20', 0.00, NULL, NULL, 'full', 1, NULL),
(17, 1, 3, 1, 'sale', '2023-10-27 12:30:00', 85000.00, 'transfer', 'INV-2024-018', 'Vente BMW M4', 'completed', NULL, 'BMW', 'M4 Competition', NULL, 'Bleu San Marino', 'WBA3R1C58EK234568', 'IJ-780-KL', 15000, '2023-10-27', 0.00, NULL, NULL, 'full', 1, NULL),
(18, 2, 1, 2, 'sale', '2023-11-04 08:15:00', 155000.00, 'transfer', 'INV-2024-019', 'Vente Mercedes AMG', 'completed', NULL, 'Mercedes-Benz', 'AMG GT', NULL, 'Noir Obsidienne', 'WDDLJ7GB5EA123458', 'EF-459-GH', 1500, '2023-11-04', 0.00, NULL, NULL, 'full', 1, NULL),
(19, 1, 4, 1, 'sale', '2023-11-11 15:45:00', 125000.00, 'card', 'INV-2024-020', 'Vente Tesla Model S', 'completed', NULL, 'Tesla', 'Model S Plaid', NULL, 'Blanc Nacré', '5YJSA1E47MF123458', 'MN-013-OP', 500, '2023-11-11', 0.00, NULL, NULL, 'full', 1, NULL),
(20, 2, 7, 2, 'sale', '2023-11-18 12:30:00', 23500.00, 'cash', 'INV-2024-021', 'Vente Clio RS', 'completed', NULL, 'Renault', 'Clio RS Line', NULL, 'Orange Valencia', 'VF15RJL0H12345679', 'YZ-902-AB', 8500, '2023-11-18', 0.00, NULL, NULL, 'full', 1, NULL),
(21, 1, 10, 1, 'sale', '2023-11-25 09:20:00', 155000.00, 'transfer', 'INV-2024-022', 'Vente Audi RS Q8', 'completed', NULL, 'Audi', 'RS Q8', NULL, 'Gris Nardo', 'WAUZZZF18N123458', 'KL-892-MN', 5000, '2023-11-25', 0.00, NULL, NULL, 'full', 1, NULL),
(22, 2, 2, 2, 'sale', '2023-12-01 10:30:00', 185000.00, 'transfer', 'INV-2023-023', 'Vente Porsche 911', 'completed', NULL, 'Porsche', '911 GT3', NULL, 'Gris Argent', 'WP0ZZZ99ZTS392819', 'EF-460-GH', 0, '2023-12-01', 0.00, NULL, NULL, 'full', 1, NULL),
(23, 1, 5, 1, 'sale', '2023-12-08 13:15:00', 185000.00, 'card', 'INV-2023-024', 'Vente Porsche Taycan', 'completed', NULL, 'Porsche', 'Taycan Turbo S', NULL, 'Rouge Carmin', 'WP0ZZZ29ZPS123457', 'QR-346-ST', 1200, '2023-12-08', 0.00, NULL, NULL, 'full', 1, NULL),
(24, 2, 13, 2, 'sale', '2023-12-15 15:45:00', 77500.00, 'transfer', 'INV-2023-025', 'Vente Alpine A110', 'completed', NULL, 'Alpine', 'A110 S', NULL, 'Bleu Alpine', 'VF3MLVF00N1123458', 'WX-791-YZ', 3500, '2023-12-15', 0.00, NULL, NULL, 'full', 1, NULL),
(25, 1, 11, 1, 'sale', '2023-12-20 08:30:00', 41500.00, 'card', 'INV-2023-026', 'Vente Toyota RAV4', 'completed', NULL, 'Toyota', 'RAV4 Hybride', NULL, 'Blanc Lunaire', 'JTMDDREV20D123458', 'OP-125-QR', 12000, '2023-12-20', 0.00, NULL, NULL, 'full', 1, NULL),
(26, 4, 16, 2, 'sale', '2025-02-17 10:47:57', 32900.00, 'card', 'INV-2025-8696', 'la voiture bleu', 'completed', NULL, 'Peugeot', '308', NULL, 'Bleu Vertigo', 'VF3LBHZMGHS123458', 'CD-236-EF', 18000, '2025-02-17', 0.00, NULL, NULL, 'full', 1, NULL),
(27, 4, 17, 2, 'sale', '2025-02-17 10:54:31', 999999.99, 'card', 'INV-2025-4340', 'la voiture bleu', 'completed', NULL, 'Omolon', 'XURF Board', NULL, 'Néon Chromé', 'DSTNY2SPARROW778', 'XU-RF-778', 0, '2025-02-17', 0.00, NULL, NULL, 'full', 1, NULL),
(28, 1, 13, 1, 'purchase', '2025-02-17 11:13:32', 79900.00, 'card', 'INV-2025-9916', 'jj', 'completed', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.00, NULL, NULL, 'full', 1, NULL),
(29, 9, 3, 1, 'sale', '2025-02-17 13:41:33', 89900.00, 'card', 'INV-2025-5824', '', 'completed', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.00, NULL, NULL, 'full', 1, NULL),
(30, 11, 23, 1, 'sale', '2025-02-17 13:42:50', 35900.00, 'card', 'INV-2025-5077', '', 'completed', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.00, NULL, NULL, 'full', 1, NULL),
(31, 1, 22, 1, 'sale', '2025-02-17 14:31:36', 19900.00, 'card', 'INV-2025-7953', 'negro', 'completed', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.00, NULL, NULL, 'full', 1, NULL);

-- --------------------------------------------------------

--
-- Structure de la table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `role` varchar(20) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `profile_image` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ;

--
-- Déchargement des données de la table `users`
--

INSERT INTO `users` (`id`, `username`, `password_hash`, `email`, `role`, `created_at`, `profile_image`) VALUES
(1, 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@phoceenne-auto.fr', 'admin', '2025-02-17 10:05:43', '/uploads/profiles/profile_67b35191eb6a9.jpg'),
(2, 'eliott', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'vendeur1@phoceenne-auto.fr', 'employee', '2025-02-17 10:05:43', 'uploads/profiles/67b3175c0a7cd.jpg');

-- --------------------------------------------------------

--
-- Structure de la table `vehicles`
--

DROP TABLE IF EXISTS `vehicles`;
CREATE TABLE IF NOT EXISTS `vehicles` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `brand` varchar(50) NOT NULL,
  `model` varchar(50) NOT NULL,
  `year` int NOT NULL,
  `mileage` int NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `vehicle_condition` varchar(20) DEFAULT NULL,
  `color` varchar(30) DEFAULT NULL,
  `fuel_type` varchar(20) DEFAULT NULL,
  `transmission` varchar(20) DEFAULT NULL,
  `registration_number` varchar(20) DEFAULT NULL,
  `vin_number` varchar(17) DEFAULT NULL,
  `status` varchar(20) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `supplier_id` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`),
  UNIQUE KEY `registration_number` (`registration_number`),
  UNIQUE KEY `vin_number` (`vin_number`)
) ;

--
-- Déchargement des données de la table `vehicles`
--

INSERT INTO `vehicles` (`id`, `brand`, `model`, `year`, `mileage`, `price`, `vehicle_condition`, `color`, `fuel_type`, `transmission`, `registration_number`, `vin_number`, `status`, `created_at`, `updated_at`, `supplier_id`) VALUES
(1, 'Mercedes-Benz', 'AMG GT', 2023, 1500, 158900.00, 'new', 'Noir Obsidienne', 'Essence', 'Automatique', 'AB-123-CD', 'WDDLJ7GB5EA123456', 'available', '2025-02-17 10:05:43', '2025-02-17 13:07:57', 1),
(2, 'Porsche', '911 GT3', 2023, 0, 189500.00, 'new', 'Gris Argent', 'Essence', 'Automatique', 'EF-456-GH', 'WP0ZZZ99ZTS392817', 'available', '2025-02-17 10:05:43', '2025-02-17 10:05:43', 2),
(3, 'BMW', 'M4 Competition', 2022, 15000, 89900.00, 'used', 'Bleu San Marino', 'Essence', 'Automatique', 'IJ-789-KL', 'WBA3R1C58EK234567', 'sold', '2025-02-17 10:05:43', '2025-02-17 10:05:43', 1),
(4, 'Tesla', 'Model S Plaid', 2023, 500, 129900.00, 'new', 'Blanc Nacré', 'Électrique', 'Automatique', 'MN-012-OP', '5YJSA1E47MF123456', 'available', '2025-02-17 10:05:43', '2025-02-17 10:05:43', 3),
(5, 'Porsche', 'Taycan Turbo S', 2023, 1200, 189900.00, 'new', 'Rouge Carmin', 'Électrique', 'Automatique', 'QR-345-ST', 'WP0ZZZ29ZPS123456', 'available', '2025-02-17 10:05:43', '2025-02-17 10:05:43', 2),
(6, 'Volkswagen', 'Golf 8 GTI', 2022, 25000, 38900.00, 'used', 'Gris Dauphin', 'Essence', 'Manuelle', 'UV-678-WX', 'WVWZZZAUZMP123456', 'available', '2025-02-17 10:05:43', '2025-02-17 10:05:43', NULL),
(7, 'Renault', 'Clio RS Line', 2023, 8500, 24900.00, 'used', 'Orange Valencia', 'Essence', 'Manuelle', 'YZ-901-AB', 'VF15RJL0H12345678', 'sold', '2025-02-17 10:05:43', '2025-02-17 10:05:43', NULL),
(8, 'Peugeot', '308 GT', 2022, 18000, 32900.00, 'used', 'Bleu Vertigo', 'Diesel', 'Automatique', 'CD-234-EF', 'VF3LBHZMGHS123456', 'sold', '2025-02-17 10:05:43', '2025-02-17 10:05:43', NULL),
(9, 'Range Rover', 'Sport P530 V8', 2023, 100, 149900.00, 'new', 'Noir Santorini', 'Essence', 'Automatique', 'GH-567-IJ', 'SALGA2BG7EA123456', 'sold', '2025-02-17 10:05:43', '2025-02-17 10:05:43', 2),
(10, 'Audi', 'RS Q8', 2023, 5000, 159900.00, 'used', 'Gris Nardo', 'Essence', 'Automatique', 'KL-890-MN', 'WAUZZZF18N123456', 'sold', '2025-02-17 10:05:43', '2025-02-17 10:05:43', NULL),
(11, 'Toyota', 'RAV4 Hybride', 2023, 12000, 42900.00, 'used', 'Blanc Lunaire', 'Hybride', 'Automatique', 'OP-123-QR', 'JTMDDREV20D123456', 'sold', '2025-02-17 10:05:43', '2025-02-17 10:05:43', 5),
(12, 'Lexus', 'NX 450h+', 2023, 8000, 69900.00, 'used', 'Gris Mercure', 'Hybride', 'Automatique', 'ST-456-UV', 'JTJBARBZ502123456', 'sold', '2025-02-17 10:05:43', '2025-02-17 10:05:43', 5),
(13, 'Alpine', 'A110 S', 2023, 3500, 79900.00, 'used', 'Bleu Alpine', 'Essence', 'Automatique', 'WX-789-YZ', 'VF3MLVF00N1123456', 'sold', '2025-02-17 10:05:43', '2025-02-17 11:13:11', 4),
(14, 'Nissan', 'GT-R Nismo', 2022, 9000, 219900.00, 'used', 'Blanc Pearl', 'Essence', 'Automatique', 'AB-012-CD', 'JN1GANR35U0123456', 'available', '2025-02-17 10:05:43', '2025-02-17 10:05:43', 4),
(15, 'Volkswagen', 'Transporter T6.1', 2023, 15000, 42900.00, 'used', 'Blanc Candy', 'Diesel', 'Manuelle', 'EF-346-GH', 'WV1ZZZ7HZNH123456', 'available', '2025-02-17 10:05:43', '2025-02-17 10:05:43', NULL),
(16, 'Ford', 'Transit Custom', 2022, 28000, 32900.00, 'used', 'Gris Magnetic', 'Diesel', 'Manuelle', 'IJ-678-KL', 'WF0VXXBDFV1234567', 'sold', '2025-02-17 10:05:43', '2025-02-17 10:05:43', NULL),
(17, 'Omolon', 'XURF Board', 2759, 0, 999999.99, 'new', 'Néon Chromé', 'Lumière', 'Automatique', 'XU-RF-777', 'DSTNY2SPARROW777', 'sold', '2025-02-17 10:05:43', '2025-02-17 10:05:43', NULL),
(18, 'Peugeot', '308', 2023, 0, 28900.00, 'new', NULL, 'diesel', 'manual', NULL, NULL, 'available', '2025-02-17 13:10:51', '2025-02-17 13:10:51', NULL),
(19, 'Renault', 'Clio', 2022, 15000, 18500.00, 'used', NULL, 'essence', 'manual', NULL, NULL, 'available', '2025-02-17 13:10:51', '2025-02-17 13:10:51', NULL),
(20, 'Volkswagen', 'Golf', 2023, 0, 32900.00, 'new', NULL, 'hybrid', 'automatic', NULL, NULL, 'available', '2025-02-17 13:10:51', '2025-02-17 13:10:51', NULL),
(21, 'Toyota', 'Yaris', 2022, 8000, 21900.00, 'used', NULL, 'hybrid', 'automatic', NULL, NULL, 'available', '2025-02-17 13:10:51', '2025-02-17 13:10:51', NULL),
(22, 'Citroen', 'C3', 2023, 0, 19900.00, 'new', NULL, 'essence', 'manual', NULL, NULL, 'sold', '2025-02-17 13:10:51', '2025-02-17 13:10:51', NULL),
(23, 'BMW', 'Serie 1', 2022, 12000, 35900.00, 'used', NULL, 'diesel', 'automatic', NULL, NULL, 'sold', '2025-02-17 13:10:51', '2025-02-17 13:10:51', NULL),
(24, 'Peugeot', '308', 2023, 0, 28900.00, 'new', NULL, 'diesel', 'manual', NULL, NULL, 'available', '2025-02-17 13:10:51', '2025-02-17 13:10:51', NULL),
(25, 'Renault', 'Clio', 2022, 15000, 18500.00, 'used', NULL, 'essence', 'manual', NULL, NULL, 'available', '2025-02-17 13:10:51', '2025-02-17 13:10:51', NULL),
(26, 'Volkswagen', 'Golf', 2023, 0, 32900.00, 'new', NULL, 'hybrid', 'automatic', NULL, NULL, 'available', '2025-02-17 13:10:51', '2025-02-17 13:10:51', NULL),
(27, 'Toyota', 'Yaris', 2022, 8000, 21900.00, 'used', NULL, 'hybrid', 'automatic', NULL, NULL, 'available', '2025-02-17 13:10:51', '2025-02-17 13:10:51', NULL),
(28, 'Citroen', 'C3', 2023, 0, 19900.00, 'new', NULL, 'essence', 'manual', NULL, NULL, 'available', '2025-02-17 13:10:51', '2025-02-17 13:10:51', NULL),
(29, 'BMW', 'Serie 1', 2022, 12000, 35900.00, 'used', NULL, 'diesel', 'automatic', NULL, NULL, 'available', '2025-02-17 13:10:51', '2025-02-17 13:10:51', NULL),
(30, 'Peugeot', '308', 2023, 0, 28900.00, 'new', NULL, 'Essence', 'Manuelle', NULL, NULL, 'available', '2025-02-17 13:13:25', '2025-02-17 13:13:25', NULL),
(31, 'Renault', 'Clio', 2022, 15000, 18500.00, 'used', NULL, 'Diesel', 'Manuelle', NULL, NULL, 'available', '2025-02-17 13:13:25', '2025-02-17 13:13:25', NULL),
(32, 'Volkswagen', 'Golf', 2023, 0, 32900.00, 'new', NULL, 'Hybride', 'Automatique', NULL, NULL, 'available', '2025-02-17 13:13:25', '2025-02-17 13:13:25', NULL),
(33, 'Toyota', 'Yaris', 2022, 8000, 21900.00, 'used', NULL, 'Hybride', 'Automatique', NULL, NULL, 'available', '2025-02-17 13:13:25', '2025-02-17 13:13:25', NULL),
(34, 'Citroen', 'C3', 2023, 0, 19900.00, 'new', NULL, 'Essence', 'Manuelle', NULL, NULL, 'available', '2025-02-17 13:13:25', '2025-02-17 13:13:25', NULL),
(35, 'BMW', 'Serie 1', 2022, 12000, 35900.00, 'used', NULL, 'Diesel', 'Automatique', NULL, NULL, 'available', '2025-02-17 13:13:25', '2025-02-17 13:13:25', NULL),
(37, 'Renault', 'Clio', 2022, 15000, 18500.00, 'used', NULL, 'Diesel', 'Manuelle', NULL, NULL, 'available', '2025-02-17 13:13:25', '2025-02-17 13:13:25', NULL),
(38, 'Volkswagen', 'Golf', 2023, 0, 32900.00, 'new', NULL, 'Hybride', 'Automatique', NULL, NULL, 'available', '2025-02-17 13:13:25', '2025-02-17 13:13:25', NULL),
(39, 'Toyota', 'Yaris', 2022, 8000, 21900.00, 'used', NULL, 'Hybride', 'Automatique', NULL, NULL, 'available', '2025-02-17 13:13:25', '2025-02-17 13:13:25', NULL),
(40, 'Citroen', 'C3', 2023, 0, 19900.00, 'new', NULL, 'Essence', 'Manuelle', NULL, NULL, 'available', '2025-02-17 13:13:25', '2025-02-17 13:13:25', NULL),
(41, 'BMW', 'Serie 1', 2022, 12000, 35900.00, 'used', NULL, 'Diesel', 'Automatique', NULL, NULL, 'available', '2025-02-17 13:13:25', '2025-02-17 13:13:25', NULL);

-- --------------------------------------------------------

--
-- Structure de la table `vehicle_images`
--

DROP TABLE IF EXISTS `vehicle_images`;
CREATE TABLE IF NOT EXISTS `vehicle_images` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `vehicle_id` int DEFAULT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `is_primary` tinyint(1) DEFAULT '0',
  `uploaded_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `vehicle_images`
--

INSERT INTO `vehicle_images` (`id`, `vehicle_id`, `file_name`, `file_path`, `is_primary`, `uploaded_at`) VALUES
(1, 13, '67b3198926d8a.jpg', '/uploads/vehicles/13/67b3198926d8a.jpg', 1, '2025-02-17 11:12:09'),
(2, 13, '67b3198927357.webp', '/uploads/vehicles/13/67b3198927357.webp', 0, '2025-02-17 11:12:09'),
(3, 13, '67b31989277f3.jpg', '/uploads/vehicles/13/67b31989277f3.jpg', 0, '2025-02-17 11:12:09'),
(4, 13, '67b3198927d16.jpg', '/uploads/vehicles/13/67b3198927d16.jpg', 0, '2025-02-17 11:12:09'),
(5, 1, '67b32ed4355ff.jpg', '/uploads/vehicles/1/67b32ed4355ff.jpg', 0, '2025-02-17 12:43:00'),
(6, 1, '67b32ed435ac8.jpg', '/uploads/vehicles/1/67b32ed435ac8.jpg', 1, '2025-02-17 12:43:00'),
(7, 36, '67b33b5e131ce.jpg', '/uploads/vehicles/36/67b33b5e131ce.jpg', 1, '2025-02-17 13:36:30'),
(8, 36, '67b33b5e138cf.jpg', '/uploads/vehicles/36/67b33b5e138cf.jpg', 0, '2025-02-17 13:36:30');

-- Mettre à jour les transactions existantes
UPDATE transactions SET status = 'completed' WHERE status IS NULL;
COMMIT;

-- --------------------------------------------------------

--
-- Structure de la table `payments`
--

DROP TABLE IF EXISTS `payments`;
CREATE TABLE IF NOT EXISTS `payments` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `transaction_id` bigint UNSIGNED NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_date` date NOT NULL,
  `status` varchar(20) DEFAULT 'pending',
  `payment_method` varchar(20) DEFAULT NULL,
  `payment_type` varchar(20) DEFAULT 'installment' COMMENT 'down_payment, installment',
  `notes` text,
  PRIMARY KEY (`id`),
  KEY `transaction_id` (`transaction_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Garder uniquement la mise à jour des données existantes
UPDATE `transactions` SET `payment_type` = 'full' WHERE `payment_type` IS NULL;