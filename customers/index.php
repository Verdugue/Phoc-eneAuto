<?php
// Configuration de la pagination
$items_per_page = 10;
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($current_page - 1) * $items_per_page;

// Gestion du tri
$sort_field = isset($_GET['sort']) ? $_GET['sort'] : 'created_at';
$sort_order = isset($_GET['order']) ? $_GET['order'] : 'DESC';

// Valider les champs de tri autorisés
$allowed_sort_fields = ['last_name', 'first_name', 'email', 'phone', 'created_at'];
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

try {
    // Compter le nombre total de clients
    $total_items = $pdo->query("SELECT COUNT(*) FROM customers")->fetchColumn();
    $total_pages = ceil($total_items / $items_per_page);

    // Récupérer les clients avec tri et pagination
    $stmt = $pdo->prepare("
        SELECT * FROM customers 
        ORDER BY {$sort_field} {$sort_order}
        LIMIT :limit OFFSET :offset
    ");
    
    $stmt->bindValue(':limit', $items_per_page, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $customers = $stmt->fetchAll();
} catch (PDOException $e) {
    $_SESSION['error'] = "Erreur: " . $e->getMessage();
}
?>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-bordered align-middle">
                <thead>
                    <tr>
                        <th><a href="<?php echo getSortLink('last_name', $sort_field, $sort_order); ?>" class="text-light text-decoration-none">Nom</a></th>
                        <th><a href="<?php echo getSortLink('first_name', $sort_field, $sort_order); ?>" class="text-light text-decoration-none">Prénom</a></th>
                        <th><a href="<?php echo getSortLink('email', $sort_field, $sort_order); ?>" class="text-light text-decoration-none">Email</a></th>
                        <th><a href="<?php echo getSortLink('phone', $sort_field, $sort_order); ?>" class="text-light text-decoration-none">Téléphone</a></th>
                        <th class="text-light">Statut</th>
                        <th class="text-light">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- ... reste du tableau ... -->
                </tbody>
            </table>
        </div>
    </div>
</div> 