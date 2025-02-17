<?php
session_start();
require_once '../config/database.php';

$page_title = "Détails du Véhicule";
require_once '../includes/header.php';

if (!isset($_GET['id'])) {
    $_SESSION['error'] = "ID du véhicule non spécifié";
    header('Location: index.php');
    exit;
}

try {
    // Récupérer les informations du véhicule
    $stmt = $pdo->prepare("
        SELECT v.*, 
               COUNT(vi.id) as total_images,
               (SELECT file_path FROM vehicle_images WHERE vehicle_id = v.id AND is_primary = true LIMIT 1) as primary_image
        FROM vehicles v
        LEFT JOIN vehicle_images vi ON v.id = vi.vehicle_id
        WHERE v.id = ?
        GROUP BY v.id
    ");
    $stmt->execute([$_GET['id']]);
    $vehicle = $stmt->fetch();

    if (!$vehicle) {
        throw new Exception("Véhicule non trouvé");
    }

    // Récupérer toutes les images du véhicule
    $stmt = $pdo->prepare("SELECT * FROM vehicle_images WHERE vehicle_id = ? ORDER BY is_primary DESC");
    $stmt->execute([$_GET['id']]);
    $images = $stmt->fetchAll();

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
</div>

<div class="card mb-4">
    <div class="card-body">
        <div class="row">
            <!-- Informations principales -->
            <div class="col-md-8">
                <h2 class="mb-3"><?php echo htmlspecialchars($vehicle['brand'] ?? '') . ' ' . htmlspecialchars($vehicle['model'] ?? ''); ?></h2>
                <div class="mb-4">
                    <h3 class="text-primary mb-0"><?php echo number_format($vehicle['price'] ?? 0, 2, ',', ' '); ?> €</h3>
                    <div class="mt-2">
                        <span class="badge bg-<?php echo $vehicle['vehicle_condition'] === 'new' ? 'success' : 'info'; ?> me-2">
                            <?php echo $vehicle['vehicle_condition'] === 'new' ? 'Neuf' : 'Occasion'; ?>
                        </span>
                        <span class="badge bg-<?php 
                            echo $vehicle['status'] === 'available' ? 'success' : 
                                ($vehicle['status'] === 'reserved' ? 'warning' : 'danger'); ?>">
                            <?php 
                            echo $vehicle['status'] === 'available' ? 'Disponible' : 
                                ($vehicle['status'] === 'reserved' ? 'Réservé' : 'Vendu'); 
                            ?>
                        </span>
                    </div>
                </div>

                <!-- Caractéristiques principales -->
                <div class="row mb-4">
                    <div class="col-sm-6 col-md-3 mb-3">
                        <div class="card h-100 bg-light">
                            <div class="card-body text-center">
                                <i class="fa fa-calendar fa-2x mb-2 text-primary"></i>
                                <h6 class="mb-1">Année</h6>
                                <p class="mb-0"><?php echo htmlspecialchars($vehicle['year'] ?? ''); ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-md-3 mb-3">
                        <div class="card h-100 bg-light">
                            <div class="card-body text-center">
                                <i class="fa fa-road fa-2x mb-2 text-primary"></i>
                                <h6 class="mb-1">Kilométrage</h6>
                                <p class="mb-0"><?php echo number_format($vehicle['mileage'] ?? 0, 0, ',', ' '); ?> km</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-md-3 mb-3">
                        <div class="card h-100 bg-light">
                            <div class="card-body text-center">
                                <i class="fa fa-tachometer fa-2x mb-2 text-primary"></i>
                                <h6 class="mb-1">Carburant</h6>
                                <p class="mb-0"><?php echo htmlspecialchars($vehicle['fuel_type'] ?? ''); ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-md-3 mb-3">
                        <div class="card h-100 bg-light">
                            <div class="card-body text-center">
                                <i class="fa fa-cog fa-2x mb-2 text-primary"></i>
                                <h6 class="mb-1">Transmission</h6>
                                <p class="mb-0"><?php echo htmlspecialchars($vehicle['transmission'] ?? ''); ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Informations détaillées -->
                <div class="row">
                    <div class="col-md-6">
                        <div class="card mb-3">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0"><i class="fa fa-info-circle me-2"></i>Caractéristiques</h5>
                            </div>
                            <div class="card-body">
                                <table class="table table-striped mb-0">
                                    <tbody>
                                        <tr>
                                            <th>Couleur</th>
                                            <td><?php echo htmlspecialchars($vehicle['color'] ?? ''); ?></td>
                                        </tr>
                                        <tr>
                                            <th>État</th>
                                            <td><?php echo $vehicle['vehicle_condition'] === 'new' ? 'Neuf' : 'Occasion'; ?></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card mb-3">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0"><i class="fa fa-file-alt me-2"></i>Informations légales</h5>
                            </div>
                            <div class="card-body">
                                <table class="table table-striped mb-0">
                                    <tbody>
                                        <tr>
                                            <th>Immatriculation</th>
                                            <td><?php echo htmlspecialchars($vehicle['registration_number'] ?? ''); ?></td>
                                        </tr>
                                        <tr>
                                            <th>N° de série</th>
                                            <td><?php echo htmlspecialchars($vehicle['vin'] ?? ''); ?></td>
                                        </tr>
                                        <tr>
                                            <th>Date d'ajout</th>
                                            <td><?php echo date('d/m/Y', strtotime($vehicle['created_at'] ?? 'now')); ?></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Galerie d'images -->
            <div class="col-md-4">
                <?php if (!empty($images)): ?>
                    <div id="vehicleCarousel" class="carousel slide" data-bs-ride="carousel">
                        <div class="carousel-inner">
                            <?php foreach ($images as $index => $image): ?>
                                <div class="carousel-item <?php echo $index === 0 ? 'active' : ''; ?>">
                                    <img src="<?php echo htmlspecialchars($image['file_path']); ?>" 
                                         class="d-block w-100" 
                                         alt="Photo véhicule"
                                         style="height: 400px; object-fit: cover;">
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <?php if (count($images) > 1): ?>
                            <button class="carousel-control-prev" type="button" data-bs-target="#vehicleCarousel" data-bs-slide="prev">
                                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                <span class="visually-hidden">Précédent</span>
                            </button>
                            <button class="carousel-control-next" type="button" data-bs-target="#vehicleCarousel" data-bs-slide="next">
                                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                <span class="visually-hidden">Suivant</span>
                            </button>
                        <?php endif; ?>
                    </div>
                    <div class="row mt-3">
                        <?php foreach ($images as $image): ?>
                            <div class="col-3 mb-3">
                                <img src="<?php echo htmlspecialchars($image['file_path']); ?>" 
                                     class="img-thumbnail w-100" 
                                     style="height: 80px; object-fit: cover; cursor: pointer;"
                                     onclick="showImage('<?php echo htmlspecialchars($image['file_path']); ?>')"
                                     alt="Miniature">
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center p-4">
                        <i class="fa fa-camera fa-3x text-muted"></i>
                        <p class="mt-2">Aucune photo disponible</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Modal pour afficher les images en grand -->
<div class="modal fade" id="imageModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-body p-0">
                <button type="button" class="btn-close position-absolute top-0 end-0 m-2" data-bs-dismiss="modal"></button>
                <img src="" class="w-100" id="modalImage" alt="Photo véhicule">
            </div>
        </div>
    </div>
</div>

<script>
const imageModal = new bootstrap.Modal(document.getElementById('imageModal'));
const modalImage = document.getElementById('modalImage');

function showImage(imagePath) {
    modalImage.src = imagePath;
    imageModal.show();
}

function deleteVehicle(id) {
    if (confirm('Êtes-vous sûr de vouloir supprimer ce véhicule ? Cette action est irréversible.')) {
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