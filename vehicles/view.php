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

<div class="row">
    <!-- Galerie d'images -->
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header">
                <h3>Photos du Véhicule</h3>
            </div>
            <div class="card-body">
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

    <!-- Informations du véhicule -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3><?php echo htmlspecialchars($vehicle['brand'] . ' ' . $vehicle['model']); ?></h3>
                <div class="btn-group">
                    <a href="edit.php?id=<?php echo $vehicle['id']; ?>" class="btn btn-primary">
                        <i class="fa fa-edit"></i> Modifier
                    </a>
                    <?php if ($vehicle['status'] === 'available'): ?>
                        <button onclick="deleteVehicle(<?php echo $vehicle['id']; ?>)" 
                                class="btn btn-danger">
                            <i class="fa fa-trash"></i> Supprimer
                        </button>
                    <?php endif; ?>
                </div>
            </div>
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h5 class="text-primary">Prix</h5>
                        <h3><?php echo number_format($vehicle['price'], 2, ',', ' '); ?> €</h3>
                    </div>
                    <div class="col-md-6 text-end">
                        <span class="badge bg-<?php echo $vehicle['vehicle_condition'] === 'new' ? 'success' : 'info'; ?> mb-2">
                            <?php echo $vehicle['vehicle_condition'] === 'new' ? 'Neuf' : 'Occasion'; ?>
                        </span>
                        <br>
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

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <h5>Caractéristiques</h5>
                        <ul class="list-unstyled">
                            <li><strong>Année :</strong> <?php echo htmlspecialchars($vehicle['year']); ?></li>
                            <li><strong>Kilométrage :</strong> <?php echo number_format($vehicle['mileage'], 0, ',', ' '); ?> km</li>
                            <li><strong>Carburant :</strong> <?php echo htmlspecialchars($vehicle['fuel_type']); ?></li>
                            <li><strong>Transmission :</strong> <?php echo htmlspecialchars($vehicle['transmission']); ?></li>
                            <li><strong>Couleur :</strong> <?php echo htmlspecialchars($vehicle['color']); ?></li>
                        </ul>
                    </div>
                    <div class="col-md-6 mb-3">
                        <h5>Informations légales</h5>
                        <ul class="list-unstyled">
                            <li><strong>Immatriculation :</strong> <?php echo htmlspecialchars($vehicle['registration_number']); ?></li>
                            <li><strong>N° de série :</strong> <?php echo htmlspecialchars($vehicle['vin_number']); ?></li>
                            <li><strong>Date d'ajout :</strong> <?php echo date('d/m/Y', strtotime($vehicle['created_at'])); ?></li>
                            <li><strong>Dernière mise à jour :</strong> <?php echo date('d/m/Y', strtotime($vehicle['updated_at'])); ?></li>
                        </ul>
                    </div>
                </div>
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