<?php
session_start();
require_once '../config/database.php';

$page_title = "Recherche Avancée";
require_once '../includes/header.php';

// Récupérer les marques, modèles et années uniques pour les filtres
$brands = $pdo->query("SELECT DISTINCT brand FROM vehicles ORDER BY brand")->fetchAll(PDO::FETCH_COLUMN);
$models = $pdo->query("SELECT DISTINCT model FROM vehicles ORDER BY model")->fetchAll(PDO::FETCH_COLUMN);
$years = $pdo->query("SELECT DISTINCT year FROM vehicles ORDER BY year DESC")->fetchAll(PDO::FETCH_COLUMN);

// Construire la requête de recherche
if ($_SERVER['REQUEST_METHOD'] === 'GET' && !empty($_GET)) {
    $where_conditions = [];
    $params = [];

    if (!empty($_GET['brand'])) {
        $where_conditions[] = "brand = ?";
        $params[] = $_GET['brand'];
    }
    if (!empty($_GET['model'])) {
        $where_conditions[] = "model = ?";
        $params[] = $_GET['model'];
    }
    if (!empty($_GET['year'])) {
        $where_conditions[] = "year = ?";
        $params[] = $_GET['year'];
    }
    if (!empty($_GET['price_min'])) {
        $where_conditions[] = "price >= ?";
        $params[] = $_GET['price_min'];
    }
    if (!empty($_GET['price_max'])) {
        $where_conditions[] = "price <= ?";
        $params[] = $_GET['price_max'];
    }
    if (!empty($_GET['mileage_max'])) {
        $where_conditions[] = "mileage <= ?";
        $params[] = $_GET['mileage_max'];
    }
    if (!empty($_GET['fuel_type'])) {
        $where_conditions[] = "fuel_type = ?";
        $params[] = $_GET['fuel_type'];
    }
    if (!empty($_GET['transmission'])) {
        $where_conditions[] = "transmission = ?";
        $params[] = $_GET['transmission'];
    }
    if (!empty($_GET['condition'])) {
        $where_conditions[] = "vehicle_condition = ?";
        $params[] = $_GET['condition'];
    }
    if (!empty($_GET['status'])) {
        $where_conditions[] = "status = ?";
        $params[] = $_GET['status'];
    }

    $sql = "SELECT v.*, vi.file_path as image_path 
            FROM vehicles v 
            LEFT JOIN vehicle_images vi ON v.id = vi.vehicle_id AND vi.is_primary = true";

    if (!empty($where_conditions)) {
        $sql .= " WHERE " . implode(" AND ", $where_conditions);
    }

    $sql .= " ORDER BY v.created_at DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $vehicles = $stmt->fetchAll();
}
?>

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
                                    <option value="<?php echo $year; ?>"
                                            <?php echo isset($_GET['year']) && $_GET['year'] == $year ? 'selected' : ''; ?>>
                                        <?php echo $year; ?>
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
                        <h5 class="mb-0">Résultats (<?php echo count($vehicles); ?> véhicules)</h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($vehicles)): ?>
                            <div class="row">
                                <?php foreach ($vehicles as $vehicle): ?>
                                    <div class="col-md-6 col-lg-4 mb-4">
                                        <div class="card h-100">
                                            <img src="<?php echo $vehicle['image_path'] ?? '/assets/images/no-image.jpg'; ?>" 
                                                 class="card-img-top" 
                                                 style="height: 200px; object-fit: cover;"
                                                 alt="<?php echo htmlspecialchars($vehicle['brand'] . ' ' . $vehicle['model']); ?>">
                                            <div class="card-body">
                                                <h5 class="card-title">
                                                    <?php echo htmlspecialchars($vehicle['brand'] . ' ' . $vehicle['model']); ?>
                                                </h5>
                                                <p class="card-text">
                                                    <strong>Année :</strong> <?php echo $vehicle['year']; ?><br>
                                                    <strong>Prix :</strong> <?php echo number_format($vehicle['price'], 2, ',', ' '); ?> €<br>
                                                    <strong>Kilométrage :</strong> <?php echo number_format($vehicle['mileage'], 0, ',', ' '); ?> km
                                                </p>
                                                <div class="d-flex justify-content-between align-items-center">
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
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
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