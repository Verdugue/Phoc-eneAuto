<?php
session_start();
require_once '../config/database.php';

$page_title = "Ajouter un véhicule";
require_once '../includes/header.php';

if (!isset($_GET['supplier_id']) || !is_numeric($_GET['supplier_id'])) {
    $_SESSION['error'] = "ID du fournisseur invalide";
    header('Location: /suppliers.php');
    exit;
}

$supplier_id = $_GET['supplier_id'];

try {
    // Récupérer les informations du fournisseur
    $stmt = $pdo->prepare("SELECT * FROM suppliers WHERE id = ?");
    $stmt->execute([$supplier_id]);
    $supplier = $stmt->fetch();

    if (!$supplier) {
        $_SESSION['error'] = "Fournisseur non trouvé";
        header('Location: /suppliers.php');
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Validation des données
        $required_fields = ['brand', 'model', 'year', 'mileage', 'price', 'vehicle_condition', 'color', 'fuel_type', 'transmission', 'registration_number', 'vin_number'];
        $errors = [];

        foreach ($required_fields as $field) {
            if (empty($_POST[$field])) {
                $errors[] = "Le champ " . str_replace('_', ' ', $field) . " est requis.";
            }
        }

        if (empty($errors)) {
            $stmt = $pdo->prepare("
                INSERT INTO vehicles (
                    brand, model, year, mileage, price,
                    vehicle_condition, color, fuel_type, transmission,
                    registration_number, vin_number, status, supplier_id
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'available', ?)
            ");

            $stmt->execute([
                $_POST['brand'],
                $_POST['model'],
                $_POST['year'],
                $_POST['mileage'],
                $_POST['price'],
                $_POST['vehicle_condition'],
                $_POST['color'],
                $_POST['fuel_type'],
                $_POST['transmission'],
                $_POST['registration_number'],
                $_POST['vin_number'],
                $supplier_id
            ]);

            $_SESSION['success'] = "Le véhicule a été ajouté avec succès.";
            header('Location: /suppliers/view.php?id=' . $supplier_id);
            exit;
        } else {
            $_SESSION['error'] = implode("<br>", $errors);
        }
    }
} catch (PDOException $e) {
    $_SESSION['error'] = "Erreur lors de l'ajout du véhicule: " . $e->getMessage();
}
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Ajouter un véhicule pour <?php echo htmlspecialchars($supplier['name']); ?></h1>
        <a href="/suppliers/view.php?id=<?php echo $supplier_id; ?>" class="btn btn-secondary">Retour</a>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="POST">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="brand" class="form-label">Marque *</label>
                        <input type="text" class="form-control" id="brand" name="brand" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="model" class="form-label">Modèle *</label>
                        <input type="text" class="form-control" id="model" name="model" required>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="year" class="form-label">Année *</label>
                        <input type="number" class="form-control" id="year" name="year" required min="1900" max="<?php echo date('Y') + 1; ?>">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="mileage" class="form-label">Kilométrage *</label>
                        <input type="number" class="form-control" id="mileage" name="mileage" required min="0">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="price" class="form-label">Prix *</label>
                        <input type="number" class="form-control" id="price" name="price" required min="0" step="0.01">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="vehicle_condition" class="form-label">État *</label>
                        <select class="form-control" id="vehicle_condition" name="vehicle_condition" required>
                            <option value="new">Neuf</option>
                            <option value="used">Occasion</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="color" class="form-label">Couleur *</label>
                        <input type="text" class="form-control" id="color" name="color" required>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="fuel_type" class="form-label">Carburant *</label>
                        <select class="form-control" id="fuel_type" name="fuel_type" required>
                            <option value="Essence">Essence</option>
                            <option value="Diesel">Diesel</option>
                            <option value="Électrique">Électrique</option>
                            <option value="Hybride">Hybride</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="transmission" class="form-label">Transmission *</label>
                        <select class="form-control" id="transmission" name="transmission" required>
                            <option value="Manuelle">Manuelle</option>
                            <option value="Automatique">Automatique</option>
                        </select>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="registration_number" class="form-label">Numéro d'immatriculation *</label>
                        <input type="text" class="form-control" id="registration_number" name="registration_number" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="vin_number" class="form-label">Numéro de série (VIN) *</label>
                        <input type="text" class="form-control" id="vin_number" name="vin_number" required>
                    </div>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">Ajouter le véhicule</button>
                    <a href="/suppliers/view.php?id=<?php echo $supplier_id; ?>" class="btn btn-secondary">Annuler</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?> 