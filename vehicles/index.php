<?php
session_start();
require_once '../config/database.php';

$page_title = "Gestion des Véhicules";
require_once '../includes/header.php';

// Configuration de la pagination
$items_per_page = 10; // Nombre d'éléments par page
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($current_page - 1) * $items_per_page;

// Gestion du tri
$sort_field = isset($_GET['sort']) ? $_GET['sort'] : 'created_at';
$sort_order = isset($_GET['order']) ? $_GET['order'] : 'DESC';

// Valider les champs de tri autorisés
$allowed_sort_fields = ['brand', 'model', 'year', 'price', 'mileage', 'created_at'];
if (!in_array($sort_field, $allowed_sort_fields)) {
    $sort_field = 'created_at';
}

// Valider l'ordre de tri
$sort_order = strtoupper($sort_order) === 'ASC' ? 'ASC' : 'DESC';

// Fonction pour générer les liens de tri
function getSortLink($field, $current_sort_field, $current_sort_order) {
    $order = ($field === $current_sort_field && $current_sort_order === 'ASC') ? 'DESC' : 'ASC';
    $icon = '';
    if ($field === $current_sort_field) {
        $icon = $current_sort_order === 'ASC' ? ' ↑' : ' ↓';
    }
    return "?sort={$field}&order={$order}&page=" . ($_GET['page'] ?? 1) . $icon;
}

// Récupérer la liste des véhicules avec leurs images principales
try {
    // Compter le nombre total de véhicules
    $total_items = $pdo->query("SELECT COUNT(*) FROM vehicles")->fetchColumn();
    $total_pages = ceil($total_items / $items_per_page);

    // Récupérer les véhicules pour la page courante
    $stmt = $pdo->prepare("
        SELECT v.*, vi.file_path as image_path, s.name as supplier_name 
        FROM vehicles v
        LEFT JOIN vehicle_images vi ON v.id = vi.vehicle_id AND vi.is_primary = true
        LEFT JOIN suppliers s ON v.supplier_id = s.id
        ORDER BY v.created_at DESC
        LIMIT :limit OFFSET :offset
    ");
    
    $stmt->bindValue(':limit', $items_per_page, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
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
            <table class="table table-striped table-bordered align-middle">
                <thead>
                    <tr>
                        <th>Photo</th>
                        <th><a href="<?php echo getSortLink('brand', $sort_field, $sort_order); ?>" class="text-light text-decoration-none">Marque</a></th>
                        <th><a href="<?php echo getSortLink('model', $sort_field, $sort_order); ?>" class="text-light text-decoration-none">Modèle</a></th>
                        <th><a href="<?php echo getSortLink('year', $sort_field, $sort_order); ?>" class="text-light text-decoration-none">Année</a></th>
                        <th><a href="<?php echo getSortLink('price', $sort_field, $sort_order); ?>" class="text-light text-decoration-none">Prix</a></th>
                        <th><a href="<?php echo getSortLink('mileage', $sort_field, $sort_order); ?>" class="text-light text-decoration-none">Kilométrage</a></th>
                        <th class="text-light">Statut</th>
                        <th class="text-light">Actions</th>
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
                            <td><?php echo htmlspecialchars($vehicle['mileage']); ?> km</td>
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

<?php if ($total_pages > 1): ?>
    <nav aria-label="Navigation des pages" class="mt-4">
        <ul class="pagination justify-content-center">
            <!-- Bouton "Précédent" -->
            <li class="page-item <?php echo $current_page <= 1 ? 'disabled' : ''; ?>">
                <a class="page-link" href="?page=<?php echo $current_page - 1; ?>" <?php echo $current_page <= 1 ? 'tabindex="-1" aria-disabled="true"' : ''; ?>>
                    Précédent
                </a>
            </li>
            
            <?php
            // Afficher les numéros de page
            $start_page = max(1, $current_page - 2);
            $end_page = min($total_pages, $current_page + 2);

            // Toujours montrer la première page
            if ($start_page > 1) {
                echo '<li class="page-item"><a class="page-link" href="?page=1">1</a></li>';
                if ($start_page > 2) {
                    echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                }
            }

            // Pages autour de la page courante
            for ($i = $start_page; $i <= $end_page; $i++) {
                echo '<li class="page-item ' . ($i == $current_page ? 'active' : '') . '">';
                echo '<a class="page-link" href="?page=' . $i . '">' . $i . '</a>';
                echo '</li>';
            }

            // Toujours montrer la dernière page
            if ($end_page < $total_pages) {
                if ($end_page < $total_pages - 1) {
                    echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                }
                echo '<li class="page-item"><a class="page-link" href="?page=' . $total_pages . '">' . $total_pages . '</a></li>';
            }
            ?>

            <!-- Bouton "Suivant" -->
            <li class="page-item <?php echo $current_page >= $total_pages ? 'disabled' : ''; ?>">
                <a class="page-link" href="?page=<?php echo $current_page + 1; ?>" <?php echo $current_page >= $total_pages ? 'tabindex="-1" aria-disabled="true"' : ''; ?>>
                    Suivant
                </a>
            </li>
        </ul>
    </nav>

    <div class="text-center text-muted">
        Page <?php echo $current_page; ?> sur <?php echo $total_pages; ?> 
        (<?php echo $total_items; ?> véhicules au total)
    </div>
<?php endif; ?>

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