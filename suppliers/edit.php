<?php
session_start();
require_once '../config/database.php';

$page_title = "Modifier un fournisseur";
require_once '../includes/header.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error'] = "ID du fournisseur invalide";
    header('Location: /suppliers.php');
    exit;
}

$supplier_id = $_GET['id'];

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Validation des données
        $required_fields = ['name', 'contact_name', 'email', 'phone', 'address', 'postal_code', 'city'];
        $errors = [];

        foreach ($required_fields as $field) {
            if (empty($_POST[$field])) {
                $errors[] = "Le champ " . str_replace('_', ' ', $field) . " est requis.";
            }
        }

        if (!empty($_POST['email']) && !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = "L'adresse email n'est pas valide.";
        }

        // Vérifier si l'email existe déjà (sauf pour ce fournisseur)
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM suppliers WHERE email = ? AND id != ?");
        $stmt->execute([$_POST['email'], $supplier_id]);
        if ($stmt->fetchColumn() > 0) {
            $errors[] = "Cette adresse email est déjà utilisée.";
        }

        if (empty($errors)) {
            $stmt = $pdo->prepare("
                UPDATE suppliers SET 
                    name = ?,
                    contact_name = ?,
                    email = ?,
                    phone = ?,
                    address = ?,
                    postal_code = ?,
                    city = ?,
                    updated_at = CURRENT_TIMESTAMP
                WHERE id = ?
            ");

            $stmt->execute([
                $_POST['name'],
                $_POST['contact_name'],
                $_POST['email'],
                $_POST['phone'],
                $_POST['address'],
                $_POST['postal_code'],
                $_POST['city'],
                $supplier_id
            ]);

            $_SESSION['success'] = "Le fournisseur a été modifié avec succès.";
            header('Location: /suppliers.php');
            exit;
        } else {
            $_SESSION['error'] = implode("<br>", $errors);
        }
    }

    // Récupérer les informations du fournisseur
    $stmt = $pdo->prepare("SELECT * FROM suppliers WHERE id = ?");
    $stmt->execute([$supplier_id]);
    $supplier = $stmt->fetch();

    if (!$supplier) {
        $_SESSION['error'] = "Fournisseur non trouvé";
        header('Location: /suppliers.php');
        exit;
    }

} catch (PDOException $e) {
    $_SESSION['error'] = "Erreur lors de la modification du fournisseur: " . $e->getMessage();
}
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Modifier le fournisseur</h1>
        <a href="/suppliers.php" class="btn btn-secondary">Retour</a>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="POST">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="name" class="form-label">Nom de l'entreprise *</label>
                        <input type="text" class="form-control" id="name" name="name" required 
                               value="<?php echo htmlspecialchars($supplier['name']); ?>">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="contact_name" class="form-label">Nom du contact *</label>
                        <input type="text" class="form-control" id="contact_name" name="contact_name" required
                               value="<?php echo htmlspecialchars($supplier['contact_name']); ?>">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="email" class="form-label">Email *</label>
                        <input type="email" class="form-control" id="email" name="email" required
                               value="<?php echo htmlspecialchars($supplier['email']); ?>">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="phone" class="form-label">Téléphone *</label>
                        <input type="tel" class="form-control" id="phone" name="phone" required
                               value="<?php echo htmlspecialchars($supplier['phone']); ?>">
                    </div>
                </div>

                <div class="mb-3">
                    <label for="address" class="form-label">Adresse *</label>
                    <input type="text" class="form-control" id="address" name="address" required
                           value="<?php echo htmlspecialchars($supplier['address']); ?>">
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="postal_code" class="form-label">Code postal *</label>
                        <input type="text" class="form-control" id="postal_code" name="postal_code" required
                               value="<?php echo htmlspecialchars($supplier['postal_code']); ?>">
                    </div>
                    <div class="col-md-8 mb-3">
                        <label for="city" class="form-label">Ville *</label>
                        <input type="text" class="form-control" id="city" name="city" required
                               value="<?php echo htmlspecialchars($supplier['city']); ?>">
                    </div>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">Enregistrer les modifications</button>
                    <a href="/suppliers.php" class="btn btn-secondary">Annuler</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?> 