<?php
session_start();
require_once 'config/database.php';

$page_title = "Mon Profil";
require_once 'includes/header.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: auth/login.php');
    exit;
}

// Récupérer les informations de l'utilisateur
try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();

    // Récupérer les transactions de l'utilisateur
    $stmt = $pdo->prepare("
        SELECT t.*, v.brand, v.model, c.first_name, c.last_name 
        FROM transactions t
        JOIN vehicles v ON t.vehicle_id = v.id
        JOIN customers c ON t.customer_id = c.id
        WHERE t.user_id = ?
        ORDER BY t.transaction_date DESC
        LIMIT 5
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $transactions = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = "Erreur lors de la récupération des données: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil - Phocéenne Auto</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h3>Profil Utilisateur</h3>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <strong>Nom d'utilisateur:</strong>
                            <p><?php echo htmlspecialchars($user['username']); ?></p>
                        </div>
                        <div class="mb-3">
                            <strong>Email:</strong>
                            <p><?php echo htmlspecialchars($user['email']); ?></p>
                        </div>
                        <div class="mb-3">
                            <strong>Rôle:</strong>
                            <p><?php echo htmlspecialchars($user['role']); ?></p>
                        </div>
                        <div class="mb-3">
                            <strong>Date d'inscription:</strong>
                            <p><?php echo date('d/m/Y', strtotime($user['created_at'])); ?></p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h3>Dernières Transactions</h3>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($transactions)): ?>
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Client</th>
                                            <th>Véhicule</th>
                                            <th>Type</th>
                                            <th>Montant</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($transactions as $transaction): ?>
                                            <tr>
                                                <td><?php echo date('d/m/Y', strtotime($transaction['transaction_date'])); ?></td>
                                                <td><?php echo htmlspecialchars($transaction['first_name'] . ' ' . $transaction['last_name']); ?></td>
                                                <td><?php echo htmlspecialchars($transaction['brand'] . ' ' . $transaction['model']); ?></td>
                                                <td><?php echo $transaction['transaction_type'] === 'sale' ? 'Vente' : 'Achat'; ?></td>
                                                <td><?php echo number_format($transaction['price'], 2, ',', ' ') . ' €'; ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <p>Aucune transaction trouvée.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="container mt-4">
            <div class="mb-3">
                <a href="javascript:history.back()" class="btn btn-outline-secondary">
                    <i class="fa fa-arrow-left"></i> Retour
                </a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 