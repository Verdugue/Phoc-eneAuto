<?php
// Créer le dossier uploads s'il n'existe pas
$uploadsPath = __DIR__ . '/uploads';
$vehiclesUploadsPath = $uploadsPath . '/vehicles';
$customersUploadsPath = __DIR__ . '/uploads/customers';

if (!file_exists($uploadsPath)) {
    mkdir($uploadsPath, 0777, true);
    chmod($uploadsPath, 0777);
}

if (!file_exists($vehiclesUploadsPath)) {
    mkdir($vehiclesUploadsPath, 0777, true);
    chmod($vehiclesUploadsPath, 0777);
}

if (!file_exists($customersUploadsPath)) {
    mkdir($customersUploadsPath, 0777, true);
    chmod($customersUploadsPath, 0777);
}

// Redirection vers la page de connexion
header('Location: auth/login.php');
exit; 