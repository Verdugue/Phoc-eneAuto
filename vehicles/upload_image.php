<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    die(json_encode(['success' => false, 'error' => 'Non autorisé']));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['vehicle_id'])) {
    $vehicle_id = $_POST['vehicle_id'];
    $upload_dir = '../uploads/vehicles/' . $vehicle_id . '/';
    
    // Créer le dossier parent s'il n'existe pas
    if (!file_exists('../uploads/vehicles/')) {
        mkdir('../uploads/vehicles/', 0777, true);
    }
    
    // Créer le dossier s'il n'existe pas
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    
    $uploaded_files = [];
    $errors = [];
    
    foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
        $file_name = $_FILES['images']['name'][$key];
        $file_size = $_FILES['images']['size'][$key];
        $file_type = $_FILES['images']['type'][$key];
        
        // Vérifications
        if ($file_size > 5000000) { // 5MB max
            $errors[] = "Le fichier $file_name est trop volumineux";
            continue;
        }
        
        if (!in_array($file_type, ['image/jpeg', 'image/png', 'image/webp'])) {
            $errors[] = "Le type de fichier $file_name n'est pas autorisé";
            continue;
        }
        
        // Générer un nom unique
        $extension = pathinfo($file_name, PATHINFO_EXTENSION);
        $new_file_name = uniqid() . '.' . $extension;
        $file_path = $upload_dir . $new_file_name;
        
        if (move_uploaded_file($tmp_name, $file_path)) {
            try {
                // Vérifier s'il y a déjà des images pour ce véhicule
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM vehicle_images WHERE vehicle_id = ?");
                $stmt->execute([$vehicle_id]);
                $count = $stmt->fetchColumn();
                
                // Si c'est la première image, la définir comme principale
                $is_primary = ($count == 0);
                
                $stmt = $pdo->prepare("
                    INSERT INTO vehicle_images (vehicle_id, file_name, file_path, is_primary)
                    VALUES (?, ?, ?, ?)
                ");
                $stmt->execute([
                    $vehicle_id,
                    $new_file_name,
                    '/uploads/vehicles/' . $vehicle_id . '/' . $new_file_name,
                    $is_primary
                ]);
                
                $uploaded_files[] = $new_file_name;
            } catch (PDOException $e) {
                $errors[] = "Erreur lors de l'enregistrement de $file_name";
                unlink($file_path); // Supprimer le fichier si erreur
            }
        } else {
            $errors[] = "Erreur lors de l'upload de $file_name";
        }
    }
    
    echo json_encode([
        'success' => true,
        'uploaded' => $uploaded_files,
        'errors' => $errors
    ]);
} else {
    echo json_encode(['success' => false, 'error' => 'Requête invalide']);
} 