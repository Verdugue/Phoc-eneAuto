<?php
session_start();
require_once '../config/database.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    die(json_encode(['success' => false, 'error' => 'Non autorisé']));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    try {
        // Vérifier si le véhicule est disponible
        $stmt = $pdo->prepare("SELECT status FROM vehicles WHERE id = ?");
        $stmt->execute([$_POST['id']]);
        $vehicle = $stmt->fetch();

        if ($vehicle && $vehicle['status'] === 'available') {
            $stmt = $pdo->prepare("DELETE FROM vehicles WHERE id = ?");
            $stmt->execute([$_POST['id']]);
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Ce véhicule ne peut pas être supprimé car il est réservé ou vendu']);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Requête invalide']);
} 