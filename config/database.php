<?php
$host = 'localhost';
$dbname = 'phoceenne_auto';
$username = 'root';
$password = '';  // Mot de passe vide pour XAMPP par défaut

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password,
        array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION)
    );
    
    // Rendre $pdo disponible globalement
    $GLOBALS['db'] = $pdo;
} catch(PDOException $e) {
    die('Erreur de connexion à la base de données : ' . $e->getMessage());
} 