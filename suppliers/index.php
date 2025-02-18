<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

$page_title = "Fournisseurs";
require_once '../includes/header.php';

// Configuration de la pagination
$items_per_page = 10;
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($current_page - 1) * $items_per_page;

// Gestion du tri
$sort_field = isset($_GET['sort']) ? $_GET['sort'] : 'name';
$sort_order = isset($_GET['order']) ? $_GET['order'] : 'ASC';

// Valider les champs de tri autorisés
$allowed_sort_fields = ['name', 'email', 'phone'];
if (!in_array($sort_field, $allowed_sort_fields)) {
    $sort_field = 'name';
}

// Valider l'ordre de tri
$sort_order = strtoupper($sort_order) === 'ASC' ? 'ASC' : 'DESC';

try {
    // Compter le nombre total de fournisseurs
    $total_items = $pdo->query("SELECT COUNT(*) FROM suppliers")->fetchColumn();
    $total_pages = ceil($total_items / $items_per_page);

    // Récupérer les fournisseurs avec tri et pagination
    $stmt = $pdo->prepare("
        SELECT * FROM suppliers 
        ORDER BY {$sort_field} {$sort_order}
        LIMIT :limit OFFSET :offset
    ");
    
    $stmt->bindValue(':limit', $items_per_page, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $suppliers = $stmt->fetchAll();
} catch (PDOException $e) {
    $_SESSION['error'] = "Erreur: " . $e->getMessage();
}
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Fournisseurs</h1>
        <a href="add.php" class="btn btn-primary">
            <i class="fas fa-plus"></i> Nouveau Fournisseur
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-bordered table-hover align-middle">
                    <thead>
                        <tr>
                            <th><a href="<?php echo getSortLink('name', $sort_field, $sort_order); ?>" class="text-white text-decoration-none">Nom</a></th>
                            <th><a href="<?php echo getSortLink('email', $sort_field, $sort_order); ?>" class="text-white text-decoration-none">Email</a></th>
                            <th><a href="<?php echo getSortLink('phone', $sort_field, $sort_order); ?>" class="text-white text-decoration-none">Téléphone</a></th>
                            <th class="text-white">Adresse</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($suppliers as $supplier): ?>
                            <tr class="supplier-row" data-href="view.php?id=<?php echo $supplier['id']; ?>" style="cursor: pointer;">
                                <td><?php echo htmlspecialchars($supplier['name']); ?></td>
                                <td><?php echo htmlspecialchars($supplier['email']); ?></td>
                                <td><?php echo htmlspecialchars($supplier['phone']); ?></td>
                                <td>
                                    <?php echo htmlspecialchars($supplier['address']); ?><br>
                                    <?php echo htmlspecialchars($supplier['postal_code'] . ' ' . $supplier['city']); ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Ajouter ce script JavaScript avant la fermeture de la balise body -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const rows = document.querySelectorAll('.supplier-row');
    rows.forEach(row => {
        row.addEventListener('click', function() {
            window.location.href = this.dataset.href;
        });
    });
});
</script>

<style>
.supplier-row:hover {
    background-color: #f5f5f5 !important;
    transition: background-color 0.2s ease;
}
</style>

<?php require_once '../includes/footer.php'; ?> 