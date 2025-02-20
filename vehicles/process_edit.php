<?php
session_start();
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Vérifier si c'est une modification (ID présent dans l'URL)
        $vehicle_id = isset($_GET['id']) ? $_GET['id'] : null;

        // Vérification des champs requis
        $required_fields = [
            'brand', 'model', 'year', 'mileage', 'price', 
            'vehicle_condition', 'fuel_type', 'transmission'
        ];
        $errors = [];

        foreach ($required_fields as $field) {
            if (empty($_POST[$field])) {
                $errors[] = "Le champ " . str_replace('_', ' ', $field) . " est requis.";
            }
        }

        // Vérification de l'unicité du VIN et de l'immatriculation
        if (empty($errors) && !empty($_POST['vin_number'])) {
            $stmt = $pdo->prepare("
                SELECT id FROM vehicles 
                WHERE vin_number = ? AND id != ?
            ");
            $stmt->execute([$_POST['vin_number'], $vehicle_id]);
            if ($stmt->fetch()) {
                $errors[] = "Ce numéro VIN existe déjà dans la base de données.";
            }
        }

        if (empty($errors) && !empty($_POST['registration_number'])) {
            $stmt = $pdo->prepare("
                SELECT id FROM vehicles 
                WHERE registration_number = ? AND id != ?
            ");
            $stmt->execute([$_POST['registration_number'], $vehicle_id]);
            if ($stmt->fetch()) {
                $errors[] = "Ce numéro d'immatriculation existe déjà dans la base de données.";
            }
        }

        if (!empty($errors)) {
            $_SESSION['error'] = implode("<br>", $errors);
            header("Location: edit.php" . ($vehicle_id ? "?id=$vehicle_id" : ""));
            exit;
        }

        // Préparation des données
        $data = [
            'brand' => $_POST['brand'],
            'model' => $_POST['model'],
            'version' => $_POST['version'] ?? null,
            'year' => $_POST['year'],
            'mileage' => $_POST['mileage'],
            'price' => $_POST['price'],
            'registration_date' => !empty($_POST['registration_date']) ? $_POST['registration_date'] : null,
            'vehicle_condition' => $_POST['vehicle_condition'],
            'color' => $_POST['color'] ?? null,
            'location' => $_POST['location'] ?? null,
            'fuel_type' => $_POST['fuel_type'],
            'transmission' => $_POST['transmission'],
            'registration_number' => $_POST['registration_number'] ?? null,
            'vin_number' => $_POST['vin_number'] ?? null,
            'options' => $_POST['options'] ?? null,
            'supplier_id' => !empty($_POST['supplier_id']) ? $_POST['supplier_id'] : null
        ];

        if ($vehicle_id) {
            // Mise à jour d'un véhicule existant
            $sql = "UPDATE vehicles SET 
                    brand = :brand,
                    model = :model,
                    version = :version,
                    year = :year,
                    mileage = :mileage,
                    price = :price,
                    registration_date = :registration_date,
                    vehicle_condition = :vehicle_condition,
                    color = :color,
                    location = :location,
                    fuel_type = :fuel_type,
                    transmission = :transmission,
                    registration_number = :registration_number,
                    vin_number = :vin_number,
                    options = :options,
                    supplier_id = :supplier_id,
                    updated_at = CURRENT_TIMESTAMP
                    WHERE id = :id";
            
            $data['id'] = $vehicle_id;
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute($data);
            
            $_SESSION['success'] = "Véhicule mis à jour avec succès";
        } else {
            // Insertion d'un nouveau véhicule
            $sql = "INSERT INTO vehicles (
                brand, model, version, year, mileage, price,
                registration_date, vehicle_condition, color, location,
                fuel_type, transmission, registration_number, vin_number,
                options, status, supplier_id, created_at, updated_at
            ) VALUES (
                :brand, :model, :version, :year, :mileage, :price,
                :registration_date, :vehicle_condition, :color, :location,
                :fuel_type, :transmission, :registration_number, :vin_number,
                :options, 'available', :supplier_id, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP
            )";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute($data);
            
            $vehicle_id = $pdo->lastInsertId();
            $_SESSION['success'] = "Véhicule ajouté avec succès";
        }

        // Traitement des images
        if (!empty($_FILES['vehicle_images']['name'][0])) {
            $upload_dir = "../uploads/vehicles/$vehicle_id/";
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            foreach ($_FILES['vehicle_images']['tmp_name'] as $key => $tmp_name) {
                if ($_FILES['vehicle_images']['error'][$key] === UPLOAD_ERR_OK) {
                    $file_name = uniqid() . '.' . pathinfo($_FILES['vehicle_images']['name'][$key], PATHINFO_EXTENSION);
                    $file_path = $upload_dir . $file_name;
                    
                    if (move_uploaded_file($tmp_name, $file_path)) {
                        // Vérifier s'il y a déjà des images
                        $stmt = $pdo->prepare("SELECT COUNT(*) FROM vehicle_images WHERE vehicle_id = ?");
                        $stmt->execute([$vehicle_id]);
                        $is_primary = ($stmt->fetchColumn() == 0);
                        
                        $stmt = $pdo->prepare("
                            INSERT INTO vehicle_images (vehicle_id, file_name, file_path, is_primary)
                            VALUES (?, ?, ?, ?)
                        ");
                        $stmt->execute([
                            $vehicle_id,
                            $file_name,
                            '/uploads/vehicles/' . $vehicle_id . '/' . $file_name,
                            $is_primary
                        ]);
                    }
                }
            }
        }

        header("Location: view.php?id=$vehicle_id");
        exit;

    } catch (PDOException $e) {
        $_SESSION['error'] = "Erreur lors de l'enregistrement : " . $e->getMessage();
        header("Location: edit.php" . ($vehicle_id ? "?id=$vehicle_id" : ""));
        exit;
    }
}

// Si on arrive ici, c'est que la requête n'est pas POST
header('Location: /vehicles/');
exit; 