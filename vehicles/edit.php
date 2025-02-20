<?php
session_start();
require_once '../config/database.php';
require_once '../init.php';

// Récupérer les informations du véhicule si c'est une modification
if (isset($_GET['id'])) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM vehicles WHERE id = ?");
        $stmt->execute([$_GET['id']]);
        $vehicle = $stmt->fetch();
        
        if (!$vehicle) {
            $_SESSION['error'] = "Véhicule non trouvé";
            header('Location: index.php');
            exit;
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = "Erreur lors de la récupération du véhicule: " . $e->getMessage();
        header('Location: index.php');
        exit;
    }
}

$page_title = isset($_GET['id']) ? "Modifier un Véhicule" : "Ajouter un Véhicule";
require_once '../includes/header.php';
?>

<style>
.container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
}

/* Style de la carte principale */
.card {
    border: none;
    border-radius: 15px;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    margin-bottom: 2rem;
}

.card-header {
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    color: white;
    border-radius: 15px 15px 0 0 !important;
    padding: 1.5rem;
}

.card-body {
    padding: 2rem;
}

/* Style des champs de formulaire */
.form-label {
    font-weight: 500;
    color: var(--dark-gray);
    margin-bottom: 0.5rem;
}

.form-control, .form-select {
    border-radius: 8px;
    border: 1px solid #e2e8f0;
    padding: 0.75rem;
    transition: all 0.3s ease;
}

.form-control:focus, .form-select:focus {
    border-color: var(--accent-color);
    box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
}

/* Style des boutons */
.btn {
    padding: 0.75rem 1.5rem;
    border-radius: 8px;
    font-weight: 500;
    transition: all 0.3s ease;
}

.btn-primary {
    background: linear-gradient(135deg, var(--accent-color), #2980b9);
    border: none;
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(52, 152, 219, 0.3);
}

/* Style pour la zone de dépôt des images */
.image-upload-container {
    border: 2px dashed #e2e8f0;
    border-radius: 8px;
    padding: 2rem;
    text-align: center;
    transition: all 0.3s ease;
    background: #f8fafc;
}

.image-upload-container:hover {
    border-color: var(--accent-color);
    background: #f1f5f9;
}

/* Style pour les messages d'erreur */
.invalid-feedback {
    color: var(--danger-color);
    font-size: 0.875rem;
    margin-top: 0.25rem;
}

/* Responsive design */
@media (max-width: 768px) {
    .card-body {
        padding: 1.5rem;
    }
    
    .row {
        margin-bottom: 1rem;
    }
}
</style>

<div class="container mt-4">
    <div class="card">
        <div class="card-header">
            <h2 class="mb-0"><?php echo $page_title; ?></h2>
        </div>
        <div class="card-body">
            <form method="POST" action="process_edit.php<?php echo isset($_GET['id']) ? '?id='.$_GET['id'] : ''; ?>" enctype="multipart/form-data">
                <!-- Informations de base -->
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label for="brand" class="form-label">Marque*</label>
                        <input type="text" class="form-control" id="brand" name="brand" 
                               value="<?php echo htmlspecialchars($vehicle['brand'] ?? ''); ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label for="model" class="form-label">Modèle*</label>
                        <input type="text" class="form-control" id="model" name="model" 
                               value="<?php echo htmlspecialchars($vehicle['model'] ?? ''); ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label for="version" class="form-label">Version</label>
                        <input type="text" class="form-control" id="version" name="version" 
                               value="<?php echo htmlspecialchars($vehicle['version'] ?? ''); ?>">
                    </div>
                </div>

                <!-- Caractéristiques techniques -->
                <div class="row mb-3">
                    <div class="col-md-3">
                        <label for="year" class="form-label">Année*</label>
                        <input type="number" class="form-control" id="year" name="year" 
                               value="<?php echo htmlspecialchars($vehicle['year'] ?? date('Y')); ?>" required>
                    </div>
                    <div class="col-md-3">
                        <label for="mileage" class="form-label">Kilométrage*</label>
                        <input type="number" class="form-control" id="mileage" name="mileage" 
                               value="<?php echo htmlspecialchars($vehicle['mileage'] ?? '0'); ?>" required>
                    </div>
                    <div class="col-md-3">
                        <label for="price" class="form-label">Prix*</label>
                        <input type="number" class="form-control" id="price" name="price" step="0.01" 
                               value="<?php echo htmlspecialchars($vehicle['price'] ?? ''); ?>" required>
                    </div>
                    <div class="col-md-3">
                        <label for="registration_date" class="form-label">Date d'immatriculation</label>
                        <input type="date" class="form-control" id="registration_date" name="registration_date" 
                               value="<?php echo htmlspecialchars($vehicle['registration_date'] ?? ''); ?>">
                    </div>
                </div>

                <!-- État et localisation -->
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label for="vehicle_condition" class="form-label">État*</label>
                        <select class="form-select" id="vehicle_condition" name="vehicle_condition" required>
                            <option value="new" <?php echo ($vehicle['vehicle_condition'] ?? '') === 'new' ? 'selected' : ''; ?>>Neuf</option>
                            <option value="used" <?php echo ($vehicle['vehicle_condition'] ?? '') === 'used' ? 'selected' : ''; ?>>Occasion</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="color" class="form-label">Couleur*</label>
                        <input type="text" class="form-control" id="color" name="color" 
                               value="<?php echo htmlspecialchars($vehicle['color'] ?? ''); ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label for="location" class="form-label">Emplacement</label>
                        <input type="text" class="form-control" id="location" name="location" 
                               value="<?php echo htmlspecialchars($vehicle['location'] ?? ''); ?>">
                    </div>
                </div>

                <!-- Motorisation -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="fuel_type" class="form-label">Carburant*</label>
                        <select class="form-select" id="fuel_type" name="fuel_type" required>
                            <option value="Essence" <?php echo ($vehicle['fuel_type'] ?? '') === 'Essence' ? 'selected' : ''; ?>>Essence</option>
                            <option value="Diesel" <?php echo ($vehicle['fuel_type'] ?? '') === 'Diesel' ? 'selected' : ''; ?>>Diesel</option>
                            <option value="Hybride" <?php echo ($vehicle['fuel_type'] ?? '') === 'Hybride' ? 'selected' : ''; ?>>Hybride</option>
                            <option value="Électrique" <?php echo ($vehicle['fuel_type'] ?? '') === 'Électrique' ? 'selected' : ''; ?>>Électrique</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="transmission" class="form-label">Transmission*</label>
                        <select class="form-select" id="transmission" name="transmission" required>
                            <option value="Manuelle" <?php echo ($vehicle['transmission'] ?? '') === 'Manuelle' ? 'selected' : ''; ?>>Manuelle</option>
                            <option value="Automatique" <?php echo ($vehicle['transmission'] ?? '') === 'Automatique' ? 'selected' : ''; ?>>Automatique</option>
                        </select>
                    </div>
                </div>

                <!-- Identification -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="registration_number" class="form-label">Numéro d'immatriculation*</label>
                        <input type="text" class="form-control" id="registration_number" name="registration_number" 
                               value="<?php echo htmlspecialchars($vehicle['registration_number'] ?? ''); ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label for="vin_number" class="form-label">Numéro de châssis (VIN)*</label>
                        <input type="text" class="form-control" id="vin_number" name="vin_number" 
                               value="<?php echo htmlspecialchars($vehicle['vin_number'] ?? ''); ?>" required>
                    </div>
                </div>

                <!-- Options -->
                <div class="mb-3">
                    <label for="options" class="form-label">Options</label>
                    <textarea class="form-control" id="options" name="options" rows="3" 
                              placeholder="Entrez les options du véhicule (une par ligne)"><?php echo htmlspecialchars($vehicle['options'] ?? ''); ?></textarea>
                </div>

                <!-- Photos -->
                <div class="mb-3">
                    <label for="vehicle_images" class="form-label">Photos du véhicule</label>
                    <input type="file" class="form-control" id="vehicle_images" name="vehicle_images[]" multiple accept="image/*">
                    <small class="text-muted">La première image sera utilisée comme image principale</small>
                </div>

                <button type="submit" class="btn btn-primary">
                    <?php echo isset($_GET['id']) ? 'Modifier' : 'Ajouter'; ?> le véhicule
                </button>
            </form>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?> 