<?php
$page_title = "Page non trouvée";
require_once 'includes/header.php';
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6 text-center">
            <div class="error-template">
                <h1 class="display-1">404</h1>
                <h2>Oups! Page non trouvée</h2>
                <div class="error-details my-4">
                    Désolé, la page que vous recherchez n'existe pas ou a été déplacée.
                </div>
                <div class="error-actions">
                    <a href="/" class="btn btn-primary btn-lg">
                        <i class="fa fa-home"></i> Retour à l'accueil
                    </a>
                    <a href="javascript:history.back()" class="btn btn-secondary btn-lg ms-3">
                        <i class="fa fa-arrow-left"></i> Page précédente
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.error-template {
    padding: 40px 15px;
}
.error-template h1 {
    color: #dc3545;
    font-size: 8rem;
    margin-bottom: 20px;
}
.error-template h2 {
    font-size: 2rem;
    color: #6c757d;
    margin-bottom: 30px;
}
.error-details {
    font-size: 1.2rem;
    color: #6c757d;
}
.error-actions {
    margin-top: 30px;
}
</style>

<?php require_once 'includes/footer.php'; ?> 