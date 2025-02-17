<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    die(json_encode(['success' => false, 'error' => 'Non autorisé']));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['image_id'])) {
    try {
        // Récupérer les informations de l'image
        $stmt = $pdo->prepare("SELECT * FROM vehicle_images WHERE id = ?");
        $stmt->execute([$_POST['image_id']]);
        $image = $stmt->fetch();
        
        if ($image) {
            // Supprimer le fichier physique
            $file_path = '../' . ltrim($image['file_path'], '/');
            if (file_exists($file_path)) {
                unlink($file_path);
            }
            
            // Supprimer l'enregistrement
            $stmt = $pdo->prepare("DELETE FROM vehicle_images WHERE id = ?");
            $stmt->execute([$_POST['image_id']]);
            
            // Si c'était l'image principale, définir une autre image comme principale
            if ($image['is_primary']) {
                $stmt = $pdo->prepare("
                    UPDATE vehicle_images 
                    SET is_primary = true 
                    WHERE vehicle_id = ? 
                    ORDER BY id ASC 
                    LIMIT 1
                ");
                $stmt->execute([$image['vehicle_id']]);
            }
            
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Image non trouvée']);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Requête invalide']);
} 