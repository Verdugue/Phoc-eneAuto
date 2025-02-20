<?php
session_start();
require_once '../config/database.php';
require_once '../init.php';

// Définir le titre avant d'inclure le header
$page_title = "Ajouter un véhicule";
require_once '../includes/header.php';

// Récupérer la liste des fournisseurs
try {
    $stmt = $pdo->query("SELECT id, name FROM suppliers WHERE is_active = true ORDER BY name");
    $suppliers = $stmt->fetchAll();
} catch (PDOException $e) {
    $_SESSION['error'] = "Erreur lors de la récupération des fournisseurs: " . $e->getMessage();
}
?>

<!-- Forcer le chargement de Bootstrap si nécessaire -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
/* Styles personnalisés pour écraser les styles existants */
.container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
}

.form-control, .form-select {
    display: block;
    width: 100%;
    padding: 0.375rem 0.75rem;
    font-size: 1rem;
    line-height: 1.5;
    border: 1px solid #ced4da;
    border-radius: 0.25rem;
    margin-bottom: 10px;
}

.form-label {
    margin-bottom: 0.5rem;
    font-weight: 500;
}

.btn-primary {
    color: #fff;
    background-color: #0d6efd;
    border-color: #0d6efd;
    padding: 0.375rem 0.75rem;
    border-radius: 0.25rem;
}
</style>

<div class="container mt-4">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h2 class="mb-0">Ajouter un véhicule</h2>
                </div>
                <div class="card-body">
                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="alert alert-danger">
                            <?php 
                            echo $_SESSION['error'];
                            unset($_SESSION['error']);
                            ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="process_add.php" enctype="multipart/form-data">
                        <!-- Informations de base -->
                        <div class="row mb-3">
                            <div class="col-md-3">
                                <label for="brand" class="form-label">Marque*</label>
                                <input type="text" class="form-control" id="brand" name="brand" required>
                            </div>
                            <div class="col-md-3">
                                <label for="model" class="form-label">Modèle*</label>
                                <input type="text" class="form-control" id="model" name="model" required>
                            </div>
                            <div class="col-md-3">
                                <label for="version" class="form-label">Version</label>
                                <input type="text" class="form-control" id="version" name="version">
                            </div>
                            <div class="col-md-3">
                                <label for="supplier_id" class="form-label">Fournisseur</label>
                                <select class="form-select" id="supplier_id" name="supplier_id">
                                    <option value="">Sélectionner un fournisseur</option>
                                    <?php foreach ($suppliers as $supplier): ?>
                                        <option value="<?= $supplier['id'] ?>"><?= htmlspecialchars($supplier['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <!-- Caractéristiques techniques -->
                        <div class="row mb-3">
                            <div class="col-md-3">
                                <label for="year" class="form-label">Année*</label>
                                <input type="number" class="form-control" id="year" name="year" required>
                            </div>
                            <div class="col-md-3">
                                <label for="mileage" class="form-label">Kilométrage*</label>
                                <input type="number" class="form-control" id="mileage" name="mileage" required>
                            </div>
                            <div class="col-md-3">
                                <label for="price" class="form-label">Prix*</label>
                                <input type="number" class="form-control" id="price" name="price" step="0.01" required>
                            </div>
                            <div class="col-md-3">
                                <label for="registration_date" class="form-label">Date d'immatriculation</label>
                                <input type="date" class="form-control" id="registration_date" name="registration_date">
                            </div>
                        </div>

                        <!-- État et localisation -->
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="vehicle_condition" class="form-label">État*</label>
                                <select class="form-select" id="vehicle_condition" name="vehicle_condition" required>
                                    <option value="new">Neuf</option>
                                    <option value="used">Occasion</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="color" class="form-label">Couleur*</label>
                                <input type="text" class="form-control" id="color" name="color" required>
                            </div>
                            <div class="col-md-4">
                                <label for="location" class="form-label">Emplacement</label>
                                <input type="text" class="form-control" id="location" name="location">
                            </div>
                        </div>

                        <!-- Motorisation -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="fuel_type" class="form-label">Carburant*</label>
                                <select class="form-select" id="fuel_type" name="fuel_type" required>
                                    <option value="Essence">Essence</option>
                                    <option value="Diesel">Diesel</option>
                                    <option value="Hybride">Hybride</option>
                                    <option value="Électrique">Électrique</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="transmission" class="form-label">Transmission*</label>
                                <select class="form-select" id="transmission" name="transmission" required>
                                    <option value="Manuelle">Manuelle</option>
                                    <option value="Automatique">Automatique</option>
                                </select>
                            </div>
                        </div>

                        <!-- Identification -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="registration_number" class="form-label">Numéro d'immatriculation*</label>
                                <input type="text" class="form-control" id="registration_number" name="registration_number" required>
                            </div>
                            <div class="col-md-6">
                                <label for="vin_number" class="form-label">Numéro de châssis (VIN)*</label>
                                <input type="text" class="form-control" id="vin_number" name="vin_number" required>
                            </div>
                        </div>

                        <!-- Options -->
                        <div class="mb-3">
                            <label for="options" class="form-label">Options</label>
                            <textarea class="form-control" id="options" name="options" rows="3" 
                                    placeholder="Entrez les options du véhicule (une par ligne)"></textarea>
                        </div>

                        <!-- Photos -->
                        <div class="mb-3">
                            <label for="vehicle_images" class="form-label">Photos du véhicule</label>
                            <input type="file" class="form-control" id="vehicle_images" name="vehicle_images[]" multiple accept="image/*">
                            <small class="text-muted">La première image sera utilisée comme image principale</small>
                        </div>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary">Ajouter le véhicule</button>
                            <a href="/vehicles/" class="btn btn-secondary">Annuler</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?> 