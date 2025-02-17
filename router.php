<?php
// Obtenir le chemin de la requête
$request_uri = $_SERVER['REQUEST_URI'];

// Retirer les paramètres GET de l'URL
$request_uri = strtok($request_uri, '?');

// Si le fichier/dossier existe, on le sert directement
if (file_exists(__DIR__ . $request_uri)) {
    return false;
}

// Si c'est un fichier PHP qui n'existe pas, on vérifie d'abord sans le .php
if (strpos($request_uri, '.php') !== false) {
    $without_php = str_replace('.php', '', $request_uri);
    if (file_exists(__DIR__ . $without_php . '.php')) {
        include __DIR__ . $without_php . '.php';
        exit;
    }
    require __DIR__ . '/404.php';
    exit;
}

// Sinon on sert le fichier demandé
return false; 