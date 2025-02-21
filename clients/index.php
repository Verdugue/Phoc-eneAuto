<?php
session_start();
require_once '../config/database.php';

$page_title = "Gestion des Clients";
require_once '../includes/header.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}

// Récupérer la liste des clients
try {
    $stmt = $pdo->query("SELECT * FROM customers WHERE is_active = true ORDER BY last_name, first_name");
    $clients = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = "Erreur lors de la récupération des clients: " . $e->getMessage();
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>Gestion des Clients</h1>
    <a href="edit.php" class="btn btn-primary">
        <i class="fa fa-plus"></i> Nouveau Client
    </a>
</div>

<?php if (isset($error)): ?>
    <div class="alert alert-danger"><?php echo $error; ?></div>
<?php endif; ?>

<?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success"><?php echo $_SESSION['success']; ?></div>
    <?php unset($_SESSION['success']); ?>
<?php endif; ?>

<div class="card">
    <div class="card-body">
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th style="color:black">Nom</th>
                        <th style="color:black">Email</th>
                        <th style="color:black">Téléphone</th>
                        <th style="color:black">Ville</th>
                        <th style="color:black">Statut</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($clients as $client): ?>
                        <tr onclick="window.location='/clients/view.php?id=<?php echo $client['id']; ?>'" 
                            style="cursor: pointer;">
                            <td><?php echo htmlspecialchars($client['last_name'] . ' ' . $client['first_name']); ?></td>
                            <td><?php echo htmlspecialchars($client['email']); ?></td>
                            <td><?php echo htmlspecialchars($client['phone']); ?></td>
                            <td><?php echo htmlspecialchars($client['city']); ?></td>
                            <td>
                                <span class="badge bg-<?php echo $client['is_active'] ? 'success' : 'danger'; ?>">
                                    <?php echo $client['is_active'] ? 'Actif' : 'Inactif'; ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function deleteClient(id) {
    if (confirm('Êtes-vous sûr de vouloir supprimer ce client ?')) {
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
                alert('Erreur lors de la suppression : ' + data.error);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Une erreur est survenue lors de la suppression');
        });
    }
}
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 