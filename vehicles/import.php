<?php
session_start();
require_once '../config/database.php';

$page_title = "Importer un véhicule";
require_once '../includes/header.php';

try {
    // Récupérer la liste des fournisseurs
    $stmt = $pdo->query("SELECT id, name FROM suppliers WHERE is_active = true ORDER BY name");
    $suppliers = $stmt->fetchAll();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Validation des données
        $required_fields = ['registration_number', 'vin_number', 'brand', 'model', 'year', 'supplier_id'];
        $errors = [];

        foreach ($required_fields as $field) {
            if (empty($_POST[$field])) {
                $errors[] = "Le champ " . str_replace('_', ' ', $field) . " est requis.";
            }
        }

        if (empty($errors)) {
            // Insérer le véhicule
            $stmt = $pdo->prepare("
                INSERT INTO vehicles (
                    registration_number, vin_number, brand, model, 
                    year, supplier_id, status, vehicle_condition,
                    mileage, price, fuel_type, transmission,
                    color, created_at, updated_at
                ) VALUES (
                    ?, ?, ?, ?, ?, ?, 'available', 'used',
                    0, 0, 'Essence', 'Manuelle',
                    NULL, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP
                )
            ");

            $stmt->execute([
                $_POST['registration_number'],
                $_POST['vin_number'],
                $_POST['brand'],
                $_POST['model'],
                $_POST['year'],
                $_POST['supplier_id']
            ]);

            $_SESSION['success'] = "Véhicule ajouté avec succès";
            header('Location: /vehicles/');
            exit;
        }
    }
} catch (Exception $e) {
    $_SESSION['error'] = "Erreur lors de l'importation: " . $e->getMessage();
}
?>

<div class="container mt-4">
    <div class="card">
        <div class="card-header">
            <h3>Importer un véhicule</h3>
        </div>
        <div class="card-body">
            <form method="POST" class="mb-4">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="supplier_id" class="form-label">Fournisseur *</label>
                            <select class="form-control" id="supplier_id" name="supplier_id" required>
                                <option value="">Sélectionnez un fournisseur</option>
                                <?php foreach ($suppliers as $supplier): ?>
                                    <option value="<?php echo $supplier['id']; ?>">
                                        <?php echo htmlspecialchars($supplier['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="registration_number" class="form-label">Immatriculation *</label>
                            <input type="text" class="form-control" id="registration_number" 
                                   name="registration_number" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="vin_number" class="form-label">Numéro de série (VIN) *</label>
                            <input type="text" class="form-control" id="vin_number" 
                                   name="vin_number" required>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="brand" class="form-label">Marque *</label>
                            <input type="text" class="form-control" id="brand" 
                                   name="brand" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="model" class="form-label">Modèle *</label>
                            <input type="text" class="form-control" id="model" 
                                   name="model" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="year" class="form-label">Année *</label>
                            <input type="number" class="form-control" id="year" 
                                   name="year" required min="1900" max="<?php echo date('Y') + 1; ?>">
                        </div>
                    </div>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">Ajouter le véhicule</button>
                    <a href="/vehicles/" class="btn btn-secondary">Annuler</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?> 