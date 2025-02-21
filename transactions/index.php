<?php
session_start();
require_once '../config/database.php';

$page_title = "Gestion des Transactions";
require_once '../includes/header.php';

// Configuration de la pagination
$items_per_page = 10;
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($current_page - 1) * $items_per_page;

// Gestion du tri
$sort_field = isset($_GET['sort']) ? $_GET['sort'] : 'transaction_date';
$sort_order = isset($_GET['order']) ? $_GET['order'] : 'DESC';

// Valider les champs de tri autorisés
$allowed_sort_fields = ['transaction_date', 'invoice_number', 'price', 'transaction_type'];
if (!in_array($sort_field, $allowed_sort_fields)) {
    $sort_field = 'transaction_date';
}

// Valider l'ordre de tri
$sort_order = strtoupper($sort_order) === 'ASC' ? 'ASC' : 'DESC';

// Paramètres de recherche
$search = [
    'date_start' => $_GET['date_start'] ?? '',
    'date_end' => $_GET['date_end'] ?? '',
    'customer' => $_GET['customer'] ?? '',
    'vehicle' => $_GET['vehicle'] ?? '',
    'transaction_type' => $_GET['transaction_type'] ?? ''
];

try {
    // Construction de la requête avec les filtres
    $sql = "SELECT t.*, 
            c.first_name as customer_first_name, 
            c.last_name as customer_last_name,
            v.brand as vehicle_brand, 
            v.model as vehicle_model,
            u.username as user_name
            FROM transactions t
            LEFT JOIN customers c ON t.customer_id = c.id
            LEFT JOIN vehicles v ON t.vehicle_id = v.id
            LEFT JOIN users u ON t.user_id = u.id
            WHERE 1=1";
    
    $params = [];

    // Ajouter les conditions de recherche
    if (!empty($search['date_start'])) {
        $sql .= " AND DATE(t.transaction_date) >= ?";
        $params[] = $search['date_start'];
    }
    if (!empty($search['date_end'])) {
        $sql .= " AND DATE(t.transaction_date) <= ?";
        $params[] = $search['date_end'];
    }
    if (!empty($search['customer'])) {
        $sql .= " AND (LOWER(c.first_name) LIKE ? OR LOWER(c.last_name) LIKE ?)";
        $searchTerm = '%' . strtolower($search['customer']) . '%';
        $params[] = $searchTerm;
        $params[] = $searchTerm;
    }
    if (!empty($search['vehicle'])) {
        $sql .= " AND (LOWER(v.brand) LIKE ? OR LOWER(v.model) LIKE ?)";
        $searchTerm = '%' . strtolower($search['vehicle']) . '%';
        $params[] = $searchTerm;
        $params[] = $searchTerm;
    }
    if (!empty($search['transaction_type'])) {
        $sql .= " AND t.transaction_type = ?";
        $params[] = $search['transaction_type'];
    }

    $sql .= " ORDER BY t.transaction_date DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $transactions = $stmt->fetchAll();

} catch (PDOException $e) {
    $_SESSION['error'] = "Erreur lors de la récupération des transactions: " . $e->getMessage();
}

// Fonction pour générer les liens de tri
function getSortLink($field, $current_sort_field, $current_sort_order) {
    $order = ($field === $current_sort_field && $current_sort_order === 'ASC') ? 'DESC' : 'ASC';
    $icon = '';
    if ($field === $current_sort_field) {
        $icon = $current_sort_order === 'ASC' ? ' ↑' : ' ↓';
    }
    return "?sort={$field}&order={$order}&page=" . ($_GET['page'] ?? 1) . $icon;
}
?>

<div class="container mt-4">
    <!-- Formulaire de recherche -->
    <div class="card mb-4 shadow-sm">
        <div class="card-header bg-light">
            <h5 class="mb-0">Rechercher des transactions</h5>
        </div>
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Période</label>
                    <div class="input-group">
                        <input type="date" class="form-control" name="date_start" value="<?= htmlspecialchars($search['date_start']) ?>">
                        <span class="input-group-text">au</span>
                        <input type="date" class="form-control" name="date_end" value="<?= htmlspecialchars($search['date_end']) ?>">
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Client</label>
                    <input type="text" class="form-control" name="customer" placeholder="Nom du client" 
                           value="<?= htmlspecialchars($search['customer']) ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Véhicule</label>
                    <input type="text" class="form-control" name="vehicle" placeholder="Marque ou modèle"
                           value="<?= htmlspecialchars($search['vehicle']) ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Type</label>
                    <select class="form-select" name="transaction_type">
                        <option value="">Tous</option>
                        <option value="sale" <?= $search['transaction_type'] === 'sale' ? 'selected' : '' ?>>Vente</option>
                        <option value="purchase" <?= $search['transaction_type'] === 'purchase' ? 'selected' : '' ?>>Achat</option>
                    </select>
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-search me-2"></i>Rechercher
                    </button>
                    <a href="/transactions/" class="btn btn-outline-secondary">
                        <i class="fa fa-times me-2"></i>Réinitialiser
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Liste des transactions -->
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h4 class="mb-0">Transactions</h4>
            <a href="/transactions/add.php" class="btn btn-light">
                <i class="fa fa-plus me-2"></i>Nouvelle transaction
            </a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th style="color:black">Date</th>
                            <th style="color:black">N° Facture</th>
                            <th style="color:black">Client</th>
                            <th style="color:black">Véhicule</th>
                            <th style="color:black">Type</th>
                            <th style="color:black">Montant</th>
                            <th style="color:black">Paiement</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($transactions as $transaction): ?>
                            <tr onclick="window.location='/transactions/view.php?id=<?= $transaction['id'] ?>'" 
                                style="cursor: pointer">
                                <td><?= date('d/m/Y', strtotime($transaction['transaction_date'])) ?></td>
                                <td><?= htmlspecialchars($transaction['invoice_number']) ?></td>
                                <td><?= htmlspecialchars($transaction['customer_first_name'] . ' ' . $transaction['customer_last_name']) ?></td>
                                <td><?= htmlspecialchars($transaction['vehicle_brand'] . ' ' . $transaction['vehicle_model']) ?></td>
                                <td>
                                    <span class="badge bg-<?= $transaction['transaction_type'] === 'sale' ? 'success' : 'info' ?>">
                                        <?= $transaction['transaction_type'] === 'sale' ? 'Vente' : 'Achat' ?>
                                    </span>
                                </td>
                                <td><?= number_format($transaction['price'], 2, ',', ' ') ?> €</td>
                                <td><?= $transaction['payment_type'] === 'monthly' ? 'Mensuel' : 'Comptant' ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<style>
.table-hover tbody tr:hover {
    background-color: rgba(0,0,0,.075);
    transition: background-color 0.2s ease;
}
</style>

<?php require_once '../includes/footer.php'; ?> 