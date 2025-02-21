<?php
session_start();
require_once 'config/database.php';

$page_title = "Tableau de Bord";
require_once 'includes/header.php';

// Récupérer les statistiques générales
try {
    // Nombre total de véhicules par statut
    $stmt = $pdo->query("
        SELECT 
            status, 
            COUNT(*) as count 
        FROM vehicles 
        GROUP BY status
    ");
    $vehicleStats = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

    // Calculer le CA du mois en cours
    $stmt = $pdo->prepare("
        SELECT COALESCE(SUM(price), 0) as monthly_revenue 
        FROM transactions 
        WHERE MONTH(transaction_date) = MONTH(CURRENT_DATE) 
        AND YEAR(transaction_date) = YEAR(CURRENT_DATE)
        AND transaction_type = 'sale'  -- Compter uniquement les ventes
    ");
    $stmt->execute();
    $monthly_revenue = $stmt->fetch()['monthly_revenue'];

    // Calculer les ventes des 6 derniers mois
    $stmt = $pdo->prepare("
        WITH RECURSIVE months AS (
            SELECT DATE_FORMAT(DATE_SUB(CURRENT_DATE, INTERVAL 5 MONTH), '%Y-%m-01') as month
            UNION ALL
            SELECT DATE_ADD(month, INTERVAL 1 MONTH)
            FROM months
            WHERE month < DATE_FORMAT(CURRENT_DATE, '%Y-%m-01')
        )
        SELECT 
            DATE_FORMAT(m.month, '%m/%Y') as month_label,
            COALESCE(COUNT(t.id), 0) as count,
            COALESCE(SUM(t.price), 0) as total
        FROM months m
        LEFT JOIN transactions t ON 
            DATE_FORMAT(t.transaction_date, '%Y-%m') = DATE_FORMAT(m.month, '%Y-%m')
            AND t.transaction_type = 'sale'
        GROUP BY m.month
        ORDER BY m.month ASC
    ");
    $stmt->execute();
    $salesStats = $stmt->fetchAll();

    // Préparer les données pour le graphique
    $months = array_column($salesStats, 'month_label');
    $sales = array_column($salesStats, 'total');
    $counts = array_column($salesStats, 'count');

    // Top 5 des marques les plus vendues
    $stmt = $pdo->query("
        SELECT v.brand, COUNT(*) as count
        FROM transactions t
        JOIN vehicles v ON t.vehicle_id = v.id
        WHERE t.transaction_type = 'sale'
        GROUP BY v.brand
        ORDER BY count DESC
        LIMIT 5
    ");
    $topBrands = $stmt->fetchAll();

    // Nombre de clients actifs
    $stmt = $pdo->query("
        SELECT COUNT(*) as count
        FROM customers
        WHERE is_active = true
    ");
    $activeCustomers = $stmt->fetch()['count'];

    // Modifier la requête des transactions récentes
    $stmt = $pdo->query("
        SELECT t.*, v.brand, v.model, v.version, v.location, c.first_name, c.last_name
        FROM transactions t
        JOIN vehicles v ON t.vehicle_id = v.id
        JOIN customers c ON t.customer_id = c.id
        WHERE t.transaction_type = 'sale'
        ORDER BY t.transaction_date DESC
        LIMIT 5
    ");
    $recentTransactions = $stmt->fetchAll();

} catch (PDOException $e) {
    $_SESSION['error'] = "Erreur lors de la récupération des statistiques: " . $e->getMessage();
    $monthly_revenue = 0;
    $months = [];
    $sales = [];
}
?>

<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body" style="cursor: pointer" onclick="window.location.href='/vehicles/search.php?status=available'">
                <h6 class="card-title">Véhicules disponibles</h6>
                <h2 class="mb-0"><?php echo $vehicleStats['available'] ?? 0; ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body" style="cursor: pointer" onclick="window.location.href='/transactions/'">
                <h6 class="card-title">CA du mois</h6>
                <h2 class="mb-0"><?php echo number_format($monthly_revenue, 2, ',', ' '); ?> €</h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-info text-white">
            <div class="card-body" style="cursor: pointer" onclick="window.location.href='/clients/'">
                <h6 class="card-title">Clients actifs</h6>
                <h2 class="mb-0"><?php echo $activeCustomers; ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-white">
            <div class="card-body" style="cursor: pointer" onclick="window.location.href='/vehicles/search.php?status=reserved'">
                <h6 class="card-title">Véhicules réservés</h6>
                <h2 class="mb-0"><?php echo $vehicleStats['reserved'] ?? 0; ?></h2>
            </div>
        </div>
    </div>
</div>

    <div class="row">
        <!-- Graphique des ventes -->
        <div class="col-md-8 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Évolution des ventes</h5>
                </div>
                <div class="card-body">
                    <canvas id="salesChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Graphique des marques -->
        <div class="col-md-4 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Top marques vendues</h5>
                </div>
                <div class="card-body">
                    <canvas id="brandsChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Graphique du stock -->
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">État du stock</h5>
                </div>
                <div class="card-body">
                    <canvas id="stockChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Dernières transactions -->
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Dernières transactions</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered align-middle">
                            <thead>
                                <tr>
                                    <th class="text-black">Date</th>
                                    <th class="text-black">Client</th>
                                    <th class="text-black">Véhicule</th>
                                    <th class="text-black">Version</th>
                                    <th class="text-black">Location</th>
                                    <th class="text-black">Montant</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentTransactions as $transaction): ?>
                                    <tr style="cursor: pointer" 
                                        onclick="window.location.href='/transactions/view.php?id=<?php echo $transaction['id']; ?>'"
                                        class="transaction-row">
                                        <td><?php echo date('d/m/Y', strtotime($transaction['transaction_date'])); ?></td>
                                        <td><?php echo htmlspecialchars(($transaction['first_name'] ?? '') . ' ' . ($transaction['last_name'] ?? '')); ?></td>
                                        <td><?php echo htmlspecialchars(($transaction['brand'] ?? '') . ' ' . ($transaction['model'] ?? '')); ?></td>
                                        <td><?php echo htmlspecialchars($transaction['version'] ?? ''); ?></td>
                                        <td><?php echo htmlspecialchars($transaction['location'] ?? ''); ?></td>
                                        <td><?php echo number_format($transaction['price'], 2, ',', ' '); ?> €</td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Inclure Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
// Graphique des ventes
const salesCtx = document.getElementById('salesChart').getContext('2d');
new Chart(salesCtx, {
    type: 'line',
    data: {
        labels: <?php echo json_encode($months); ?>,
        datasets: [
            {
                label: 'Chiffre d\'affaires (€)',
                data: <?php echo json_encode($sales); ?>,
                borderColor: 'rgb(49, 53, 102)',
                backgroundColor: 'rgba(49, 53, 102, 0.1)',
                yAxisID: 'y',
                tension: 0.1,
                fill: true
            }
        ]
    },
    options: {
        responsive: true,
        interaction: {
            mode: 'index',
            intersect: false,
        },
        scales: {
            y: {
                type: 'linear',
                display: true,
                position: 'left',
                title: {
                    display: true,
                    text: 'Chiffre d\'affaires (€)'
                },
                ticks: {
                    callback: function(value) {
                        return new Intl.NumberFormat('fr-FR', {
                            style: 'currency',
                            currency: 'EUR',
                            maximumFractionDigits: 0
                        }).format(value);
                    }
                }
            }
        }
    }
});

// Graphique des marques
const brandsCtx = document.getElementById('brandsChart').getContext('2d');
new Chart(brandsCtx, {
    type: 'doughnut',
    data: {
        labels: <?php echo json_encode(array_column($topBrands, 'brand')); ?>,
        datasets: [{
            data: <?php echo json_encode(array_column($topBrands, 'count')); ?>,
            backgroundColor: [
                'rgb(255, 99, 132)',
                'rgb(54, 162, 235)',
                'rgb(255, 205, 86)',
                'rgb(75, 192, 192)',
                'rgb(153, 102, 255)'
            ]
        }]
    }
});

// Graphique du stock
const stockCtx = document.getElementById('stockChart').getContext('2d');
new Chart(stockCtx, {
    type: 'pie',
    data: {
        labels: ['Disponible', 'Réservé', 'Vendu'],
        datasets: [{
            data: [
                <?php echo $vehicleStats['available'] ?? 0; ?>,
                <?php echo $vehicleStats['reserved'] ?? 0; ?>,
                <?php echo $vehicleStats['sold'] ?? 0; ?>
            ],
            backgroundColor: [
                'rgb(54, 162, 235)',
                'rgb(255, 205, 86)',
                'rgb(255, 99, 132)'
            ]
        }]
    }
});
</script>

<!-- Ajouter le style pour l'effet de survol -->
<style>
.transaction-row:hover {
    background-color: #f5f5f5;
    transition: background-color 0.2s ease;
}
</style>

</body>
</html> 