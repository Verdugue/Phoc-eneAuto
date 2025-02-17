<?php
session_start();
require_once '../config/database.php';

$page_title = "Gestion des Véhicules";
require_once '../includes/header.php';

// Récupérer la liste des véhicules avec leurs images principales
try {
    $stmt = $pdo->query("
        SELECT v.*, vi.file_path as image_path 
        FROM vehicles v 
        LEFT JOIN vehicle_images vi ON v.id = vi.vehicle_id AND vi.is_primary = true
        ORDER BY v.brand, v.model, v.year DESC
    ");
    $vehicles = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = "Erreur lors de la récupération des véhicules: " . $e->getMessage();
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>Gestion des Véhicules</h1>
    <a href="edit.php" class="btn btn-primary">
        <i class="fa fa-plus"></i> Nouveau Véhicule
    </a>
</div>

<?php if (isset($error)): ?>
    <div class="alert alert-danger"><?php echo $error; ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped align-middle">
                <thead>
                    <tr>
                        <th>Photo</th>
                        <th>Marque</th>
                        <th>Modèle</th>
                        <th>Année</th>
                        <th>Prix</th>
                        <th>État</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($vehicles as $vehicle): ?>
                        <tr>
                            <td style="width: 100px;">
                                <?php if ($vehicle['image_path']): ?>
                                    <img src="<?php echo htmlspecialchars($vehicle['image_path']); ?>" 
                                         class="img-thumbnail" 
                                         alt="Photo <?php echo htmlspecialchars($vehicle['brand'] . ' ' . $vehicle['model']); ?>"
                                         style="width: 80px; height: 60px; object-fit: cover;">
                                <?php else: ?>
                                    <div class="bg-light d-flex align-items-center justify-content-center" 
                                         style="width: 80px; height: 60px;">
                                        <i class="fa fa-camera text-muted"></i>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($vehicle['brand']); ?></td>
                            <td><?php echo htmlspecialchars($vehicle['model']); ?></td>
                            <td><?php echo htmlspecialchars($vehicle['year']); ?></td>
                            <td><?php echo number_format($vehicle['price'], 2, ',', ' ') . ' €'; ?></td>
                            <td>
                                <span class="badge bg-<?php echo $vehicle['vehicle_condition'] === 'new' ? 'success' : 'info'; ?>">
                                    <?php echo $vehicle['vehicle_condition'] === 'new' ? 'Neuf' : 'Occasion'; ?>
                                </span>
                            </td>
                            <td>
                                <?php
                                $statusClasses = [
                                    'available' => 'success',
                                    'reserved' => 'warning',
                                    'sold' => 'danger'
                                ];
                                $statusLabels = [
                                    'available' => 'Disponible',
                                    'reserved' => 'Réservé',
                                    'sold' => 'Vendu'
                                ];
                                ?>
                                <span class="badge bg-<?php echo $statusClasses[$vehicle['status']]; ?>">
                                    <?php echo $statusLabels[$vehicle['status']]; ?>
                                </span>
                            </td>
                            <td>
                                <div class="btn-group">
                                    <a href="edit.php?id=<?php echo $vehicle['id']; ?>" class="btn btn-sm btn-primary">
                                        <i class="fa fa-edit"></i>
                                    </a>
                                    <a href="view.php?id=<?php echo $vehicle['id']; ?>" class="btn btn-sm btn-info">
                                        <i class="fa fa-eye"></i>
                                    </a>
                                    <button onclick="deleteVehicle(<?php echo $vehicle['id']; ?>)" 
                                            class="btn btn-sm btn-danger" 
                                            <?php echo $vehicle['status'] !== 'available' ? 'disabled' : ''; ?>>
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function deleteVehicle(id) {
    if (confirm('Êtes-vous sûr de vouloir supprimer ce véhicule ?')) {
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
                location.reload();
            } else {
                alert(data.error);
            }
        });
    }
}
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 