<?php
// Démarrer la session si elle n'est pas déjà démarrée
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Définir le chemin de base
define('BASE_PATH', dirname(__DIR__));

// Inclure d'abord la connexion à la base de données
require_once BASE_PATH . '/config/database.php';

// Puis les autres fichiers
require_once BASE_PATH . '/includes/functions.php';

// Fonction pour gérer les erreurs
function handleError($errno, $errstr, $errfile, $errline) {
    if (!(error_reporting() & $errno)) {
        return false;
    }

    switch ($errno) {
        case E_USER_ERROR:
            $_SESSION['error'] = "Erreur fatale: $errstr";
            break;
        case E_USER_WARNING:
            $_SESSION['warning'] = "Attention: $errstr";
            break;
        case E_USER_NOTICE:
            $_SESSION['info'] = "Information: $errstr";
            break;
        default:
            $_SESSION['error'] = "Type d'erreur inconnu: [$errno] $errstr";
            break;
    }

    return true;
}

// Définir le gestionnaire d'erreurs
set_error_handler("handleError");

// Fonction pour les messages flash
function setFlashMessage($type, $message) {
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message
    ];
}

// Fonction pour récupérer un enregistrement
function fetchOne($sql, $params = []) {
    global $db;
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Fonction pour récupérer plusieurs enregistrements
function fetchAll($sql, $params = []) {
    global $db;
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
} 