<?php
session_start();
require_once '../config/database.php';

$page_title = "Gestion des Transactions";
require_once '../includes/header.php';

try {
    // Récupérer toutes les transactions avec les détails
    $stmt = $pdo->query("
        SELECT 
            t.*,
            v.brand, v.model,
            c.first_name, c.last_name,
            u.username as vendeur
        FROM transactions t
        JOIN vehicles v ON t.vehicle_id = v.id
        JOIN customers c ON t.customer_id = c.id
        JOIN users u ON t.user_id = u.id
        ORDER BY t.transaction_date DESC
    ");
    $transactions = $stmt->fetchAll();
} catch (PDOException $e) {
    $_SESSION['error'] = "Erreur lors de la récupération des transactions: " . $e->getMessage();
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>Gestion des Transactions</h1>
    <a href="add.php" class="btn btn-primary">
        <i class="fa fa-plus"></i> Nouvelle Transaction
    </a>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>N° Facture</th>
                        <th>Client</th>
                        <th>Véhicule</th>
                        <th>Type</th>
                        <th>Montant</th>
                        <th>Vendeur</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($transactions as $transaction): ?>
                        <tr>
                            <td><?php echo date('d/m/Y', strtotime($transaction['transaction_date'])); ?></td>
                            <td><?php echo htmlspecialchars($transaction['invoice_number']); ?></td>
                            <td><?php echo htmlspecialchars($transaction['first_name'] . ' ' . $transaction['last_name']); ?></td>
                            <td><?php echo htmlspecialchars($transaction['brand'] . ' ' . $transaction['model']); ?></td>
                            <td>
                                <span class="badge bg-<?php echo $transaction['transaction_type'] === 'sale' ? 'success' : 'info'; ?>">
                                    <?php echo $transaction['transaction_type'] === 'sale' ? 'Vente' : 'Achat'; ?>
                                </span>
                            </td>
                            <td><?php echo number_format($transaction['price'], 2, ',', ' '); ?> €</td>
                            <td><?php echo htmlspecialchars($transaction['vendeur']); ?></td>
                            <td>
                                <div class="btn-group">
                                    <a href="view.php?id=<?php echo $transaction['id']; ?>" 
                                       class="btn btn-sm btn-info">
                                        <i class="fa fa-eye"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?> 