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
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="/assets/css/style.css" rel="stylesheet">
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    
    <style>
        /* Correction du défilement horizontal */
        body {
            overflow-x: hidden;
            width: 100%;
        }
        
        .navbar {
            padding: 0.5rem 1rem; /* Réduit le padding vertical */
            background-color: rgb(49, 53, 102);
            box-shadow: 0 2px 4px rgba(0,0,0,.2);
        }

        .navbar-brand {
            font-weight: bold;
            color: white;
        }

        .navbar-nav {
            gap: 0.5rem; /* Espace entre les items */
        }

        .nav-item {
            margin: 0; /* Retire les marges par défaut */
        }

        .nav-link {
            padding: 0.5rem 0.75rem !important; /* Réduit le padding des liens */
            color: rgba(255,255,255,.9);
            transition: color 0.3s ease;
            font-size: 0.9rem; /* Réduit légèrement la taille de la police */
        }

        .navbar-nav .nav-item .nav-link {
            position: relative;
            padding-bottom: 0.5rem;
        }

        .navbar-nav .nav-item .nav-link.active::after {
            content: '';
            position: absolute;
            left: 0;
            bottom: 0;
            width: 100%;
            height: 3px;
            background-color: white;
            border-radius: 2px;
        }

        .navbar-nav .nav-item .nav-link:hover::after {
            content: '';
            position: absolute;
            left: 0;
            bottom: 0;
            width: 100%;
            height: 3px;
            background-color: rgba(255, 255, 255, 0.5);
            border-radius: 2px;
        }

        /* Ajustements pour le dropdown du profil */
        .navbar .dropdown-menu {
            margin-top: 0.5rem;
            padding: 0.5rem 0;
        }

        .navbar .dropdown-item {
            padding: 0.5rem 1rem;
            font-size: 0.9rem;
        }

        /* Responsive */
        @media (max-width: 992px) {
            .navbar-nav {
                padding: 0.5rem 0;
            }
            
            .nav-link {
                padding: 0.5rem !important;
            }
        }

        /* Style pour le dropdown */
        .dropdown-menu {
            background-color: white;  /* Retour au fond blanc */
            border: 1px solid rgba(0,0,0,.15);  /* Bordure standard */
        }

        .dropdown-item {
            color: #212529;  /* Couleur de texte standard */
        }

        .dropdown-item:hover {
            background-color: #f8f9fa;  /* Fond gris clair au survol */
            color: #16181b;  /* Texte plus foncé au survol */
        }

        /* Styles pour le dashboard */
        .dashboard-card {
            transition: transform 0.2s;
            cursor: pointer;
        }

        .dashboard-card:hover {
            transform: translateY(-5px);
        }

        .card-icon {
            font-size: 2rem;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="/">
                <i class="fa fa-car"></i> Phocéenne Auto
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link <?php echo $current_dir === 'clients' ? 'active' : ''; ?>" 
                           href="/clients/">
                            <i class="fa fa-users"></i> Clients
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($current_dir === 'vehicles' && $current_page !== 'search.php') ? 'active' : ''; ?>" 
                           href="/vehicles/">
                            <i class="fa fa-car"></i> Véhicules
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $current_dir === 'transactions' ? 'active' : ''; ?>" 
                           href="/transactions/">
                            <i class="fa fa-exchange"></i> Transactions
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $current_dir === 'suppliers' ? 'active' : ''; ?>" 
                           href="/suppliers/">
                            <i class="fa fa-truck"></i> Fournisseurs
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($current_page === 'search.php' && $current_dir === 'vehicles') ? 'active' : ''; ?>" 
                           href="/vehicles/search.php">
                            <i class="fa fa-search"></i> Recherche
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $current_page === 'dashboard.php' ? 'active' : ''; ?>" 
                           href="/dashboard.php">
                            <i class="fa fa-tachometer"></i> Tableau de bord
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $current_dir === 'parking' ? 'active' : ''; ?>" 
                           href="/parking/">
                            <i class="fa fa-car"></i> Parking
                        </a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" 
                           data-bs-toggle="dropdown" aria-expanded="false">
                            <img src="<?php echo get_profile_image($_SESSION['profile_image'] ?? null); ?>" 
                                 alt="Profile" 
                                 class="rounded-circle me-2"
                                 style="width: 30px; height: 30px; object-fit: cover;">
                            <?php echo htmlspecialchars($_SESSION['username']); ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                            <li>
                                <a class="dropdown-item <?php echo $current_page === 'profile.php' ? 'active' : ''; ?>" 
                                   href="/profile.php">
                                    <i class="fa fa-user-circle"></i> Mon Profil
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item text-danger" href="/auth/logout.php">
                                    <i class="fa fa-sign-out"></i> Déconnexion
                                </a>
                            </li>
                        </ul>
                    </li>
                </ul>
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