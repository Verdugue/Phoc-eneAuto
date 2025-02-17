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
            <div class="card mb-4 shadow-sm">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center py-3">
                    <h3 class="mb-0">
                        <i class="fa fa-file-text-o me-2"></i>
                        Transaction #<?php echo $transaction['invoice_number']; ?>
                    </h3>
                    <a href="print_invoice.php?id=<?php echo $transaction['id']; ?>" 
                       class="btn btn-light" target="_blank">
                        <i class="fa fa-print me-2"></i> Imprimer la facture
                    </a>
                </div>
                <div class="card-body p-4">
                    <!-- Informations générales dans des cartes -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card h-100 bg-light border-0">
                                <div class="card-body text-center">
                                    <i class="fa fa-calendar fa-2x mb-2 text-primary"></i>
                                    <h6 class="text-muted mb-1">Date</h6>
                                    <p class="mb-0 fw-bold"><?php echo date('d/m/Y', strtotime($transaction['transaction_date'])); ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card h-100 bg-light border-0">
                                <div class="card-body text-center">
                                    <i class="fa fa-exchange fa-2x mb-2 text-primary"></i>
                                    <h6 class="text-muted mb-1">Type</h6>
                                    <span class="badge bg-<?php echo $transaction['transaction_type'] === 'sale' ? 'success' : 'info'; ?> px-3 py-2">
                                        <?php echo $transaction['transaction_type'] === 'sale' ? 'Vente' : 'Achat'; ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card h-100 bg-light border-0">
                                <div class="card-body text-center">
                                    <i class="fa fa-eur fa-2x mb-2 text-primary"></i>
                                    <h6 class="text-muted mb-1">Montant</h6>
                                    <p class="mb-0 fw-bold"><?php echo number_format($transaction['price'], 2, ',', ' '); ?> €</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card h-100 bg-light border-0">
                                <div class="card-body text-center">
                                    <i class="fa fa-credit-card fa-2x mb-2 text-primary"></i>
                                    <h6 class="text-muted mb-1">Paiement</h6>
                                    <p class="mb-0 fw-bold"><?php echo htmlspecialchars($transaction['payment_method']); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Détails du client et du véhicule -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card border-0 shadow-sm mb-4">
                                <div class="card-header bg-light">
                                    <h5 class="mb-0"><i class="fa fa-user me-2 text-primary"></i>Client</h5>
                                </div>
                                <div class="card-body">
                                    <table class="table table-borderless mb-0">
                                        <tr>
                                            <th class="ps-0 text-muted">Nom</th>
                                            <td class="text-end pe-0"><?php echo htmlspecialchars(($transaction['first_name'] ?? '') . ' ' . ($transaction['last_name'] ?? '')); ?></td>
                                        </tr>
                                        <tr>
                                            <th class="ps-0 text-muted">Email</th>
                                            <td class="text-end pe-0"><?php echo htmlspecialchars($transaction['email'] ?? ''); ?></td>
                                        </tr>
                                        <tr>
                                            <th class="ps-0 text-muted">Téléphone</th>
                                            <td class="text-end pe-0"><?php echo htmlspecialchars($transaction['phone'] ?? ''); ?></td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card border-0 shadow-sm mb-4">
                                <div class="card-header bg-light">
                                    <h5 class="mb-0"><i class="fa fa-car me-2 text-primary"></i>Véhicule</h5>
                                </div>
                                <div class="card-body">
                                    <table class="table table-borderless mb-0">
                                        <tr>
                                            <th class="ps-0 text-muted">Marque/Modèle</th>
                                            <td class="text-end pe-0"><?php echo htmlspecialchars(($transaction['brand'] ?? '') . ' ' . ($transaction['model'] ?? '')); ?></td>
                                        </tr>
                                        <tr>
                                            <th class="ps-0 text-muted">Année</th>
                                            <td class="text-end pe-0"><?php echo htmlspecialchars($transaction['year'] ?? ''); ?></td>
                                        </tr>
                                        <tr>
                                            <th class="ps-0 text-muted">Immatriculation</th>
                                            <td class="text-end pe-0"><?php echo htmlspecialchars($transaction['registration_number'] ?? ''); ?></td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <?php if ($transaction['notes']): ?>
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-light">
                                <h5 class="mb-0"><i class="fa fa-sticky-note me-2 text-primary"></i>Notes</h5>
                            </div>
                            <div class="card-body">
                                <p class="mb-0"><?php echo nl2br(htmlspecialchars($transaction['notes'])); ?></p>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="fa fa-files-o me-2 text-primary"></i>Documents</h5>
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
                                   class="list-group-item list-group-item-action d-flex align-items-center py-3 px-3">
                                    <i class="fa fa-file-pdf-o text-danger me-3 fa-lg"></i>
                                    <div>
                                        <h6 class="mb-0"><?php echo htmlspecialchars($doc['document_type']); ?></h6>
                                        <small class="text-muted">Ajouté le <?php echo date('d/m/Y', strtotime($doc['uploaded_at'])); ?></small>
                                    </div>
                                    <i class="fa fa-download ms-auto text-muted"></i>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="fa fa-file-o fa-3x text-muted mb-3"></i>
                            <p class="text-muted mb-0">Aucun document disponible</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.card {
    transition: all 0.3s ease;
}

.card:hover {
    transform: translateY(-2px);
}

.badge {
    font-size: 0.9rem;
    font-weight: 500;
}

.list-group-item {
    transition: all 0.2s ease;
}

.list-group-item:hover {
    background-color: #f8f9fa;
    transform: translateX(3px);
}

.text-primary {
    color: #0d6efd !important;
}

.bg-light {
    background-color: #f8f9fa !important;
}

.shadow-sm {
    box-shadow: 0 .125rem .25rem rgba(0,0,0,.075) !important;
}
</style>

<?php require_once '../includes/footer.php'; ?> 