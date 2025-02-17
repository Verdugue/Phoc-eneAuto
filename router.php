<?php
// Si le fichier/dossier existe, on le sert directement
if (file_exists(__DIR__ . $_SERVER['REQUEST_URI'])) {
    return false;
}

// Si c'est un fichier PHP qui n'existe pas, on redirige vers 404.php
if (strpos($_SERVER['REQUEST_URI'], '.php') !== false) {
    require __DIR__ . '/404.php';
    exit;
}

// Sinon on sert le fichier demandé
return false; 