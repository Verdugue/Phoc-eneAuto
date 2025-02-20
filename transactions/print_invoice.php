<?php
session_start();
require_once '../config/database.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("ID de transaction invalide");
}

try {
    $stmt = $pdo->prepare("
        SELECT 
            t.id, t.customer_id, t.vehicle_id, t.user_id, 
            t.transaction_type, t.transaction_date, t.price,
            t.payment_method, t.payment_type, t.invoice_number, 
            t.notes, t.status,
            v.brand, v.model, v.year, v.mileage,
            v.fuel_type, v.transmission, v.color, 
            v.registration_number, v.options, v.version,
            c.first_name, c.last_name, c.address, c.postal_code, 
            c.city, c.email, c.phone,
            u.username as vendeur
        FROM transactions t
        JOIN vehicles v ON t.vehicle_id = v.id
        JOIN customers c ON t.customer_id = c.id
        JOIN users u ON t.user_id = u.id
        WHERE t.id = ?
    ");
    $stmt->execute([$_GET['id']]);
    $transaction = $stmt->fetch();
} catch (PDOException $e) {
    die("Erreur: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Facture <?php echo $transaction['invoice_number']; ?></title>
    <style>
        @media print {
            .no-print { display: none; }
        }
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
        }
        .invoice-header {
            background: #f8f9fa;
            padding: 20px;
            margin-bottom: 40px;
        }
        .company-info {
            float: left;
            width: 40%;
        }
        .invoice-info {
            float: right;
            width: 40%;
            text-align: right;
        }
        .clear {
            clear: both;
        }
        .client-info {
            margin-bottom: 40px;
        }
        .invoice-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 40px;
        }
        .invoice-table th, .invoice-table td {
            padding: 10px;
            border: 1px solid #ddd;
        }
        .invoice-table th {
            background: #f8f9fa;
        }
        .total-info {
            float: right;
            width: 40%;
            margin-bottom: 40px;
        }
        .payment-info {
            margin-bottom: 40px;
        }
        .print-button {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 10px 20px;
            background: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .page {
            page-break-after: always;
            margin-bottom: 30px;
        }
        .vehicle-details {
            padding: 20px;
        }
        .details-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        .details-table th, .details-table td {
            padding: 12px;
            border: 1px solid #ddd;
        }
        .details-table th {
            background: #f8f9fa;
            width: 25%;
            text-align: left;
        }
        .options-section, .technical-section {
            margin: 20px 0;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 4px;
        }
        @media print {
            .page {
                height: 100vh;
                page-break-after: always;
            }
        }
    </style>
</head>
<body>
    <button onclick="window.print()" class="print-button no-print">Imprimer</button>

    <!-- Première page - Facture existante -->
    <div class="page">
        <div class="invoice-header">
            <div class="company-info">
                <h1>Phocéenne Auto</h1>
                <p>123 Avenue des Véhicules<br>
                13000 Marseille<br>
                Tél: 04.91.00.00.00<br>
                Email: contact@phoceenne-auto.fr</p>
            </div>
            <div class="invoice-info">
                <h2>FACTURE</h2>
                <p>N° : <?php echo htmlspecialchars($transaction['invoice_number']); ?><br>
                Date : <?php echo date('d/m/Y', strtotime($transaction['transaction_date'])); ?></p>
            </div>
            <div class="clear"></div>
        </div>

        <div class="client-info">
            <h3>Facturer à :</h3>
            <p><?php echo htmlspecialchars($transaction['first_name'] . ' ' . $transaction['last_name']); ?><br>
            <?php echo htmlspecialchars($transaction['address']); ?><br>
            <?php echo htmlspecialchars($transaction['postal_code'] . ' ' . $transaction['city']); ?><br>
            Tél: <?php echo htmlspecialchars($transaction['phone']); ?><br>
            Email: <?php echo htmlspecialchars($transaction['email']); ?></p>
        </div>

        <table class="invoice-table">
            <thead>
                <tr>
                    <th>Description</th>
                    <th>Quantité</th>
                    <th>Prix unitaire</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><?php echo htmlspecialchars($transaction['brand'] . ' ' . $transaction['model'] . ' (' . $transaction['year'] . ')'); ?></td>
                    <td>1</td>
                    <td><?php echo number_format($transaction['price'], 2, ',', ' '); ?> €</td>
                    <td><?php echo number_format($transaction['price'], 2, ',', ' '); ?> €</td>
                </tr>
            </tbody>
        </table>

        <div class="total-info">
            <table>
                <tr>
                    <td><strong>Total TTC :</strong></td>
                    <td><?php echo number_format($transaction['price'], 2, ',', ' '); ?> €</td>
                </tr>
            </table>
        </div>

        <div class="clear"></div>

        <div class="payment-info">
            <h3>Informations de paiement</h3>
            <p>Mode de paiement : <?php echo ucfirst($transaction['payment_method']); ?><br>
            Date d'échéance : <?php echo date('d/m/Y', strtotime($transaction['transaction_date'])); ?></p>
        </div>

        <?php if ($transaction['notes']): ?>
        <div class="notes">
            <h3>Notes</h3>
            <p><?php echo nl2br(htmlspecialchars($transaction['notes'])); ?></p>
        </div>
        <?php endif; ?>
    </div>

    <!-- Deuxième page - Détails du véhicule -->
    <div class="page vehicle-details">
        <div class="invoice-header">
            <div class="company-info">
                <h1>Phocéenne Auto</h1>
                <p>123 Avenue des Véhicules<br>
                13000 Marseille<br>
                Tél: 04.91.00.00.00<br>
                Email: contact@phoceenne-auto.fr</p>
            </div>
            <div class="invoice-info">
                <h2>FICHE VÉHICULE</h2>
                <p>Annexe à la facture N° : <?php echo htmlspecialchars($transaction['invoice_number']); ?></p>
            </div>
            <div class="clear"></div>
        </div>

        <div class="vehicle-info">
            <h3>Caractéristiques du véhicule</h3>
            <table class="details-table">
                <tr>
                    <th>Marque</th>
                    <td><?php echo htmlspecialchars($transaction['brand']); ?></td>
                    <th>Modèle</th>
                    <td><?php echo htmlspecialchars($transaction['model']); ?></td>
                </tr>
                <tr>
                    <th>Année</th>
                    <td><?php echo htmlspecialchars($transaction['year']); ?></td>
                    <th>Immatriculation</th>
                    <td><?php echo htmlspecialchars($transaction['registration_number']); ?></td>
                </tr>
                <tr>
                    <th>Kilométrage</th>
                    <td><?php echo number_format($transaction['mileage'], 0, ',', ' '); ?> km</td>
                    <th>Carburant</th>
                    <td><?php echo htmlspecialchars($transaction['fuel_type']); ?></td>
                </tr>
                <tr>
                    <th>Boîte de vitesse</th>
                    <td><?php echo htmlspecialchars($transaction['transmission']); ?></td>
                    <th>Couleur</th>
                    <td><?php echo htmlspecialchars($transaction['color']); ?></td>
                </tr>
            </table>

            <?php if (!empty($transaction['options'])): ?>
            <div class="options-section">
                <h3>Équipements et options</h3>
                <p><?php echo nl2br(htmlspecialchars($transaction['options'])); ?></p>
            </div>
            <?php endif; ?>

            <?php if (!empty($transaction['description'])): ?>
            <div class="technical-section">
                <h3>Description</h3>
                <p><?php echo nl2br(htmlspecialchars($transaction['description'])); ?></p>
            </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html> 