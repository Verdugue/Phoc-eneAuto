<?php
require_once __DIR__ . '/../init.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    // Sauvegarder l'URL actuelle pour rediriger après la connexion
    $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
    header('Location: /auth/login.php');
    exit;
}

$current_page = basename($_SERVER['PHP_SELF']);
$current_dir = basename(dirname($_SERVER['PHP_SELF']));
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' : ''; ?>Phocéenne Auto</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/font-awesome@4.7.0/css/font-awesome.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="/assets/css/style.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="/">Phocéenne Auto</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link <?php echo $current_dir === 'clients' ? 'active' : ''; ?>" href="/clients/">Clients</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($current_dir === 'vehicles' && $current_page !== 'search.php') ? 'active' : ''; ?>" href="/vehicles/">Véhicules</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $current_dir === 'transactions' ? 'active' : ''; ?>" href="/transactions/">Transactions</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($current_page === 'search.php' && $current_dir === 'vehicles') ? 'active' : ''; ?>" href="/vehicles/search.php">
                            <i class="fa fa-search"></i> Recherche Avancée
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $current_page === 'dashboard.php' ? 'active' : ''; ?>" href="/dashboard.php">
                            <i class="fa fa-dashboard"></i> Tableau de bord
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/suppliers.php">Fournisseurs</a>
                    </li>
                </ul>
                <div class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fa fa-user"></i> <?php echo htmlspecialchars($_SESSION['username']); ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="/profile.php">Mon Profil</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="/auth/logout.php">Déconnexion</a></li>
                        </ul>
                    </li>
                </div>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <?php 
                echo $_SESSION['success'];
                unset($_SESSION['success']);
                ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <?php 
                echo $_SESSION['error'];
                unset($_SESSION['error']);
                ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?> 