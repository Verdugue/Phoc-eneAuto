<?php
session_start();
require_once '../includes/init.php';
require_once '../includes/CerfaGenerator.php';

if (!isset($_GET['id'])) {
    $_SESSION['error'] = "ID de transaction manquant";
    header('Location: /transactions/');
    exit;
}

try {
    // Récupérer toutes les informations nécessaires
    $transaction_id = $_GET['id'];
    
    $sql = "SELECT t.*, 
            c.*, 
            v.*,
            s.name as seller_name,
            s.address as seller_address
            FROM transactions t
            LEFT JOIN customers c ON t.customer_id = c.id
            LEFT JOIN vehicles v ON t.vehicle_id = v.id
            LEFT JOIN suppliers s ON v.supplier_id = s.id
            WHERE t.id = ?";
            
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$transaction_id]);
    $data = $stmt->fetch();

    if (!$data) {
        throw new Exception("Transaction non trouvée");
    }

    // Préparer les données
    $seller = [
        'name' => $data['seller_name'],
        'address' => $data['seller_address']
    ];

    $buyer = [
        'first_name' => $data['first_name'],
        'last_name' => $data['last_name'],
        'address' => $data['address']
    ];

    $vehicle = [
        'brand' => $data['brand'],
        'model' => $data['model'],
        'vin_number' => $data['vin_number'],
        'registration_number' => $data['registration_number']
    ];

    // Générer le Cerfa
    $generator = new CerfaGenerator();
    $pdf = $generator->generate($data, $seller, $buyer, $vehicle);
    
    // Générer un nom de fichier
    $filename = "cerfa_cession_" . $data['invoice_number'] . ".pdf";
    
    // Envoyer le PDF
    $generator->output($filename);

} catch (Exception $e) {
    $_SESSION['error'] = "Erreur lors de la génération du Cerfa : " . $e->getMessage();
    header('Location: /transactions/view.php?id=' . $transaction_id);
    exit;
} 