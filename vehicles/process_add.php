<?php
session_start();
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
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
        if (empty($errors)) {
            $stmt = $pdo->prepare("SELECT id FROM vehicles WHERE vin_number = ?");
            $stmt->execute([$_POST['vin_number']]);
            if ($stmt->fetch()) {
                $errors[] = "Ce numéro VIN existe déjà dans la base de données.";
            }

            $stmt = $pdo->prepare("SELECT id FROM vehicles WHERE registration_number = ?");
            $stmt->execute([$_POST['registration_number']]);
            if ($stmt->fetch()) {
                $errors[] = "Ce numéro d'immatriculation existe déjà dans la base de données.";
            }
        }

        if (!empty($errors)) {
            $_SESSION['error'] = implode("<br>", $errors);
            header("Location: add.php");
            exit;
        }

        // Récupération des données du formulaire
        $brand = $_POST['brand'];
        $model = $_POST['model'];
        $version = $_POST['version'] ?? null;
        $year = $_POST['year'];
        $mileage = $_POST['mileage'];
        $price = $_POST['price'];
        $registration_date = $_POST['registration_date'] ?? null;
        $vehicle_condition = $_POST['vehicle_condition'];
        $color = $_POST['color'];
        $location = $_POST['location'] ?? null;
        $fuel_type = $_POST['fuel_type'];
        $transmission = $_POST['transmission'];
        $registration_number = $_POST['registration_number'];
        $vin_number = $_POST['vin_number'];
        $options = $_POST['options'] ?? null;
        $supplier_id = !empty($_POST['supplier_id']) ? $_POST['supplier_id'] : null;

        // Préparation de la requête SQL
        $sql = "INSERT INTO vehicles (
            brand, model, version, year, mileage, price, registration_date,
            vehicle_condition, color, location, fuel_type, transmission,
            registration_number, vin_number, options, status, supplier_id
        ) VALUES (
            :brand, :model, :version, :year, :mileage, :price, :registration_date,
            :vehicle_condition, :color, :location, :fuel_type, :transmission,
            :registration_number, :vin_number, :options, 'available', :supplier_id
        )";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'brand' => $brand,
            'model' => $model,
            'version' => $version,
            'year' => $year,
            'mileage' => $mileage,
            'price' => $price,
            'registration_date' => $registration_date,
            'vehicle_condition' => $vehicle_condition,
            'color' => $color,
            'location' => $location,
            'fuel_type' => $fuel_type,
            'transmission' => $transmission,
            'registration_number' => $registration_number,
            'vin_number' => $vin_number,
            'options' => $options,
            'supplier_id' => $supplier_id
        ]);

        $vehicle_id = $pdo->lastInsertId();

        // Traitement des images
        if (!empty($_FILES['vehicle_images']['name'][0])) {
            $upload_dir = "../uploads/vehicles/" . $vehicle_id . "/";
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            foreach ($_FILES['vehicle_images']['tmp_name'] as $key => $tmp_name) {
                $file_name = uniqid() . "." . pathinfo($_FILES['vehicle_images']['name'][$key], PATHINFO_EXTENSION);
                $file_path = "/uploads/vehicles/" . $vehicle_id . "/" . $file_name;

                if (move_uploaded_file($tmp_name, ".." . $file_path)) {
                    // Enregistrer l'image dans la base de données
                    $sql = "INSERT INTO vehicle_images (vehicle_id, file_name, file_path, is_primary) 
                           VALUES (:vehicle_id, :file_name, :file_path, :is_primary)";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([
                        'vehicle_id' => $vehicle_id,
                        'file_name' => $file_name,
                        'file_path' => $file_path,
                        'is_primary' => ($key === 0) ? 1 : 0
                    ]);
                }
            }
        }

        $_SESSION['success'] = "Le véhicule a été ajouté avec succès.";
        header("Location: view.php?id=" . $vehicle_id);
        exit;

    } catch (PDOException $e) {
        $_SESSION['error'] = "Erreur lors de l'ajout du véhicule : " . $e->getMessage();
        header("Location: add.php");
        exit;
    }
} 