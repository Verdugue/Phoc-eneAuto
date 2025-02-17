<?php
session_start();
require_once '../config/database.php';

$page_title = "Détails du Client";
require_once '../includes/header.php';

if (!isset($_GET['id'])) {
    $_SESSION['error'] = "ID du client non spécifié";
    header('Location: index.php');
    exit;
}

try {
    // Récupérer les informations du client
    $stmt = $pdo->prepare("SELECT * FROM customers WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    $client = $stmt->fetch();

    if (!$client) {
        throw new Exception("Client non trouvé");
    }

    // Récupérer les documents du client
    $stmt = $pdo->prepare("SELECT * FROM customer_documents WHERE customer_id = ? ORDER BY uploaded_at DESC");
    $stmt->execute([$_GET['id']]);
    $documents = $stmt->fetchAll();

    // Récupérer les transactions du client
    $stmt = $pdo->prepare("
        SELECT t.*, v.brand, v.model, v.year, u.username as vendeur
        FROM transactions t
        LEFT JOIN vehicles v ON t.vehicle_id = v.id
        LEFT JOIN users u ON t.user_id = u.id
        WHERE t.customer_id = ?
        ORDER BY t.transaction_date DESC
    ");
    $stmt->execute([$_GET['id']]);
    $transactions = $stmt->fetchAll();

} catch (Exception $e) {
    $_SESSION['error'] = $e->getMessage();
    header('Location: index.php');
    exit;
}
?>

<div class="container mt-4">
    <div class="mb-3">
        <a href="javascript:history.back()" class="btn btn-outline-secondary">
            <i class="fa fa-arrow-left"></i> Retour
        </a>
    </div>
    <div class="row">
        <!-- Informations principales -->
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3><?php echo htmlspecialchars($client['first_name'] . ' ' . $client['last_name']); ?></h3>
                    <div class="btn-group">
                        <a href="edit.php?id=<?php echo $client['id']; ?>" class="btn btn-primary">
                            <i class="fa fa-edit"></i> Modifier
                        </a>
                        <?php if ($client['is_active']): ?>
                            <button onclick="deleteClient(<?php echo $client['id']; ?>)" 
                                    class="btn btn-danger">
                                <i class="fa fa-trash"></i> Supprimer
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h5>Coordonnées</h5>
                            <ul class="list-unstyled">
                                <li><i class="fa fa-envelope me-2"></i> <?php echo htmlspecialchars($client['email'] ?: 'Non renseigné'); ?></li>
                                <li><i class="fa fa-phone me-2"></i> <?php echo htmlspecialchars($client['phone'] ?: 'Non renseigné'); ?></li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h5>Adresse</h5>
                            <address>
                                <?php echo htmlspecialchars($client['address'] ?: 'Non renseignée'); ?><br>
                                <?php if ($client['postal_code'] || $client['city']): ?>
                                    <?php echo htmlspecialchars($client['postal_code']); ?> <?php echo htmlspecialchars($client['city']); ?>
                                <?php endif; ?>
                            </address>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <h5>Informations complémentaires</h5>
                            <ul class="list-unstyled">
                                <li><strong>Client depuis :</strong> <?php echo date('d/m/Y', strtotime($client['created_at'])); ?></li>
                                <li><strong>Dernière mise à jour :</strong> <?php echo date('d/m/Y', strtotime($client['updated_at'])); ?></li>
                                <li>
                                    <strong>Statut :</strong>
                                    <span class="badge bg-<?php echo $client['is_active'] ? 'success' : 'danger'; ?>">
                                        <?php echo $client['is_active'] ? 'Actif' : 'Inactif'; ?>
                                    </span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Documents -->
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h3>Documents d'identité</h3>
                </div>
                <div class="card-body">
                    <?php if (!empty($documents)): ?>
                        <div class="row">
                            <?php foreach ($documents as $doc): ?>
                                <div class="col-md-4 col-sm-6 mb-3">
                                    <div class="card h-100">
                                        <div class="position-relative">
                                            <?php if (pathinfo($doc['file_name'], PATHINFO_EXTENSION) === 'pdf'): ?>
                                                <div class="text-center p-3">
                                                    <i class="fa fa-file-pdf-o fa-3x text-danger"></i>
                                                </div>
                                            <?php else: ?>
                                                <img src="<?php echo htmlspecialchars($doc['file_path']); ?>" 
                                                     class="card-img-top" 
                                                     style="height: 150px; object-fit: cover;"
                                                     alt="Document">
                                            <?php endif; ?>
                                        </div>
                                        <div class="card-body p-2">
                                            <p class="small text-muted mb-2"><?php echo htmlspecialchars($doc['document_type']); ?></p>
                                            <div class="btn-group w-100">
                                                <a href="<?php echo htmlspecialchars($doc['file_path']); ?>" 
                                                   class="btn btn-sm btn-outline-primary"
                                                   target="_blank">
                                                    <i class="fa fa-eye"></i> Voir
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center p-4">
                            <i class="fa fa-file-o fa-3x text-muted"></i>
                            <p class="mt-2">Aucun document disponible</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Historique des transactions -->
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3>Historique des transactions</h3>
                </div>
                <div class="card-body">
                    <?php if (!empty($transactions)): ?>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Type</th>
                                        <th>Véhicule</th>
                                        <th>Prix</th>
                                        <th>Vendeur</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($transactions as $transaction): ?>
                                        <tr>
                                            <td><?php echo date('d/m/Y', strtotime($transaction['transaction_date'])); ?></td>
                                            <td>
                                                <span class="badge bg-<?php echo $transaction['transaction_type'] === 'sale' ? 'success' : 'info'; ?>">
                                                    <?php echo $transaction['transaction_type'] === 'sale' ? 'Vente' : 'Achat'; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if ($transaction['vehicle_id']): ?>
                                                    <a href="/vehicles/view.php?id=<?php echo $transaction['vehicle_id']; ?>">
                                                        <?php echo htmlspecialchars($transaction['brand'] . ' ' . $transaction['model'] . ' (' . $transaction['year'] . ')'); ?>
                                                    </a>
                                                <?php else: ?>
                                                    Véhicule supprimé
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo number_format($transaction['price'], 2, ',', ' '); ?> €</td>
                                            <td><?php echo htmlspecialchars($transaction['vendeur']); ?></td>
                                            <td>
                                                <a href="/transactions/view.php?id=<?php echo $transaction['id']; ?>" 
                                                   class="btn btn-sm btn-info">
                                                    <i class="fa fa-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center p-4">
                            <i class="fa fa-exchange fa-3x text-muted"></i>
                            <p class="mt-2">Aucune transaction</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function deleteClient(id) {
    if (confirm('Êtes-vous sûr de vouloir supprimer ce client ? Cette action est irréversible.')) {
        fetch('delete.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'id=' + id
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.href = 'index.php';
            } else {
                alert(data.error || 'Une erreur est survenue');
            }
        });
    }
}
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 