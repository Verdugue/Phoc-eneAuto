<?php
session_start();
require_once 'config/database.php';

$page_title = "Fournisseurs";
require_once 'includes/header.php';

try {
    // Récupérer tous les fournisseurs avec le nombre de véhicules
    $stmt = $pdo->query("
        SELECT 
            s.*,
            COUNT(v.id) as vehicle_count,
            SUM(CASE WHEN v.status = 'available' THEN 1 ELSE 0 END) as available_count
        FROM suppliers s
        LEFT JOIN vehicles v ON s.id = v.supplier_id
        GROUP BY s.id
        ORDER BY s.name
    ");
    $suppliers = $stmt->fetchAll();
} catch (PDOException $e) {
    $_SESSION['error'] = "Erreur lors de la récupération des fournisseurs: " . $e->getMessage();
}
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Fournisseurs</h1>
        <a href="suppliers/add.php" class="btn btn-primary">Ajouter un fournisseur</a>
    </div>

    <div class="row">
        <?php foreach ($suppliers as $supplier): ?>
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><?php echo htmlspecialchars($supplier['name']); ?></h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-8">
                                <p><strong>Contact:</strong> <?php echo htmlspecialchars($supplier['contact_name']); ?></p>
                                <p><strong>Email:</strong> <?php echo htmlspecialchars($supplier['email']); ?></p>
                                <p><strong>Téléphone:</strong> <?php echo htmlspecialchars($supplier['phone']); ?></p>
                                <p><strong>Adresse:</strong> <?php echo htmlspecialchars($supplier['address']); ?><br>
                                <?php echo htmlspecialchars($supplier['postal_code'] . ' ' . $supplier['city']); ?></p>
                            </div>
                            <div class="col-md-4 text-center">
                                <div class="mb-3">
                                    <h6>Véhicules</h6>
                                    <h3><?php echo $supplier['vehicle_count']; ?></h3>
                                </div>
                                <div>
                                    <h6>Disponibles</h6>
                                    <h3><?php echo $supplier['available_count']; ?></h3>
                                </div>
                            </div>
                        </div>
                        <div class="mt-3">
                            <a href="suppliers/view.php?id=<?php echo $supplier['id']; ?>" class="btn btn-info">Voir les véhicules</a>
                            <a href="suppliers/edit.php?id=<?php echo $supplier['id']; ?>" class="btn btn-secondary">Modifier</a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?> 