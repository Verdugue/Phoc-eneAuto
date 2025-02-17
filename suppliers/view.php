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
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><?php echo htmlspecialchars($supplier['name']); ?></h1>
        <div>
            <a href="/suppliers/add_vehicle.php?supplier_id=<?php echo $supplier['id']; ?>" class="btn btn-success">Ajouter un véhicule</a>
            <a href="/suppliers.php" class="btn btn-secondary">Retour</a>
            <a href="/suppliers/edit.php?id=<?php echo $supplier['id']; ?>" class="btn btn-primary">Modifier</a>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Informations du fournisseur</h5>
                </div>
                <div class="card-body">
                    <p><strong>Contact:</strong> <?php echo htmlspecialchars($supplier['contact_name']); ?></p>
                    <p><strong>Email:</strong> <?php echo htmlspecialchars($supplier['email']); ?></p>
                    <p><strong>Téléphone:</strong> <?php echo htmlspecialchars($supplier['phone']); ?></p>
                    <p><strong>Adresse:</strong><br>
                    <?php echo htmlspecialchars($supplier['address']); ?><br>
                    <?php echo htmlspecialchars($supplier['postal_code'] . ' ' . $supplier['city']); ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Statistiques</h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-4">
                            <h6>Total Véhicules</h6>
                            <h3><?php echo count($vehicles); ?></h3>
                        </div>
                        <div class="col-md-4">
                            <h6>Disponibles</h6>
                            <h3><?php echo count(array_filter($vehicles, function($v) { return $v['status'] === 'available'; })); ?></h3>
                        </div>
                        <div class="col-md-4">
                            <h6>Vendus</h6>
                            <h3><?php echo count(array_filter($vehicles, function($v) { return $v['status'] === 'sold'; })); ?></h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Véhicules</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Marque</th>
                            <th>Modèle</th>
                            <th>Année</th>
                            <th>Prix</th>
                            <th>Kilométrage</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($vehicles as $vehicle): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($vehicle['brand']); ?></td>
                                <td><?php echo htmlspecialchars($vehicle['model']); ?></td>
                                <td><?php echo htmlspecialchars($vehicle['year']); ?></td>
                                <td><?php echo number_format($vehicle['price'], 2, ',', ' '); ?> €</td>
                                <td><?php echo number_format($vehicle['mileage'], 0, ',', ' '); ?> km</td>
                                <td>
                                    <span class="badge bg-<?php 
                                        echo $vehicle['status'] === 'available' ? 'success' : 
                                            ($vehicle['status'] === 'reserved' ? 'warning' : 'secondary');
                                    ?>">
                                        <?php echo $vehicle['status_fr']; ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="/vehicles/view.php?id=<?php echo $vehicle['id']; ?>" class="btn btn-sm btn-info">Voir</a>
                                    <a href="/vehicles/edit.php?id=<?php echo $vehicle['id']; ?>" class="btn btn-sm btn-secondary">Modifier</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?> 