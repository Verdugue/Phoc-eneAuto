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
    // Construction de la requête avec les filtres
    $sql = "SELECT v.*, 
            s.name as supplier_name,
            (SELECT vi.file_path 
             FROM vehicle_images vi 
             WHERE vi.vehicle_id = v.id 
             AND vi.is_primary = 1 
             LIMIT 1) as primary_image
            FROM vehicles v
            LEFT JOIN suppliers s ON v.supplier_id = s.id";

    $where_conditions = [];
    $params = [];

    // Ajouter les conditions de recherche
    if (!empty($search['brand'])) {
        $where_conditions[] = "LOWER(v.brand) LIKE ?";
        $params[] = '%' . strtolower($search['brand']) . '%';
    }
    // ... autres conditions de recherche ...

    // Ajouter les conditions WHERE si elles existent
    if (!empty($where_conditions)) {
        $sql .= " WHERE " . implode(" AND ", $where_conditions);
    }

    // Grouper par véhicule pour éviter les doublons
    $sql .= " GROUP BY v.id";

    // Ajouter le tri
    $sql .= " ORDER BY v.$sort_field $sort_order";

    // D'abord, compter le nombre total de véhicules
    $count_sql = "SELECT COUNT(DISTINCT v.id) as total 
                  FROM vehicles v 
                  LEFT JOIN suppliers s ON v.supplier_id = s.id";
    
    if (!empty($where_conditions)) {
        $count_sql .= " WHERE " . implode(" AND ", $where_conditions);
    }
    
    $stmt = $pdo->prepare($count_sql);
    $stmt->execute($params);
    $total_items = $stmt->fetch()['total'];
    
    // Calculer le nombre total de pages
    $total_pages = ceil($total_items / $items_per_page);
    
    // Ajuster la requête principale pour la pagination
    $sql .= " LIMIT $items_per_page OFFSET $offset";
    
    // Exécuter la requête principale avec pagination
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $vehicles = $stmt->fetchAll();

} catch (PDOException $e) {
    $_SESSION['error'] = "Erreur lors de la récupération des véhicules : " . $e->getMessage();
    $vehicles = [];
    $total_items = 0;
    $total_pages = 1;
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
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Photo</th>
                        <th>Marque</th>
                        <th>Modèle</th>
                        <th>Année</th>
                        <th>Prix</th>
                        <th>Kilométrage</th>
                        <th>Statut</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($vehicles as $vehicle): ?>
                        <tr onclick="window.location='/vehicles/view.php?id=<?php echo $vehicle['id']; ?>'" 
                            style="cursor: pointer;">
                            <td style="width: 100px;">
                                <?php if ($vehicle['primary_image']): ?>
                                    <img src="<?php echo htmlspecialchars($vehicle['primary_image']); ?>" 
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
                            <td><?php echo number_format($vehicle['mileage'], 0, ',', ' '); ?> km</td>
                            <td>
                                <span class="badge bg-<?php 
                                    echo $vehicle['status'] === 'available' ? 'success' : 
                                        ($vehicle['status'] === 'reserved' ? 'warning' : 'danger'); 
                                ?>">
                                    <?php 
                                    echo $vehicle['status'] === 'available' ? 'Disponible' : 
                                        ($vehicle['status'] === 'reserved' ? 'Réservé' : 'Vendu'); 
                                    ?>
                                </span>
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