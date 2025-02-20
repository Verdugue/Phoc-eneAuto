<?php
session_start();
require_once '../config/database.php';

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $required_fields = ['name', 'contact_name', 'email', 'phone', 'address', 'postal_code', 'city'];
    $errors = [];

    // Validation des champs requis
    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            $errors[] = "Le champ " . str_replace('_', ' ', $field) . " est requis.";
        }
    }

    if (empty($errors)) {
        try {
            $sql = "UPDATE suppliers 
                    SET name = ?, contact_name = ?, email = ?, phone = ?, 
                        website = ?, address = ?, postal_code = ?, city = ?, country = ?
                    WHERE id = ?";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $_POST['name'],
                $_POST['contact_name'],
                $_POST['email'],
                $_POST['phone'],
                $_POST['website'] ?? null,
                $_POST['address'],
                $_POST['postal_code'],
                $_POST['city'],
                $_POST['country'],
                $_POST['id']
            ]);

            $_SESSION['success'] = "Fournisseur modifié avec succès";
            header('Location: /suppliers/');
            exit;
            
        } catch (PDOException $e) {
            $errors[] = "Erreur lors de la modification du fournisseur: " . $e->getMessage();
        }
    }
}

// Récupération du fournisseur
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error'] = "ID de fournisseur invalide";
    header('Location: /suppliers/');
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT * FROM suppliers WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    $supplier = $stmt->fetch();

    if (!$supplier) {
        $_SESSION['error'] = "Fournisseur non trouvé";
        header('Location: /suppliers/');
        exit;
    }
} catch (PDOException $e) {
    $_SESSION['error'] = "Erreur lors de la récupération du fournisseur: " . $e->getMessage();
    header('Location: /suppliers/');
    exit;
}

// Si on arrive ici, on peut afficher le formulaire
$page_title = "Modifier un fournisseur";
require_once '../includes/header.php';
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Modifier le fournisseur</h1>
        <a href="/suppliers.php" class="btn btn-secondary">Retour</a>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="POST">
                <input type="hidden" name="id" value="<?php echo htmlspecialchars($supplier['id']); ?>">

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
                    <label for="website" class="form-label">Site Web</label>
                    <input type="url" class="form-control" id="website" name="website" placeholder="https://"
                           value="<?php echo htmlspecialchars($supplier['website'] ?? ''); ?>">
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
                    <div class="col-md-4 mb-3">
                        <label for="city" class="form-label">Ville *</label>
                        <input type="text" class="form-control" id="city" name="city" required
                               value="<?php echo htmlspecialchars($supplier['city']); ?>">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="country" class="form-label">Pays *</label>
                        <select class="form-select" id="country" name="country" required>
                            <option value="France" <?php echo $supplier['country'] === 'France' ? 'selected' : ''; ?>>France</option>
                            <option value="Belgique" <?php echo $supplier['country'] === 'Belgique' ? 'selected' : ''; ?>>Belgique</option>
                            <option value="Suisse" <?php echo $supplier['country'] === 'Suisse' ? 'selected' : ''; ?>>Suisse</option>
                            <option value="Luxembourg" <?php echo $supplier['country'] === 'Luxembourg' ? 'selected' : ''; ?>>Luxembourg</option>
                            <option value="Monaco" <?php echo $supplier['country'] === 'Monaco' ? 'selected' : ''; ?>>Monaco</option>
                        </select>
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