<?php
$host = 'localhost';
$dbname = 'phoceenne_auto';
$username = 'root';
$password = '';

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4'
        ]
    );
    
    // Rendre $pdo disponible globalement
    $GLOBALS['db'] = $pdo;
} catch(PDOException $e) {
    die('Erreur de connexion à la base de données : ' . $e->getMessage());
} 