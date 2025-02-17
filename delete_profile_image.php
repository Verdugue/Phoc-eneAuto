<?php
require_once 'init.php';
require_once 'config/database.php';

if (!isset($_SESSION['user_id'])) {
    die(json_encode(['success' => false, 'error' => 'Non autorisé']));
}

try {
    // Récupérer l'image actuelle
    $stmt = $pdo->prepare("SELECT profile_image FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $current_image = $stmt->fetchColumn();

    // Supprimer le fichier physique si ce n'est pas l'image par défaut
    if ($current_image && $current_image !== '/assets/images/defaults/default-profile.png') {
        $file_path = $_SERVER['DOCUMENT_ROOT'] . $current_image;
        if (file_exists($file_path)) {
            unlink($file_path);
        }
    }

    // Mettre à jour la base de données avec l'image par défaut
    $stmt = $pdo->prepare("
        UPDATE users 
        SET profile_image = '/assets/images/defaults/default-profile.png' 
        WHERE id = ?
    ");
    $stmt->execute([$_SESSION['user_id']]);

    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
} 