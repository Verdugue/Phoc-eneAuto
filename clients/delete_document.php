<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    die(json_encode(['success' => false, 'error' => 'Non autorisé']));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    try {
        // Récupérer les informations du document
        $stmt = $pdo->prepare("SELECT * FROM customer_documents WHERE id = ?");
        $stmt->execute([$_POST['id']]);
        $document = $stmt->fetch();
        
        if ($document) {
            // Supprimer le fichier physique
            $file_path = '../' . ltrim($document['file_path'], '/');
            if (file_exists($file_path)) {
                unlink($file_path);
            }
            
            // Supprimer l'enregistrement
            $stmt = $pdo->prepare("DELETE FROM customer_documents WHERE id = ?");
            $stmt->execute([$_POST['id']]);
            
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Document non trouvé']);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Requête invalide']);
} 