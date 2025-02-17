<?php
session_start();
require_once '../config/database.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    die(json_encode(['success' => false, 'error' => 'Non autorisé']));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    try {
        $pdo->beginTransaction();

        // Option 1 : Supprimer complètement le client
        $stmt = $pdo->prepare("DELETE FROM customers WHERE id = ?");
        
        // OU Option 2 : Modifier l'email pour permettre sa réutilisation
        /*
        $stmt = $pdo->prepare("
            UPDATE customers 
            SET is_active = false,
                email = CONCAT(email, '_deleted_', id, '_', DATE_FORMAT(NOW(), '%Y%m%d%H%i%s'))
            WHERE id = ?
        ");
        */

        $stmt->execute([$_POST['id']]);
        
        $pdo->commit();
        
        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request']);
} 