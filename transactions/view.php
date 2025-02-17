<?php
session_start();
require_once '../config/database.php';

$page_title = "Détails de la Transaction";
require_once '../includes/header.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error'] = "ID de transaction invalide";
    header('Location: /transactions/');
    exit;
}

try {
    // Récupérer les détails de la transaction
    $stmt = $pdo->prepare("
        SELECT 
            t.*,
            v.brand, v.model, v.year, v.registration_number,
            c.first_name, c.last_name, c.email, c.phone,
            u.username as vendeur
        FROM transactions t
        JOIN vehicles v ON t.vehicle_id = v.id
        JOIN customers c ON t.customer_id = c.id
        JOIN users u ON t.user_id = u.id
        WHERE t.id = ?
    ");
    $stmt->execute([$_GET['id']]);
    $transaction = $stmt->fetch();

    if (!$transaction) {
        $_SESSION['error'] = "Transaction non trouvée";
        header('Location: /transactions/');
        exit;
    }
} catch (PDOException $e) {
    $_SESSION['error'] = "Erreur lors de la récupération des données: " . $e->getMessage();
}
?>

<div class="container mt-4">
    <div class="mb-3">
        <a href="/transactions/" class="btn btn-outline-secondary">
            <i class="fa fa-arrow-left"></i> Retour
        </a>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="mb-0">Transaction #<?php echo $transaction['invoice_number']; ?></h3>
                    <a href="print_invoice.php?id=<?php echo $transaction['id']; ?>" 
                       class="btn btn-primary" target="_blank">
                        <i class="fa fa-file-text-o"></i> Voir la facture
                    </a>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h5>Informations générales</h5>
                            <ul class="list-unstyled">
                                <li><strong>Date:</strong> <?php echo date('d/m/Y', strtotime($transaction['transaction_date'])); ?></li>
                                <li><strong>Type:</strong> 
                                    <span class="badge bg-<?php echo $transaction['transaction_type'] === 'sale' ? 'success' : 'info'; ?>">
                                        <?php echo $transaction['transaction_type'] === 'sale' ? 'Vente' : 'Achat'; ?>
                                    </span>
                                </li>
                                <li><strong>Montant:</strong> <?php echo number_format($transaction['price'], 2, ',', ' '); ?> €</li>
                                <li><strong>Vendeur:</strong> <?php echo htmlspecialchars($transaction['vendeur']); ?></li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h5>Méthode de paiement</h5>
                            <ul class="list-unstyled">
                                <li><strong>Mode:</strong> <?php echo htmlspecialchars($transaction['payment_method']); ?></li>
                            </ul>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <h5>Client</h5>
                            <ul class="list-unstyled">
                                <li><strong>Nom:</strong> <?php echo htmlspecialchars($transaction['first_name'] . ' ' . $transaction['last_name']); ?></li>
                                <li><strong>Email:</strong> <?php echo htmlspecialchars($transaction['email']); ?></li>
                                <li><strong>Téléphone:</strong> <?php echo htmlspecialchars($transaction['phone']); ?></li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h5>Véhicule</h5>
                            <ul class="list-unstyled">
                                <li><strong>Marque/Modèle:</strong> <?php echo htmlspecialchars($transaction['brand'] . ' ' . $transaction['model']); ?></li>
                                <li><strong>Année:</strong> <?php echo htmlspecialchars($transaction['year']); ?></li>
                                <li><strong>Immatriculation:</strong> <?php echo htmlspecialchars($transaction['registration_number']); ?></li>
                            </ul>
                        </div>
                    </div>

                    <?php if ($transaction['notes']): ?>
                        <div class="mt-4">
                            <h5>Notes</h5>
                            <p class="mb-0"><?php echo nl2br(htmlspecialchars($transaction['notes'])); ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Documents</h5>
                </div>
                <div class="card-body">
                    <?php
                    // Récupérer les documents liés à la transaction
                    $stmt = $pdo->prepare("SELECT * FROM documents WHERE transaction_id = ? ORDER BY uploaded_at DESC");
                    $stmt->execute([$transaction['id']]);
                    $documents = $stmt->fetchAll();
                    ?>

                    <?php if (!empty($documents)): ?>
                        <div class="list-group">
                            <?php foreach ($documents as $doc): ?>
                                <a href="<?php echo htmlspecialchars($doc['file_path']); ?>" 
                                   class="list-group-item list-group-item-action"
                                   target="_blank">
                                    <i class="fa fa-file-o me-2"></i>
                                    <?php echo htmlspecialchars($doc['document_type']); ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-muted mb-0">Aucun document disponible</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?> 