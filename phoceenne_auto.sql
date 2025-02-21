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
  `country` varchar(50) DEFAULT 'France',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `is_active` tinyint(1) DEFAULT '1',
  `website` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `suppliers`
--

INSERT INTO `suppliers` (`id`, `name`, `contact_name`, `email`, `phone`, `address`, `postal_code`, `city`, `country`, `created_at`, `updated_at`, `is_active`, `website`) VALUES
(1, 'Auto Premium Import', 'Pierre Dubois', 'contact@autopremium.fr', '0491234567', '123 Avenue des Importateurs', '13008', 'Marseille', 'France', '2025-02-17 10:05:44', '2025-02-17 10:05:44', 1, NULL),
(2, 'Luxury Cars Europe', 'Marie Lambert', 'info@luxurycars.eu', '0478901234', '45 Rue du Commerce', '69002', 'Lyon', 'France', '2025-02-17 10:05:44', '2025-02-17 10:05:44', 1, NULL),
(3, 'Electric Vehicle Direct', 'Jean Martin', 'sales@evdirect.fr', '0155667788', '78 Boulevard Voltaire', '75011', 'Paris', 'France', '2025-02-17 10:05:44', '2025-02-17 10:05:44', 1, NULL),
(4, 'Sport Auto Distribution', 'Sophie Moreau', 'contact@sportauto.fr', '0607080910', '12 Rue des Sports', '13006', 'Marseille', 'France', '2025-02-17 10:05:44', '2025-02-17 10:05:44', 1, NULL),
(5, 'Eco Motors France', 'Lucas Bernard', 'info@ecomotors.fr', '0456789012', '34 Avenue Écologique', '33000', 'Bordeaux', 'France', '2025-02-17 10:05:44', '2025-02-17 10:12:06', 1, NULL),
(6, 'Eliott', 'Bellais', 'swazed2004@gmail.com', '0768019719', '225A Les Plantiers II', '13510', 'Éguilles', 'France', '2025-02-17 10:08:57', '2025-02-17 10:10:34', 1, NULL);

-- Mettre à jour les enregistrements existants
UPDATE suppliers SET country = 'France' WHERE country IS NULL;

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
  `registration_date` date DEFAULT NULL,
  `options` text,
  `location` varchar(100) DEFAULT NULL,
  `version` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`),
  UNIQUE KEY `registration_number` (`registration_number`),
  UNIQUE KEY `vin_number` (`vin_number`)
) ENGINE=MyISAM AUTO_INCREMENT=42 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `vehicles`
--

INSERT INTO `vehicles` (`id`, `brand`, `model`, `year`, `mileage`, `price`, `vehicle_condition`, `color`, `fuel_type`, `transmission`, `registration_number`, `vin_number`, `status`, `created_at`, `updated_at`, `supplier_id`, `registration_date`, `options`, `location`, `version`) VALUES
(1, 'Mercedes-Benz', 'AMG GT', 2023, 1500, 158900.00, 'new', 'Noir Obsidienne', 'Essence', 'Automatique', 'AB-123-CD', 'WDDLJ7GB5EA123456', 'available', '2025-02-17 10:05:43', '2025-02-17 13:07:57', 1, '2023-07-05', NULL, NULL, NULL),
(2, 'Porsche', '911 GT3', 2023, 0, 189500.00, 'new', 'Gris Argent', 'Essence', 'Automatique', 'EF-456-GH', 'WP0ZZZ99ZTS392817', 'available', '2025-02-17 10:05:43', '2025-02-17 10:05:43', 2, '2023-07-12', NULL, NULL, NULL),
(3, 'BMW', 'M4 Competition', 2022, 15000, 89900.00, 'used', 'Bleu San Marino', 'Essence', 'Automatique', 'IJ-789-KL', 'WBA3R1C58EK234567', 'sold', '2025-02-17 10:05:43', '2025-02-17 10:05:43', 1, '2023-07-20', NULL, NULL, NULL),
(4, 'Tesla', 'Model S Plaid', 2023, 500, 129900.00, 'new', 'Blanc Nacré', 'Électrique', 'Automatique', 'MN-012-OP', '5YJSA1E47MF123456', 'available', '2025-02-17 10:05:43', '2025-02-17 10:05:43', 3, '2023-08-10', NULL, NULL, NULL),
(5, 'Porsche', 'Taycan Turbo S', 2023, 1200, 189900.00, 'new', 'Rouge Carmin', 'Électrique', 'Automatique', 'QR-345-ST', 'WP0ZZZ29ZPS123456', 'available', '2025-02-17 10:05:43', '2025-02-17 10:05:43', 2, '2023-07-12', NULL, NULL, NULL),
(6, 'Volkswagen', 'Golf 8 GTI', 2022, 25000, 38900.00, 'used', 'Gris Dauphin', 'Essence', 'Manuelle', 'UV-678-WX', 'WVWZZZAUZMP123456', 'available', '2025-02-17 10:05:43', '2025-02-17 10:05:43', NULL, '2023-08-18', NULL, NULL, NULL),
(7, 'Renault', 'Clio RS Line', 2023, 8500, 24900.00, 'used', 'Orange Valencia', 'Essence', 'Manuelle', 'YZ-901-AB', 'VF15RJL0H12345678', 'sold', '2025-02-17 10:05:43', '2025-02-17 10:05:43', NULL, '2023-07-20', NULL, NULL, NULL),
(8, 'Peugeot', '308 GT', 2022, 18000, 32900.00, 'used', 'Bleu Vertigo', 'Diesel', 'Automatique', 'CD-234-EF', 'VF3LBHZMGHS123456', 'sold', '2025-02-17 10:05:43', '2025-02-17 10:05:43', NULL, '2023-09-09', NULL, NULL, NULL),
(9, 'Range Rover', 'Sport P530 V8', 2023, 100, 149900.00, 'new', 'Noir Santorini', 'Essence', 'Automatique', 'GH-567-IJ', 'SALGA2BG7EA123456', 'sold', '2025-02-17 10:05:43', '2025-02-17 10:05:43', 2, '2023-07-28', NULL, NULL, NULL),
(10, 'Audi', 'RS Q8', 2023, 5000, 159900.00, 'used', 'Gris Nardo', 'Essence', 'Automatique', 'KL-890-MN', 'WAUZZZF18N123456', 'sold', '2025-02-17 10:05:43', '2025-02-17 10:05:43', NULL, '2023-09-16', NULL, NULL, NULL),
(11, 'Toyota', 'RAV4 Hybride', 2023, 12000, 42900.00, 'used', 'Blanc Lunaire', 'Hybride', 'Automatique', 'OP-123-QR', 'JTMDDREV20D123456', 'sold', '2025-02-17 10:05:43', '2025-02-17 10:05:43', 5, '2023-08-25', NULL, NULL, NULL),
(12, 'Lexus', 'NX 450h+', 2023, 8000, 69900.00, 'used', 'Gris Mercure', 'Hybride', 'Automatique', 'ST-456-UV', 'JTJBARBZ502123456', 'sold', '2025-02-17 10:05:43', '2025-02-17 10:05:43', 5, '2023-09-23', NULL, NULL, NULL),
(13, 'Alpine', 'A110 S', 2023, 3500, 79900.00, 'used', 'Bleu Alpine', 'Essence', 'Automatique', 'WX-789-YZ', 'VF3MLVF00N1123456', 'sold', '2025-02-17 10:05:43', '2025-02-17 11:13:11', 4, '2023-09-30', NULL, NULL, NULL),
(14, 'Nissan', 'GT-R Nismo', 2022, 9000, 219900.00, 'used', 'Blanc Pearl', 'Essence', 'Automatique', 'AB-012-CD', 'JN1GANR35U0123456', 'available', '2025-02-17 10:05:43', '2025-02-17 10:05:43', 4, '2023-10-06', NULL, NULL, NULL),
(15, 'Volkswagen', 'Transporter T6.1', 2023, 15000, 42900.00, 'used', 'Blanc Candy', 'Diesel', 'Manuelle', 'EF-346-GH', 'WV1ZZZ7HZNH123456', 'available', '2025-02-17 10:05:43', '2025-02-17 10:05:43', NULL, '2023-10-13', NULL, NULL, NULL),
(16, 'Ford', 'Transit Custom', 2022, 28000, 32900.00, 'used', 'Gris Magnetic', 'Diesel', 'Manuelle', 'IJ-678-KL', 'WF0VXXBDFV1234567', 'sold', '2025-02-17 10:05:43', '2025-02-17 10:05:43', NULL, '2023-10-20', NULL, NULL, NULL),
(17, 'Omolon', 'XURF Board', 2759, 0, 999999.99, 'new', 'Néon Chromé', 'Lumière', 'Automatique', 'XU-RF-777', 'DSTNY2SPARROW777', 'sold', '2025-02-17 10:05:43', '2025-02-17 10:05:43', NULL, '2025-02-17', NULL, NULL, NULL),
(18, 'Peugeot', '308', 2023, 0, 28900.00, 'new', NULL, 'diesel', 'manual', NULL, NULL, 'available', '2025-02-17 13:10:51', '2025-02-17 13:10:51', NULL, '2025-02-17', NULL, NULL, NULL),
(19, 'Renault', 'Clio', 2022, 15000, 18500.00, 'used', NULL, 'essence', 'manual', NULL, NULL, 'available', '2025-02-17 13:10:51', '2025-02-17 13:10:51', NULL, '2025-02-17', NULL, NULL, NULL),
(20, 'Volkswagen', 'Golf', 2023, 0, 32900.00, 'new', NULL, 'hybrid', 'automatic', NULL, NULL, 'available', '2025-02-17 13:10:51', '2025-02-17 13:10:51', NULL, '2025-02-17', NULL, NULL, NULL),
(21, 'Toyota', 'Yaris', 2022, 8000, 21900.00, 'used', NULL, 'hybrid', 'automatic', NULL, NULL, 'available', '2025-02-17 13:10:51', '2025-02-17 13:10:51', NULL, '2025-02-17', NULL, NULL, NULL),
(22, 'Citroen', 'C3', 2023, 0, 19900.00, 'new', NULL, 'essence', 'manual', NULL, NULL, 'sold', '2025-02-17 13:10:51', '2025-02-17 13:10:51', NULL, '2025-02-17', NULL, NULL, NULL),
(23, 'BMW', 'Serie 1', 2022, 12000, 35900.00, 'used', NULL, 'diesel', 'automatic', NULL, NULL, 'sold', '2025-02-17 13:10:51', '2025-02-17 13:10:51', NULL, '2025-02-17', NULL, NULL, NULL),
(24, 'Peugeot', '308', 2023, 0, 28900.00, 'new', NULL, 'diesel', 'manual', NULL, NULL, 'available', '2025-02-17 13:10:51', '2025-02-17 13:10:51', NULL, '2025-02-17', NULL, NULL, NULL),
(25, 'Renault', 'Clio', 2022, 15000, 18500.00, 'used', NULL, 'essence', 'manual', NULL, NULL, 'available', '2025-02-17 13:10:51', '2025-02-17 13:10:51', NULL, '2025-02-17', NULL, NULL, NULL),
(26, 'Volkswagen', 'Golf', 2023, 0, 32900.00, 'new', NULL, 'hybrid', 'automatic', NULL, NULL, 'available', '2025-02-17 13:10:51', '2025-02-17 13:10:51', NULL, '2025-02-17', NULL, NULL, NULL),
(27, 'Toyota', 'Yaris', 2022, 8000, 21900.00, 'used', NULL, 'hybrid', 'automatic', NULL, NULL, 'available', '2025-02-17 13:10:51', '2025-02-17 13:10:51', NULL, '2025-02-17', NULL, NULL, NULL),
(28, 'Citroen', 'C3', 2023, 0, 19900.00, 'new', NULL, 'essence', 'manual', NULL, NULL, 'available', '2025-02-17 13:10:51', '2025-02-17 13:10:51', NULL, '2025-02-17', NULL, NULL, NULL),
(29, 'BMW', 'Serie 1', 2022, 12000, 35900.00, 'used', NULL, 'diesel', 'automatic', NULL, NULL, 'available', '2025-02-17 13:10:51', '2025-02-17 13:10:51', NULL, '2025-02-17', NULL, NULL, NULL),
(30, 'Peugeot', '308', 2023, 0, 28900.00, 'new', NULL, 'Essence', 'Manuelle', NULL, NULL, 'available', '2025-02-17 13:13:25', '2025-02-17 13:13:25', NULL, '2025-02-17', NULL, NULL, NULL),
(31, 'Renault', 'Clio', 2022, 15000, 18500.00, 'used', NULL, 'Diesel', 'Manuelle', NULL, NULL, 'available', '2025-02-17 13:13:25', '2025-02-17 13:13:25', NULL, '2025-02-17', NULL, NULL, NULL),
(32, 'Volkswagen', 'Golf', 2023, 0, 32900.00, 'new', NULL, 'Hybride', 'Automatique', NULL, NULL, 'available', '2025-02-17 13:13:25', '2025-02-17 13:13:25', NULL, '2025-02-17', NULL, NULL, NULL),
(33, 'Toyota', 'Yaris', 2022, 8000, 21900.00, 'used', NULL, 'Hybride', 'Automatique', NULL, NULL, 'available', '2025-02-17 13:13:25', '2025-02-17 13:13:25', NULL, '2025-02-17', NULL, NULL, NULL),
(34, 'Citroen', 'C3', 2023, 0, 19900.00, 'new', NULL, 'Essence', 'Manuelle', NULL, NULL, 'available', '2025-02-17 13:13:25', '2025-02-17 13:13:25', NULL, '2025-02-17', NULL, NULL, NULL),
(35, 'BMW', 'Serie 1', 2022, 12000, 35900.00, 'used', NULL, 'Diesel', 'Automatique', NULL, NULL, 'available', '2025-02-17 13:13:25', '2025-02-17 13:13:25', NULL, '2025-02-17', NULL, NULL, NULL),
(37, 'Renault', 'Clio', 2022, 15000, 18500.00, 'used', NULL, 'Diesel', 'Manuelle', NULL, NULL, 'available', '2025-02-17 13:13:25', '2025-02-17 13:13:25', NULL, '2025-02-17', NULL, NULL, NULL),
(38, 'Volkswagen', 'Golf', 2023, 0, 32900.00, 'new', NULL, 'Hybride', 'Automatique', NULL, NULL, 'available', '2025-02-17 13:13:25', '2025-02-17 13:13:25', NULL, '2025-02-17', NULL, NULL, NULL),
(39, 'Toyota', 'Yaris', 2022, 8000, 21900.00, 'used', NULL, 'Hybride', 'Automatique', NULL, NULL, 'available', '2025-02-17 13:13:25', '2025-02-17 13:13:25', NULL, '2025-02-17', NULL, NULL, NULL),
(40, 'Citroen', 'C3', 2023, 0, 19900.00, 'new', NULL, 'Essence', 'Manuelle', NULL, NULL, 'available', '2025-02-17 13:13:25', '2025-02-17 13:13:25', NULL, '2025-02-17', NULL, NULL, NULL),
(41, 'BMW', 'Serie 1', 2022, 12000, 35900.00, 'used', NULL, 'Diesel', 'Automatique', NULL, NULL, 'available', '2025-02-17 13:13:25', '2025-02-17 13:13:25', NULL, '2025-02-17', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Structure de la table `transactions`
--

DROP TABLE IF EXISTS `transactions`;
CREATE TABLE IF NOT EXISTS `transactions` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `customer_id` bigint UNSIGNED NOT NULL,
  `vehicle_id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `transaction_type` varchar(20) NOT NULL COMMENT 'sale, purchase',
  `transaction_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `price` decimal(10,2) NOT NULL,
  `payment_method` varchar(20) NOT NULL,
  `payment_type` varchar(20) NOT NULL COMMENT 'full, monthly',
  `invoice_number` varchar(50) NOT NULL,
  `notes` text,
  `status` varchar(20) DEFAULT 'completed',
  -- Informations du véhicule
  `brand` varchar(50) NOT NULL,
  `model` varchar(50) NOT NULL,
  `version` varchar(100) DEFAULT NULL,
  `year` int NOT NULL,
  `color` varchar(30) DEFAULT NULL,
  `fuel_type` varchar(20) DEFAULT NULL,
  `transmission` varchar(20) DEFAULT NULL,
  `mileage` int NOT NULL,
  `registration_number` varchar(20) DEFAULT NULL,
  `vin_number` varchar(17) DEFAULT NULL,
  `registration_date` date DEFAULT NULL,
  `options` text,
  `location` varchar(100) DEFAULT NULL,
  -- Pour les paiements mensuels
  `installments` int DEFAULT NULL,
  `first_payment_date` date DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`),
  UNIQUE KEY `invoice_number` (`invoice_number`),
  KEY `customer_id` (`customer_id`),
  KEY `vehicle_id` (`vehicle_id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `transactions`
--

INSERT INTO `transactions` (
    `id`, `customer_id`, `vehicle_id`, `user_id`, `transaction_type`, 
    `transaction_date`, `price`, `payment_method`, `payment_type`, 
    `invoice_number`, `notes`, `status`, `brand`, `model`, `version`, 
    `year`, `color`, `fuel_type`, `transmission`, `mileage`, 
    `registration_number`, `vin_number`, `registration_date`, `options`, 
    `location`, `installments`, `first_payment_date`
) VALUES 
(28, 1, 13, 1, 'purchase', '2025-02-17 11:13:32', 79900.00, 'card', 'full', 
 'INV-2025-9916', 'jj', 'completed', 'Alpine', 'A110 S', NULL, 2023, 
 'Bleu Alpine', 'Essence', 'Automatique', 3500, 'WX-789-YZ', 
 'VF3MLVF00N1123456', '2023-09-30', NULL, NULL, NULL, NULL),
(29, 9, 3, 1, 'sale', '2025-02-17 13:41:33', 89900.00, 'card', 'full', 'INV-2025-5824', '', 'completed', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.00, NULL, NULL, NULL, NULL, NULL),
(30, 11, 23, 1, 'sale', '2025-02-17 13:42:50', 35900.00, 'card', 'full', 'INV-2025-5077', '', 'completed', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.00, NULL, NULL, NULL, NULL, NULL),
(31, 1, 22, 1, 'sale', '2025-02-17 14:31:36', 19900.00, 'card', 'full', 'INV-2025-7953', 'negro', 'completed', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.00, NULL, NULL, NULL, NULL, NULL);

-- Mettre à jour les transactions problématiques
UPDATE transactions 
SET brand = (SELECT brand FROM vehicles WHERE vehicles.id = transactions.vehicle_id),
    model = (SELECT model FROM vehicles WHERE vehicles.id = transactions.vehicle_id),
    version = (SELECT version FROM vehicles WHERE vehicles.id = transactions.vehicle_id),
    year = (SELECT year FROM vehicles WHERE vehicles.id = transactions.vehicle_id),
    color = (SELECT color FROM vehicles WHERE vehicles.id = transactions.vehicle_id),
    fuel_type = (SELECT fuel_type FROM vehicles WHERE vehicles.id = transactions.vehicle_id),
    transmission = (SELECT transmission FROM vehicles WHERE vehicles.id = transactions.vehicle_id),
    mileage = (SELECT mileage FROM vehicles WHERE vehicles.id = transactions.vehicle_id),
    registration_number = (SELECT registration_number FROM vehicles WHERE vehicles.id = transactions.vehicle_id),
    vin_number = (SELECT vin_number FROM vehicles WHERE vehicles.id = transactions.vehicle_id),
    registration_date = (SELECT registration_date FROM vehicles WHERE vehicles.id = transactions.vehicle_id),
    options = (SELECT options FROM vehicles WHERE vehicles.id = transactions.vehicle_id),
    location = (SELECT location FROM vehicles WHERE vehicles.id = transactions.vehicle_id)
WHERE id IN (28, 29, 30, 31);

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

-- --------------------------------------------------------

--
-- Structure de la table `vehicle_images`
--

DROP TABLE IF EXISTS `vehicle_images`;
CREATE TABLE IF NOT EXISTS `vehicle_images` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `vehicle_id` bigint UNSIGNED NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `is_primary` tinyint(1) DEFAULT '0',
  `uploaded_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `vehicle_id` (`vehicle_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `parking_spots`
--

DROP TABLE IF EXISTS `parking_spots`;
CREATE TABLE IF NOT EXISTS `parking_spots` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `spot_number` varchar(10) NOT NULL,
  `vehicle_id` bigint UNSIGNED DEFAULT NULL,
  `status` varchar(20) DEFAULT 'available',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `spot_number` (`spot_number`),
  KEY `vehicle_id` (`vehicle_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Insérer quelques emplacements de parking par défaut
INSERT INTO `parking_spots` (`spot_number`, `status`) VALUES
('A1', 'available'),
('A2', 'available'),
('A3', 'available'),
('B1', 'available'),
('B2', 'available'),
('B3', 'available'),
('C1', 'available'),
('C2', 'available'),
('C3', 'available'),
('D1', 'available'),
('D2', 'available'),
('D3', 'available');

-- Ajouter des transactions d'exemple pour fin 2024
INSERT INTO `transactions` (`customer_id`, `vehicle_id`, `user_id`, `transaction_type`, `transaction_date`, `price`, `payment_method`, `payment_type`, `invoice_number`, `notes`, `status`, `brand`, `model`, `version`, `year`, `color`, `fuel_type`, `transmission`, `mileage`) VALUES
-- Septembre 2024
(1, 3, 1, 'sale', '2024-09-05', 45000.00, 'transfer', 'full', 'INV-2024-101', 'Vente BMW Serie 3', 'completed', 'BMW', 'Serie 3', 'M340i', 2024, 'Noir Saphir', 'Essence', 'Automatique', 1500),
(2, 5, 1, 'sale', '2024-09-15', 38000.00, 'card', 'full', 'INV-2024-102', 'Vente Audi A4', 'completed', 'Audi', 'A4', 'S-Line', 2024, 'Gris Quantum', 'Diesel', 'Automatique', 2000),
(3, 7, 1, 'sale', '2024-09-25', 42000.00, 'transfer', 'full', 'INV-2024-103', 'Vente Mercedes Classe C', 'completed', 'Mercedes', 'Classe C', 'AMG Line', 2024, 'Blanc Polaire', 'Essence', 'Automatique', 1000),

-- Octobre 2024
(1, 9, 1, 'sale', '2024-10-08', 55000.00, 'transfer', 'full', 'INV-2024-104', 'Vente BMW X3', 'completed', 'BMW', 'X3', 'M40i', 2024, 'Bleu Phytonic', 'Essence', 'Automatique', 500),
(2, 11, 1, 'sale', '2024-10-18', 48000.00, 'card', 'full', 'INV-2024-105', 'Vente Audi Q5', 'completed', 'Audi', 'Q5', 'S-Line', 2024, 'Gris Manhattan', 'Diesel', 'Automatique', 1000),
(3, 13, 1, 'sale', '2024-10-28', 52000.00, 'transfer', 'full', 'INV-2024-106', 'Vente Mercedes GLC', 'completed', 'Mercedes', 'GLC', 'AMG Line', 2024, 'Noir Obsidienne', 'Essence', 'Automatique', 800),

-- Novembre 2024
(1, 15, 1, 'sale', '2024-11-05', 65000.00, 'transfer', 'full', 'INV-2024-107', 'Vente BMW X5', 'completed', 'BMW', 'X5', 'xDrive40i', 2024, 'Gris Sophisto', 'Essence', 'Automatique', 300),
(2, 17, 1, 'sale', '2024-11-15', 58000.00, 'card', 'full', 'INV-2024-108', 'Vente Audi Q7', 'completed', 'Audi', 'Q7', 'S-Line', 2024, 'Bleu Navarre', 'Diesel', 'Automatique', 400),
(3, 19, 1, 'sale', '2024-11-25', 62000.00, 'transfer', 'full', 'INV-2024-109', 'Vente Mercedes GLE', 'completed', 'Mercedes', 'GLE', 'AMG Line', 2024, 'Gris Sélénite', 'Essence', 'Automatique', 600),

-- Décembre 2024
(1, 21, 1, 'sale', '2024-12-05', 75000.00, 'transfer', 'full', 'INV-2024-110', 'Vente BMW X7', 'completed', 'BMW', 'X7', 'M50i', 2024, 'Noir Carbone', 'Essence', 'Automatique', 200),
(2, 23, 1, 'sale', '2024-12-15', 68000.00, 'card', 'full', 'INV-2024-111', 'Vente Audi Q8', 'completed', 'Audi', 'Q8', 'S-Line', 2024, 'Blanc Glacier', 'Diesel', 'Automatique', 300),
(3, 25, 1, 'sale', '2024-12-25', 72000.00, 'transfer', 'full', 'INV-2024-112', 'Vente Mercedes GLS', 'completed', 'Mercedes', 'GLS', 'AMG Line', 2024, 'Gris Ténorite', 'Essence', 'Automatique', 400);