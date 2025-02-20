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
            $sql = "INSERT INTO suppliers (name, contact_name, email, phone, website, address, postal_code, city) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $_POST['name'],
                $_POST['contact_name'],
                $_POST['email'],
                $_POST['phone'],
                $_POST['website'] ?? null,
                $_POST['address'],
                $_POST['postal_code'],
                $_POST['city']
            ]);

            $_SESSION['success'] = "Fournisseur ajouté avec succès";
            header('Location: /suppliers/');
            exit;
            
        } catch (PDOException $e) {
            $errors[] = "Erreur lors de l'ajout du fournisseur: " . $e->getMessage();
        }
    }
}

// Si on arrive ici, c'est qu'on affiche le formulaire
$page_title = "Ajouter un fournisseur";
require_once '../includes/header.php';
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Ajouter un fournisseur</h1>
        <a href="/suppliers.php" class="btn btn-secondary">Retour</a>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="/suppliers/add.php">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="name" class="form-label">Nom de l'entreprise *</label>
                        <input type="text" class="form-control" id="name" name="name" required 
                               value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="contact_name" class="form-label">Nom du contact *</label>
                        <input type="text" class="form-control" id="contact_name" name="contact_name" required
                               value="<?php echo isset($_POST['contact_name']) ? htmlspecialchars($_POST['contact_name']) : ''; ?>">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="email" class="form-label">Email *</label>
                        <input type="email" class="form-control" id="email" name="email" required
                               value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="phone" class="form-label">Téléphone *</label>
                        <input type="tel" class="form-control" id="phone" name="phone" required
                               value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>">
                    </div>
                </div>

                <div class="mb-3">
                    <label for="website" class="form-label">Site Web</label>
                    <input type="url" class="form-control" id="website" name="website" placeholder="https://"
                           value="<?php echo isset($_POST['website']) ? htmlspecialchars($_POST['website']) : ''; ?>">
                </div>

                <div class="mb-3">
                    <label for="address" class="form-label">Adresse *</label>
                    <input type="text" class="form-control" id="address" name="address" required
                           value="<?php echo isset($_POST['address']) ? htmlspecialchars($_POST['address']) : ''; ?>">
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="postal_code" class="form-label">Code postal *</label>
                        <input type="text" class="form-control" id="postal_code" name="postal_code" required
                               value="<?php echo isset($_POST['postal_code']) ? htmlspecialchars($_POST['postal_code']) : ''; ?>">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="city" class="form-label">Ville *</label>
                        <input type="text" class="form-control" id="city" name="city" required
                               value="<?php echo isset($_POST['city']) ? htmlspecialchars($_POST['city']) : ''; ?>">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="country" class="form-label">Pays *</label>
                        <select class="form-select" id="country" name="country" required>
                            <option value="France" <?php echo (isset($_POST['country']) && $_POST['country'] === 'France') ? 'selected' : ''; ?>>France</option>
                            <option value="Belgique" <?php echo (isset($_POST['country']) && $_POST['country'] === 'Belgique') ? 'selected' : ''; ?>>Belgique</option>
                            <option value="Suisse" <?php echo (isset($_POST['country']) && $_POST['country'] === 'Suisse') ? 'selected' : ''; ?>>Suisse</option>
                            <option value="Luxembourg" <?php echo (isset($_POST['country']) && $_POST['country'] === 'Luxembourg') ? 'selected' : ''; ?>>Luxembourg</option>
                            <option value="Monaco" <?php echo (isset($_POST['country']) && $_POST['country'] === 'Monaco') ? 'selected' : ''; ?>>Monaco</option>
                        </select>
                    </div>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">Ajouter le fournisseur</button>
                    <a href="/suppliers.php" class="btn btn-secondary">Annuler</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?> 