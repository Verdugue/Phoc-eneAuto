<?php
session_start();
require_once '../config/database.php';

$page_title = "Recherche de Véhicules";
require_once '../includes/header.php';

// Configuration de la pagination
$items_per_page = 10;
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

// Récupérer les listes pour les filtres
try {
    // Liste des marques
    $stmt = $pdo->query("SELECT DISTINCT brand FROM vehicles ORDER BY brand");
    $brands = $stmt->fetchAll(PDO::FETCH_COLUMN);

    // Liste des années
    $stmt = $pdo->query("SELECT DISTINCT year FROM vehicles ORDER BY year DESC");
    $years = $stmt->fetchAll(PDO::FETCH_COLUMN);

    // Liste des modèles
    $stmt = $pdo->query("SELECT DISTINCT model FROM vehicles ORDER BY model");
    $models = $stmt->fetchAll(PDO::FETCH_COLUMN);

} catch (PDOException $e) {
    $_SESSION['error'] = "Erreur lors de la récupération des filtres: " . $e->getMessage();
}

try {
    $where_conditions = [];
    $params = [];
    $named_params = [];

    // Construire les conditions de recherche avec des paramètres nommés
    if (!empty($_GET['brand'])) {
        $where_conditions[] = "v.brand LIKE :brand";
        $named_params[':brand'] = '%' . $_GET['brand'] . '%';
    }
    if (!empty($_GET['model'])) {
        $where_conditions[] = "v.model LIKE :model";
        $named_params[':model'] = '%' . $_GET['model'] . '%';
    }
    if (!empty($_GET['year'])) {
        $where_conditions[] = "v.year = :year";
        $named_params[':year'] = $_GET['year'];
    }
    if (!empty($_GET['price_min'])) {
        $where_conditions[] = "v.price >= :price_min";
        $named_params[':price_min'] = $_GET['price_min'];
    }
    if (!empty($_GET['price_max'])) {
        $where_conditions[] = "v.price <= :price_max";
        $named_params[':price_max'] = $_GET['price_max'];
    }
    if (!empty($_GET['mileage_max'])) {
        $where_conditions[] = "v.mileage <= :mileage_max";
        $named_params[':mileage_max'] = $_GET['mileage_max'];
    }
    if (!empty($_GET['fuel_type'])) {
        $where_conditions[] = "v.fuel_type = :fuel_type";
        $named_params[':fuel_type'] = $_GET['fuel_type'];
    }
    if (!empty($_GET['transmission'])) {
        $where_conditions[] = "v.transmission = :transmission";
        $named_params[':transmission'] = $_GET['transmission'];
    }
    if (!empty($_GET['condition'])) {
        $where_conditions[] = "v.condition = :condition";
        $named_params[':condition'] = $_GET['condition'];
    }
    if (!empty($_GET['status'])) {
        $where_conditions[] = "v.status = :status";
        $named_params[':status'] = $_GET['status'];
    }

    $where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

    // Compter le nombre total de résultats
    $count_sql = "SELECT COUNT(*) FROM vehicles v " . $where_clause;
    $stmt = $pdo->prepare($count_sql);
    if (!empty($named_params)) {
        foreach ($named_params as $param => $value) {
            $stmt->bindValue($param, $value);
        }
    } else {
        $stmt->execute();
    }
    $total_items = $stmt->fetchColumn();
    $total_pages = ceil($total_items / $items_per_page);

    // Requête principale avec tri et pagination
    $sql = "
        SELECT v.*, vi.file_path as image_path 
        FROM vehicles v
        LEFT JOIN vehicle_images vi ON v.id = vi.vehicle_id AND vi.is_primary = true
        {$where_clause}
        ORDER BY v.{$sort_field} {$sort_order}
        LIMIT :limit OFFSET :offset
    ";

    $stmt = $pdo->prepare($sql);
    
    // Bind tous les paramètres nommés
    foreach ($named_params as $param => $value) {
        $stmt->bindValue($param, $value);
    }
    
    // Bind les paramètres de pagination
    $stmt->bindValue(':limit', $items_per_page, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    
    $stmt->execute();
    $vehicles = $stmt->fetchAll();

} catch (PDOException $e) {
    $_SESSION['error'] = "Erreur: " . $e->getMessage();
}

// Fonction pour générer les liens de tri avec conservation des paramètres de recherche
function getSortLink($field, $current_sort_field, $current_sort_order) {
    $params = $_GET;
    $order = ($field === $current_sort_field && $current_sort_order === 'ASC') ? 'DESC' : 'ASC';
    $params['sort'] = $field;
    $params['order'] = $order;
    $icon = '';
    if ($field === $current_sort_field) {
        $icon = $current_sort_order === 'ASC' ? ' ↑' : ' ↓';
    }
    return '?' . http_build_query($params) . $icon;
}

// Fonction pour générer les liens de pagination avec conservation des paramètres
function getPaginationLink($page) {
    $params = $_GET;
    $params['page'] = $page;
    return '?' . http_build_query($params);
}
?>

<style>
/* Style pour les bordures du tableau */
.table-bordered td, .table-bordered th {
    border-left: 1px solid #dee2e6;
    border-right: 1px solid #dee2e6;
}

/* Pour éviter la double bordure */
.table-bordered td:first-child, .table-bordered th:first-child {
    border-left: none;
}
.table-bordered td:last-child, .table-bordered th:last-child {
    border-right: none;
}
</style>

<div class="container mt-4">
    <div class="mb-3">
        <a href="javascript:history.back()" class="btn btn-outline-secondary">
            <i class="fa fa-arrow-left"></i> Retour
        </a>
    </div>
    <div class="row">
        <!-- Formulaire de recherche -->
        <div class="col-md-3">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Filtres de recherche</h5>
                </div>
                <div class="card-body">
                    <form method="GET" id="searchForm">
                        <div class="mb-3">
                            <label for="brand" class="form-label">Marque</label>
                            <select class="form-select" name="brand" id="brand">
                                <option value="">Toutes les marques</option>
                                <?php foreach ($brands as $brand): ?>
                                    <option value="<?php echo htmlspecialchars($brand); ?>"
                                            <?php echo isset($_GET['brand']) && $_GET['brand'] === $brand ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($brand); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="model" class="form-label">Modèle</label>
                            <select class="form-select" name="model" id="model">
                                <option value="">Tous les modèles</option>
                                <?php foreach ($models as $model): ?>
                                    <option value="<?php echo htmlspecialchars($model); ?>"
                                            <?php echo isset($_GET['model']) && $_GET['model'] === $model ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($model); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="year" class="form-label">Année</label>
                            <select class="form-select" name="year" id="year">
                                <option value="">Toutes les années</option>
                                <?php foreach ($years as $year): ?>
                                    <option value="<?php echo htmlspecialchars($year); ?>"
                                            <?php echo isset($_GET['year']) && $_GET['year'] == $year ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($year); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Prix</label>
                            <div class="row">
                                <div class="col-6">
                                    <input type="number" class="form-control" name="price_min" placeholder="Min" 
                                           value="<?php echo isset($_GET['price_min']) ? htmlspecialchars($_GET['price_min']) : ''; ?>">
                                </div>
                                <div class="col-6">
                                    <input type="number" class="form-control" name="price_max" placeholder="Max"
                                           value="<?php echo isset($_GET['price_max']) ? htmlspecialchars($_GET['price_max']) : ''; ?>">
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="mileage_max" class="form-label">Kilométrage max</label>
                            <input type="number" class="form-control" name="mileage_max" id="mileage_max"
                                   value="<?php echo isset($_GET['mileage_max']) ? htmlspecialchars($_GET['mileage_max']) : ''; ?>">
                        </div>

                        <div class="mb-3">
                            <label for="fuel_type" class="form-label">Carburant</label>
                            <select class="form-select" name="fuel_type" id="fuel_type">
                                <option value="">Tous types</option>
                                <option value="Essence" <?php echo isset($_GET['fuel_type']) && $_GET['fuel_type'] === 'Essence' ? 'selected' : ''; ?>>Essence</option>
                                <option value="Diesel" <?php echo isset($_GET['fuel_type']) && $_GET['fuel_type'] === 'Diesel' ? 'selected' : ''; ?>>Diesel</option>
                                <option value="Hybride" <?php echo isset($_GET['fuel_type']) && $_GET['fuel_type'] === 'Hybride' ? 'selected' : ''; ?>>Hybride</option>
                                <option value="Électrique" <?php echo isset($_GET['fuel_type']) && $_GET['fuel_type'] === 'Électrique' ? 'selected' : ''; ?>>Électrique</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="transmission" class="form-label">Transmission</label>
                            <select class="form-select" name="transmission" id="transmission">
                                <option value="">Toutes</option>
                                <option value="Manuelle" <?php echo isset($_GET['transmission']) && $_GET['transmission'] === 'Manuelle' ? 'selected' : ''; ?>>Manuelle</option>
                                <option value="Automatique" <?php echo isset($_GET['transmission']) && $_GET['transmission'] === 'Automatique' ? 'selected' : ''; ?>>Automatique</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="condition" class="form-label">État</label>
                            <select class="form-select" name="condition" id="condition">
                                <option value="">Tous</option>
                                <option value="new" <?php echo isset($_GET['condition']) && $_GET['condition'] === 'new' ? 'selected' : ''; ?>>Neuf</option>
                                <option value="used" <?php echo isset($_GET['condition']) && $_GET['condition'] === 'used' ? 'selected' : ''; ?>>Occasion</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="status" class="form-label">Statut</label>
                            <select class="form-select" name="status" id="status">
                                <option value="">Tous</option>
                                <option value="available" <?php echo isset($_GET['status']) && $_GET['status'] === 'available' ? 'selected' : ''; ?>>Disponible</option>
                                <option value="reserved" <?php echo isset($_GET['status']) && $_GET['status'] === 'reserved' ? 'selected' : ''; ?>>Réservé</option>
                                <option value="sold" <?php echo isset($_GET['status']) && $_GET['status'] === 'sold' ? 'selected' : ''; ?>>Vendu</option>
                            </select>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">Rechercher</button>
                            <button type="button" class="btn btn-secondary" onclick="resetForm()">Réinitialiser</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Résultats de recherche -->
        <div class="col-md-9">
            <?php if (isset($vehicles)): ?>
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Résultats (<?php echo $total_items; ?> véhicules)</h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($vehicles)): ?>
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
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($vehicles as $vehicle): ?>
                                            <tr>
                                                <td>
                                                    <img src="<?php echo $vehicle['image_path'] ?? '/assets/images/no-image.jpg'; ?>" 
                                                         class="img-fluid" 
                                                         style="max-height: 100px; object-fit: cover;"
                                                         alt="<?php echo htmlspecialchars($vehicle['brand'] . ' ' . $vehicle['model']); ?>">
                                                </td>
                                                <td><?php echo htmlspecialchars($vehicle['brand']); ?></td>
                                                <td><?php echo htmlspecialchars($vehicle['model']); ?></td>
                                                <td><?php echo $vehicle['year']; ?></td>
                                                <td><?php echo number_format($vehicle['price'], 2, ',', ' '); ?> €</td>
                                                <td><?php echo number_format($vehicle['mileage'], 0, ',', ' '); ?> km</td>
                                                <td>
                                                    <div class="d-flex justify-content-between align-items-center gap-2">
                                                        <span class="badge bg-<?php 
                                                            echo $vehicle['status'] === 'available' ? 'success' : 
                                                                ($vehicle['status'] === 'reserved' ? 'warning' : 'danger'); 
                                                        ?>">
                                                            <?php 
                                                            echo $vehicle['status'] === 'available' ? 'Disponible' : 
                                                                ($vehicle['status'] === 'reserved' ? 'Réservé' : 'Vendu'); 
                                                            ?>
                                                        </span>
                                                        <a href="view.php?id=<?php echo $vehicle['id']; ?>" class="btn btn-primary btn-sm">
                                                            Détails
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Pagination -->
                            <?php if ($total_pages > 1): ?>
                                <nav aria-label="Navigation des pages" class="mt-4">
                                    <ul class="pagination justify-content-center">
                                        <li class="page-item <?php echo $current_page <= 1 ? 'disabled' : ''; ?>">
                                            <a class="page-link" href="<?php echo getPaginationLink($current_page - 1); ?>">Précédent</a>
                                        </li>
                                        
                                        <?php
                                        $start_page = max(1, $current_page - 2);
                                        $end_page = min($total_pages, $current_page + 2);

                                        if ($start_page > 1) {
                                            echo '<li class="page-item"><a class="page-link" href="' . getPaginationLink(1) . '">1</a></li>';
                                            if ($start_page > 2) {
                                                echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                                            }
                                        }

                                        for ($i = $start_page; $i <= $end_page; $i++) {
                                            echo '<li class="page-item ' . ($i == $current_page ? 'active' : '') . '">';
                                            echo '<a class="page-link" href="' . getPaginationLink($i) . '">' . $i . '</a>';
                                            echo '</li>';
                                        }

                                        if ($end_page < $total_pages) {
                                            if ($end_page < $total_pages - 1) {
                                                echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                                            }
                                            echo '<li class="page-item"><a class="page-link" href="' . getPaginationLink($total_pages) . '">' . $total_pages . '</a></li>';
                                        }
                                        ?>

                                        <li class="page-item <?php echo $current_page >= $total_pages ? 'disabled' : ''; ?>">
                                            <a class="page-link" href="<?php echo getPaginationLink($current_page + 1); ?>">Suivant</a>
                                        </li>
                                    </ul>
                                </nav>

                                <div class="text-center text-muted">
                                    Page <?php echo $current_page; ?> sur <?php echo $total_pages; ?> 
                                    (<?php echo $total_items; ?> véhicules trouvés)
                                </div>
                            <?php endif; ?>
                        <?php else: ?>
                            <div class="text-center py-4">
                                <i class="fa fa-search fa-3x text-muted mb-3"></i>
                                <p class="lead">Aucun véhicule ne correspond à vos critères</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function resetForm() {
    document.getElementById('searchForm').reset();
    window.location.href = 'search.php';
}
</script> 