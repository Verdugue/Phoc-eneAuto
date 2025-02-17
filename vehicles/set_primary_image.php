<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    die(json_encode(['success' => false, 'error' => 'Non autorisé']));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['image_id'])) {
    try {
        // Récupérer l'ID du véhicule
        $stmt = $pdo->prepare("SELECT vehicle_id FROM vehicle_images WHERE id = ?");
        $stmt->execute([$_POST['image_id']]);
        $vehicle_id = $stmt->fetchColumn();

        if ($vehicle_id) {
            // Retirer le statut principal de toutes les images du véhicule
            $stmt = $pdo->prepare("UPDATE vehicle_images SET is_primary = false WHERE vehicle_id = ?");
            $stmt->execute([$vehicle_id]);

            // Définir la nouvelle image principale
            $stmt = $pdo->prepare("UPDATE vehicle_images SET is_primary = true WHERE id = ?");
            $stmt->execute([$_POST['image_id']]);

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