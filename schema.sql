-- Création des tables
-- Table des utilisateurs (employés)
CREATE TABLE users (
    id SERIAL PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    role VARCHAR(20) NOT NULL CHECK (role IN ('admin', 'employee')),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table des clients
CREATE TABLE customers (
    id SERIAL PRIMARY KEY,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(100) UNIQUE,
    phone VARCHAR(20),
    address TEXT,
    postal_code VARCHAR(10),
    city VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    is_active BOOLEAN DEFAULT true
);

-- Table des véhicules
CREATE TABLE vehicles (
    id SERIAL PRIMARY KEY,
    brand VARCHAR(50) NOT NULL,
    model VARCHAR(50) NOT NULL,
    year INTEGER NOT NULL,
    mileage INTEGER NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    vehicle_condition VARCHAR(20) CHECK (vehicle_condition IN ('new', 'used')),
    color VARCHAR(30),
    fuel_type VARCHAR(20),
    transmission VARCHAR(20),
    registration_number VARCHAR(20) UNIQUE,
    vin_number VARCHAR(17) UNIQUE,
    status VARCHAR(20) CHECK (status IN ('available', 'sold', 'reserved')),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table des transactions
CREATE TABLE transactions (
    id SERIAL PRIMARY KEY,
    customer_id INTEGER REFERENCES customers(id),
    vehicle_id INTEGER REFERENCES vehicles(id),
    user_id INTEGER REFERENCES users(id),
    transaction_type VARCHAR(20) CHECK (transaction_type IN ('sale', 'purchase')),
    transaction_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    price DECIMAL(10,2) NOT NULL,
    payment_method VARCHAR(20),
    invoice_number VARCHAR(50) UNIQUE,
    notes TEXT
);

-- Table des documents
CREATE TABLE documents (
    id SERIAL PRIMARY KEY,
    transaction_id INTEGER REFERENCES transactions(id),
    document_type VARCHAR(20) NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table des photos de véhicules
CREATE TABLE vehicle_images (
    id SERIAL PRIMARY KEY,
    vehicle_id INTEGER REFERENCES vehicles(id),
    file_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    is_primary BOOLEAN DEFAULT false,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table des documents clients
CREATE TABLE customer_documents (
    id SERIAL PRIMARY KEY,
    customer_id INTEGER REFERENCES customers(id),
    document_type VARCHAR(50) NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insertion des données initiales
-- Utilisateurs
INSERT INTO users (username, password_hash, email, role) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@phoceenne-auto.fr', 'admin'),
('vendeur1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'vendeur1@phoceenne-auto.fr', 'employee');

-- Clients
INSERT INTO customers (first_name, last_name, email, phone, address, postal_code, city) VALUES
('Jean', 'Dupont', 'jean.dupont@email.com', '0601020304', '123 rue de la République', '13001', 'Marseille'),
('Marie', 'Martin', 'marie.martin@email.com', '0607080910', '45 avenue du Prado', '13008', 'Marseille'),
('Sophie', 'Laurent', 'sophie.laurent@email.com', '0607080910', '78 avenue des Gobelins', '75013', 'Paris'),
('Thomas', 'Bernard', 'thomas.bernard@email.com', '0708091011', '45 rue de la Paix', '69002', 'Lyon'),
('Julie', 'Moreau', 'julie.moreau@email.com', '0809101112', '23 boulevard des Fleurs', '33000', 'Bordeaux'),
('Nicolas', 'Petit', 'nicolas.petit@email.com', '0910111213', '12 rue du Commerce', '44000', 'Nantes'),
('Emma', 'Leroy', 'emma.leroy@email.com', '0607080910', '56 avenue Foch', '67000', 'Strasbourg');

-- Véhicules
INSERT INTO vehicles (brand, model, year, mileage, price, vehicle_condition, color, fuel_type, transmission, registration_number, vin_number, status) VALUES
-- Véhicules de luxe
('Mercedes-Benz', 'AMG GT', 2023, 1500, 158900.00, 'new', 'Noir Obsidienne', 'Essence', 'Automatique', 'AB-123-CD', 'WDDLJ7GB5EA123456', 'available'),
('Porsche', '911 GT3', 2023, 0, 189500.00, 'new', 'Gris Argent', 'Essence', 'Automatique', 'EF-456-GH', 'WP0ZZZ99ZTS392817', 'available'),
('BMW', 'M4 Competition', 2022, 15000, 89900.00, 'used', 'Bleu San Marino', 'Essence', 'Automatique', 'IJ-789-KL', 'WBA3R1C58EK234567', 'available'),

-- Véhicules électriques
('Tesla', 'Model S Plaid', 2023, 500, 129900.00, 'new', 'Blanc Nacré', 'Électrique', 'Automatique', 'MN-012-OP', '5YJSA1E47MF123456', 'available'),
('Porsche', 'Taycan Turbo S', 2023, 1200, 189900.00, 'new', 'Rouge Carmin', 'Électrique', 'Automatique', 'QR-345-ST', 'WP0ZZZ29ZPS123456', 'reserved'),

-- Véhicules populaires
('Volkswagen', 'Golf 8 GTI', 2022, 25000, 38900.00, 'used', 'Gris Dauphin', 'Essence', 'Manuelle', 'UV-678-WX', 'WVWZZZAUZMP123456', 'available'),
('Renault', 'Clio RS Line', 2023, 8500, 24900.00, 'used', 'Orange Valencia', 'Essence', 'Manuelle', 'YZ-901-AB', 'VF15RJL0H12345678', 'available'),
('Peugeot', '308 GT', 2022, 18000, 32900.00, 'used', 'Bleu Vertigo', 'Diesel', 'Automatique', 'CD-234-EF', 'VF3LBHZMGHS123456', 'sold'),

-- SUV et 4x4
('Range Rover', 'Sport P530 V8', 2023, 100, 149900.00, 'new', 'Noir Santorini', 'Essence', 'Automatique', 'GH-567-IJ', 'SALGA2BG7EA123456', 'available'),
('Audi', 'RS Q8', 2023, 5000, 159900.00, 'used', 'Gris Nardo', 'Essence', 'Automatique', 'KL-890-MN', 'WAUZZZF18N123456', 'available'),

-- Véhicules hybrides
('Toyota', 'RAV4 Hybride', 2023, 12000, 42900.00, 'used', 'Blanc Lunaire', 'Hybride', 'Automatique', 'OP-123-QR', 'JTMDDREV20D123456', 'available'),
('Lexus', 'NX 450h+', 2023, 8000, 69900.00, 'used', 'Gris Mercure', 'Hybride', 'Automatique', 'ST-456-UV', 'JTJBARBZ502123456', 'available'),

-- Véhicules sportifs
('Alpine', 'A110 S', 2023, 3500, 79900.00, 'used', 'Bleu Alpine', 'Essence', 'Automatique', 'WX-789-YZ', 'VF3MLVF00N1123456', 'available'),
('Nissan', 'GT-R Nismo', 2022, 9000, 219900.00, 'used', 'Blanc Pearl', 'Essence', 'Automatique', 'AB-012-CD', 'JN1GANR35U0123456', 'reserved'),

-- Véhicules utilitaires
('Volkswagen', 'Transporter T6.1', 2023, 15000, 42900.00, 'used', 'Blanc Candy', 'Diesel', 'Manuelle', 'EF-345-GH', 'WV1ZZZ7HZNH123456', 'available'),
('Ford', 'Transit Custom', 2022, 28000, 32900.00, 'used', 'Gris Magnetic', 'Diesel', 'Manuelle', 'IJ-678-KL', 'WF0VXXBDFV12345678', 'available'),

-- Clin d'œil à Destiny 2
('Omolon', 'XURF Board', 2759, 0, 999999.99, 'new', 'Néon Chromé', 'Lumière', 'Automatique', 'XU-RF-777', 'DSTNY2SPARROW777', 'available');

-- Transactions
INSERT INTO transactions (customer_id, vehicle_id, user_id, transaction_type, transaction_date, price, payment_method, invoice_number, notes) VALUES
-- Janvier 2024
(1, 3, 1, 'sale', '2024-01-05 10:30:00', 89900.00, 'card', 'INV-2024-002', 'Vente BMW M4'),
(2, 5, 2, 'sale', '2024-01-12 14:15:00', 189900.00, 'transfer', 'INV-2024-003', 'Vente Porsche Taycan'),
(1, 7, 1, 'sale', '2024-01-20 11:45:00', 24900.00, 'card', 'INV-2024-004', 'Vente Clio RS'),
(2, 9, 2, 'sale', '2024-01-28 16:20:00', 149900.00, 'transfer', 'INV-2024-005', 'Vente Range Rover'),

-- Février 2024
(1, 1, 1, 'sale', '2024-02-03 09:30:00', 158900.00, 'transfer', 'INV-2024-006', 'Vente Mercedes AMG GT'),
(2, 4, 2, 'sale', '2024-02-10 15:45:00', 129900.00, 'card', 'INV-2024-007', 'Vente Tesla Model S'),
(1, 6, 1, 'sale', '2024-02-18 13:20:00', 38900.00, 'card', 'INV-2024-008', 'Vente Golf GTI'),
(2, 11, 2, 'sale', '2024-02-25 10:15:00', 42900.00, 'cash', 'INV-2024-009', 'Vente Toyota RAV4'),

-- Mars 2024
(1, 2, 1, 'sale', '2024-03-02 11:30:00', 189500.00, 'transfer', 'INV-2024-010', 'Vente Porsche 911'),
(2, 8, 2, 'sale', '2024-03-09 14:45:00', 32900.00, 'card', 'INV-2024-011', 'Vente Peugeot 308'),
(1, 10, 1, 'sale', '2024-03-16 16:20:00', 159900.00, 'transfer', 'INV-2024-012', 'Vente Audi RS Q8'),
(2, 12, 2, 'sale', '2024-03-23 09:15:00', 69900.00, 'card', 'INV-2024-013', 'Vente Lexus NX'),
(1, 13, 1, 'sale', '2024-03-30 13:45:00', 79900.00, 'transfer', 'INV-2024-014', 'Vente Alpine A110'),

-- Avril 2024
(2, 14, 2, 'sale', '2024-04-06 10:30:00', 219900.00, 'transfer', 'INV-2024-015', 'Vente Nissan GT-R'),
(1, 15, 1, 'sale', '2024-04-13 15:20:00', 42900.00, 'card', 'INV-2024-016', 'Vente VW Transporter'),
(2, 16, 2, 'sale', '2024-04-20 11:45:00', 32900.00, 'cash', 'INV-2024-017', 'Vente Ford Transit'),
(1, 3, 1, 'sale', '2024-04-27 14:30:00', 85000.00, 'transfer', 'INV-2024-018', 'Vente BMW M4'),

-- Mai 2024
(2, 1, 2, 'sale', '2024-05-04 09:15:00', 155000.00, 'transfer', 'INV-2024-019', 'Vente Mercedes AMG'),
(1, 4, 1, 'sale', '2024-05-11 16:45:00', 125000.00, 'card', 'INV-2024-020', 'Vente Tesla Model S'),
(2, 7, 2, 'sale', '2024-05-18 13:30:00', 23500.00, 'cash', 'INV-2024-021', 'Vente Clio RS'),
(1, 10, 1, 'sale', '2024-05-25 10:20:00', 155000.00, 'transfer', 'INV-2024-022', 'Vente Audi RS Q8'),

-- Juin 2024
(2, 2, 2, 'sale', '2024-06-01 11:30:00', 185000.00, 'transfer', 'INV-2024-023', 'Vente Porsche 911'),
(1, 5, 1, 'sale', '2024-06-08 14:15:00', 185000.00, 'card', 'INV-2024-024', 'Vente Porsche Taycan'),
(2, 13, 2, 'sale', '2024-06-15 16:45:00', 77500.00, 'transfer', 'INV-2024-025', 'Vente Alpine A110'),
(1, 11, 1, 'sale', '2024-06-20 09:30:00', 41500.00, 'card', 'INV-2024-026', 'Vente Toyota RAV4');

-- Mise à jour des statuts des véhicules vendus
UPDATE vehicles SET status = 'sold' WHERE id IN (1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13);
UPDATE vehicles SET status = 'reserved' WHERE id IN (14, 15);

-- Mettre à jour toutes les transactions pour qu'elles soient dans les 6 derniers mois
UPDATE transactions 
SET transaction_date = DATE_SUB(transaction_date, INTERVAL 1 YEAR)
WHERE YEAR(transaction_date) = 2024;

-- Mettre à jour spécifiquement les transactions récentes
UPDATE transactions 
SET transaction_date = '2023-12-01 11:30:00',
    invoice_number = 'INV-2023-023'
WHERE invoice_number = 'INV-2024-023';

UPDATE transactions 
SET transaction_date = '2023-12-08 14:15:00',
    invoice_number = 'INV-2023-024'
WHERE invoice_number = 'INV-2024-024';

UPDATE transactions 
SET transaction_date = '2023-12-15 16:45:00',
    invoice_number = 'INV-2023-025'
WHERE invoice_number = 'INV-2024-025';

UPDATE transactions 
SET transaction_date = '2023-12-20 09:30:00',
    invoice_number = 'INV-2023-026'
WHERE invoice_number = 'INV-2024-026';

-- Mettre à jour les dates des transactions pour les répartir sur 6 mois
UPDATE transactions 
SET transaction_date = CASE 
    WHEN invoice_number = 'INV-2024-002' THEN '2023-07-05 10:30:00'
    WHEN invoice_number = 'INV-2024-003' THEN '2023-07-12 14:15:00'
    WHEN invoice_number = 'INV-2024-004' THEN '2023-07-20 11:45:00'
    WHEN invoice_number = 'INV-2024-005' THEN '2023-07-28 16:20:00'
    
    WHEN invoice_number = 'INV-2024-006' THEN '2023-08-03 09:30:00'
    WHEN invoice_number = 'INV-2024-007' THEN '2023-08-10 15:45:00'
    WHEN invoice_number = 'INV-2024-008' THEN '2023-08-18 13:20:00'
    WHEN invoice_number = 'INV-2024-009' THEN '2023-08-25 10:15:00'
    
    WHEN invoice_number = 'INV-2024-010' THEN '2023-09-02 11:30:00'
    WHEN invoice_number = 'INV-2024-011' THEN '2023-09-09 14:45:00'
    WHEN invoice_number = 'INV-2024-012' THEN '2023-09-16 16:20:00'
    WHEN invoice_number = 'INV-2024-013' THEN '2023-09-23 09:15:00'
    WHEN invoice_number = 'INV-2024-014' THEN '2023-09-30 13:45:00'
    
    WHEN invoice_number = 'INV-2024-015' THEN '2023-10-06 10:30:00'
    WHEN invoice_number = 'INV-2024-016' THEN '2023-10-13 15:20:00'
    WHEN invoice_number = 'INV-2024-017' THEN '2023-10-20 11:45:00'
    WHEN invoice_number = 'INV-2024-018' THEN '2023-10-27 14:30:00'
    
    WHEN invoice_number = 'INV-2024-019' THEN '2023-11-04 09:15:00'
    WHEN invoice_number = 'INV-2024-020' THEN '2023-11-11 16:45:00'
    WHEN invoice_number = 'INV-2024-021' THEN '2023-11-18 13:30:00'
    WHEN invoice_number = 'INV-2024-022' THEN '2023-11-25 10:20:00'
    
    WHEN invoice_number = 'INV-2024-023' THEN '2023-12-01 11:30:00'
    WHEN invoice_number = 'INV-2024-024' THEN '2023-12-08 14:15:00'
    WHEN invoice_number = 'INV-2024-025' THEN '2023-12-15 16:45:00'
    WHEN invoice_number = 'INV-2024-026' THEN '2023-12-20 09:30:00'
    END
WHERE invoice_number LIKE 'INV-2024-%';