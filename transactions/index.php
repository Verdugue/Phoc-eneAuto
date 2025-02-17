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

try {
    // Compter le nombre total de transactions
    $total_items = $pdo->query("SELECT COUNT(*) FROM transactions")->fetchColumn();
    $total_pages = ceil($total_items / $items_per_page);

    // Requête avec tri et concaténation des noms
    $stmt = $pdo->prepare("
        SELECT 
            t.*,
            v.brand, 
            v.model,
            CONCAT(v.brand, ' ', v.model) as vehicle_info,
            c.first_name, 
            c.last_name,
            CONCAT(c.first_name, ' ', c.last_name) as client_name,
            u.username as vendeur
        FROM transactions t
        JOIN vehicles v ON t.vehicle_id = v.id
        JOIN customers c ON t.customer_id = c.id
        JOIN users u ON t.user_id = u.id
        ORDER BY t.{$sort_field} {$sort_order}
        LIMIT :limit OFFSET :offset
    ");
    
    $stmt->bindValue(':limit', $items_per_page, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
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

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>Gestion des Transactions</h1>
    <a href="add.php" class="btn btn-primary">
        <i class="fa fa-plus"></i> Nouvelle Transaction
    </a>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Client</th>
                        <th>Véhicule</th>
                        <th>Type</th>
                        <th>Montant</th>
                        <th>Statut</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($transactions as $transaction): ?>
                        <tr onclick="window.location='/transactions/view.php?id=<?php echo $transaction['id']; ?>'" 
                            style="cursor: pointer;">
                            <td><?php echo date('d/m/Y', strtotime($transaction['transaction_date'])); ?></td>
                            <td><?php echo htmlspecialchars($transaction['client_name']); ?></td>
                            <td><?php echo htmlspecialchars($transaction['vehicle_info']); ?></td>
                            <td>
                                <span class="badge bg-<?php echo $transaction['transaction_type'] === 'sale' ? 'success' : 'info'; ?>">
                                    <?php echo $transaction['transaction_type'] === 'sale' ? 'Vente' : 'Achat'; ?>
                                </span>
                            </td>
                            <td><?php echo number_format($transaction['price'], 2, ',', ' ') . ' €'; ?></td>
                            <td>
                                <span class="badge bg-<?php 
                                    echo isset($transaction['status']) && $transaction['status'] === 'completed' ? 'success' : 
                                        (isset($transaction['status']) && $transaction['status'] === 'cancelled' ? 'danger' : 'warning'); 
                                ?>">
                                    <?php 
                                    echo isset($transaction['status']) ? 
                                        ($transaction['status'] === 'completed' ? 'Terminée' : 
                                         ($transaction['status'] === 'cancelled' ? 'Annulée' : 'En cours')) 
                                        : 'En cours'; 
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
            <li class="page-item <?php echo $current_page <= 1 ? 'disabled' : ''; ?>">
                <a class="page-link" href="<?php echo getSortLink($sort_field, $sort_field, $sort_order); ?>&page=<?php echo $current_page - 1; ?>">Précédent</a>
            </li>
            
            <?php
            $start_page = max(1, $current_page - 2);
            $end_page = min($total_pages, $current_page + 2);

            if ($start_page > 1) {
                echo '<li class="page-item"><a class="page-link" href="' . getSortLink($sort_field, $sort_field, $sort_order) . '&page=1">1</a></li>';
                if ($start_page > 2) {
                    echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                }
            }

            for ($i = $start_page; $i <= $end_page; $i++) {
                echo '<li class="page-item ' . ($i == $current_page ? 'active' : '') . '">';
                echo '<a class="page-link" href="' . getSortLink($sort_field, $sort_field, $sort_order) . '&page=' . $i . '">' . $i . '</a>';
                echo '</li>';
            }

            if ($end_page < $total_pages) {
                if ($end_page < $total_pages - 1) {
                    echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                }
                echo '<li class="page-item"><a class="page-link" href="' . getSortLink($sort_field, $sort_field, $sort_order) . '&page=' . $total_pages . '">' . $total_pages . '</a></li>';
            }
            ?>

            <li class="page-item <?php echo $current_page >= $total_pages ? 'disabled' : ''; ?>">
                <a class="page-link" href="<?php echo getSortLink($sort_field, $sort_field, $sort_order); ?>&page=<?php echo $current_page + 1; ?>">Suivant</a>
            </li>
        </ul>
    </nav>

    <div class="text-center text-muted">
        Page <?php echo $current_page; ?> sur <?php echo $total_pages; ?> 
        (<?php echo $total_items; ?> transactions au total)
    </div>
<?php endif; ?>

<?php require_once '../includes/footer.php'; ?> 