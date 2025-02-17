<?php
session_start();
require_once '../config/database.php';

$page_title = "Détails du fournisseur";
require_once '../includes/header.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error'] = "ID du fournisseur invalide";
    header('Location: /suppliers.php');
    exit;
}

$supplier_id = $_GET['id'];

try {
    // Récupérer les informations du fournisseur
    $stmt = $pdo->prepare("
        SELECT * FROM suppliers WHERE id = ?
    ");
    $stmt->execute([$supplier_id]);
    $supplier = $stmt->fetch();

    if (!$supplier) {
        $_SESSION['error'] = "Fournisseur non trouvé";
        header('Location: /suppliers.php');
        exit;
    }

    // Récupérer les véhicules du fournisseur
    $stmt = $pdo->prepare("
        SELECT 
            v.*,
            CASE 
                WHEN v.status = 'available' THEN 'Disponible'
                WHEN v.status = 'reserved' THEN 'Réservé'
                WHEN v.status = 'sold' THEN 'Vendu'
            END as status_fr
        FROM vehicles v
        WHERE v.supplier_id = ?
        ORDER BY v.status, v.brand, v.model
    ");
    $stmt->execute([$supplier_id]);
    $vehicles = $stmt->fetchAll();

} catch (PDOException $e) {
    $_SESSION['error'] = "Erreur lors de la récupération des données: " . $e->getMessage();
}
?>

<div class="container mt-4">
    <div class="mb-3">
        <a href="/suppliers/" class="btn btn-outline-secondary">
            <i class="fa fa-arrow-left"></i> Retour
        </a>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4 shadow-sm">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center py-3">
                    <h3 class="mb-0">
                        <i class="fa fa-building me-2"></i>
                        <?php echo htmlspecialchars($supplier['name'] ?? ''); ?>
                    </h3>
                    <a href="edit.php?id=<?php echo $supplier['id']; ?>" class="btn btn-light">
                        <i class="fa fa-edit me-2"></i> Modifier
                    </a>
                </div>
                <div class="card-body p-4">
                    <!-- Informations de contact -->
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <div class="card h-100 bg-light border-0">
                                <div class="card-body text-center">
                                    <i class="fa fa-envelope fa-2x mb-2 text-primary"></i>
                                    <h6 class="text-muted mb-1">Email</h6>
                                    <p class="mb-0 fw-bold"><?php echo htmlspecialchars($supplier['email'] ?? ''); ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card h-100 bg-light border-0">
                                <div class="card-body text-center">
                                    <i class="fa fa-phone fa-2x mb-2 text-primary"></i>
                                    <h6 class="text-muted mb-1">Téléphone</h6>
                                    <p class="mb-0 fw-bold"><?php echo htmlspecialchars($supplier['phone'] ?? ''); ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card h-100 bg-light border-0">
                                <div class="card-body text-center">
                                    <i class="fa fa-globe fa-2x mb-2 text-primary"></i>
                                    <h6 class="text-muted mb-1">Site Web</h6>
                                    <p class="mb-0 fw-bold">
                                        <?php if (!empty($supplier['website'])): ?>
                                            <a href="<?php echo htmlspecialchars($supplier['website']); ?>" 
                                               target="_blank" 
                                               class="text-primary text-decoration-none">
                                                Visiter le site
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted">Non renseigné</span>
                                        <?php endif; ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Adresse -->
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-light">
                            <h5 class="mb-0">
                                <i class="fa fa-map-marker me-2 text-primary"></i>
                                Adresse
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <table class="table table-borderless mb-0">
                                        <tr>
                                            <th class="ps-0 text-muted w-50">Rue</th>
                                            <td class="text-end pe-0"><?php echo htmlspecialchars($supplier['address'] ?? ''); ?></td>
                                        </tr>
                                        <tr>
                                            <th class="ps-0 text-muted">Code postal</th>
                                            <td class="text-end pe-0"><?php echo htmlspecialchars($supplier['postal_code'] ?? ''); ?></td>
                                        </tr>
                                        <tr>
                                            <th class="ps-0 text-muted">Ville</th>
                                            <td class="text-end pe-0"><?php echo htmlspecialchars($supplier['city'] ?? ''); ?></td>
                                        </tr>
                                        <tr>
                                            <th class="ps-0 text-muted">Pays</th>
                                            <td class="text-end pe-0"><?php echo htmlspecialchars($supplier['country'] ?? ''); ?></td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <div id="map" style="height: 200px; border-radius: 8px;"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Notes -->
                    <?php if (!empty($supplier['notes'])): ?>
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-light">
                                <h5 class="mb-0">
                                    <i class="fa fa-sticky-note me-2 text-primary"></i>
                                    Notes
                                </h5>
                            </div>
                            <div class="card-body">
                                <p class="mb-0"><?php echo nl2br(htmlspecialchars($supplier['notes'] ?? '')); ?></p>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Historique des véhicules -->
        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="fa fa-car me-2 text-primary"></i>
                        Véhicules fournis
                    </h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($vehicles)): ?>
                        <div class="list-group">
                            <?php foreach ($vehicles as $vehicle): ?>
                                <a href="/vehicles/view.php?id=<?php echo $vehicle['id']; ?>" 
                                   class="list-group-item list-group-item-action d-flex align-items-center py-3 px-3">
                                    <i class="fa fa-car text-primary me-3"></i>
                                    <div>
                                        <h6 class="mb-0">
                                            <?php echo htmlspecialchars($vehicle['brand'] . ' ' . $vehicle['model']); ?>
                                        </h6>
                                        <small class="text-muted">
                                            <?php echo htmlspecialchars($vehicle['year']); ?> - 
                                            <?php echo number_format($vehicle['price'], 2, ',', ' '); ?> €
                                        </small>
                                    </div>
                                    <span class="badge bg-<?php 
                                        echo $vehicle['status'] === 'available' ? 'success' : 
                                            ($vehicle['status'] === 'reserved' ? 'warning' : 'danger'); 
                                    ?> ms-auto">
                                        <?php 
                                        echo $vehicle['status'] === 'available' ? 'Disponible' : 
                                            ($vehicle['status'] === 'reserved' ? 'Réservé' : 'Vendu'); 
                                        ?>
                                    </span>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="fa fa-car fa-3x text-muted mb-3"></i>
                            <p class="text-muted mb-0">Aucun véhicule fourni</p>
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
    border-radius: 12px;
}

.card:hover {
    transform: translateY(-2px);
}

.card-header {
    border-radius: 12px 12px 0 0 !important;
}

.badge {
    font-size: 0.9rem;
    font-weight: 500;
}

.list-group-item {
    transition: all 0.2s ease;
    border: none;
    margin-bottom: 5px;
    border-radius: 8px !important;
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

.table th {
    font-weight: 500;
}
</style>

<?php require_once '../includes/footer.php'; ?> 