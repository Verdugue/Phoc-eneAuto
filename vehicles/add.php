<?php
session_start();
require_once '../config/database.php';

$page_title = "Ajouter un véhicule";
require_once '../includes/header.php';

try {
    // Récupérer la liste des fournisseurs pour le menu déroulant
    $stmt = $pdo->query("SELECT id, name FROM suppliers WHERE is_active = true ORDER BY name");
    $suppliers = $stmt->fetchAll();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Ajouter supplier_id aux champs requis
        $required_fields = ['brand', 'model', 'year', 'mileage', 'price', 'vehicle_condition', 'color', 'fuel_type', 'transmission', 'registration_number', 'vin_number', 'supplier_id'];
        $errors = [];

        foreach ($required_fields as $field) {
            if (empty($_POST[$field])) {
                $errors[] = "Le champ " . str_replace('_', ' ', $field) . " est requis.";
            }
        }

        // Vérification de l'unicité du VIN et de l'immatriculation
        if (empty($errors)) {
            // Vérifier le VIN
            $stmt = $pdo->prepare("SELECT id FROM vehicles WHERE vin_number = ?");
            $stmt->execute([$_POST['vin_number']]);
            if ($stmt->fetch()) {
                $errors[] = "Ce numéro VIN existe déjà dans la base de données.";
            }

            // Vérifier l'immatriculation
            $stmt = $pdo->prepare("SELECT id FROM vehicles WHERE registration_number = ?");
            $stmt->execute([$_POST['registration_number']]);
            if ($stmt->fetch()) {
                $errors[] = "Ce numéro d'immatriculation existe déjà dans la base de données.";
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
                $_POST['supplier_id']
            ]);

            $_SESSION['success'] = "Le véhicule a été ajouté avec succès.";
            header('Location: /vehicles/');
            exit;
        } else {
            $_SESSION['error'] = implode("<br>", $errors);
        }
    }
} catch (PDOException $e) {
    $_SESSION['error'] = "Erreur lors de l'ajout du véhicule: " . $e->getMessage();
}
?>

<!-- Ajouter ce champ dans le formulaire existant, juste après le titre -->
<div class="row">
    <div class="col-md-6 mb-3">
        <label for="supplier_id" class="form-label">Fournisseur *</label>
        <select class="form-control" id="supplier_id" name="supplier_id" required>
            <option value="">Sélectionnez un fournisseur</option>
            <?php foreach ($suppliers as $supplier): ?>
                <option value="<?php echo $supplier['id']; ?>"
                    <?php echo (isset($_POST['supplier_id']) && $_POST['supplier_id'] == $supplier['id']) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($supplier['name']); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
</div>

<!-- Le reste du formulaire reste inchangé --> 