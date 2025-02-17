<?php
session_start();
require_once '../config/database.php';

$page_title = "Nouvelle Transaction";
require_once '../includes/header.php';

try {
    // Récupérer la liste des clients
    $stmt = $pdo->query("SELECT id, first_name, last_name FROM customers WHERE is_active = true ORDER BY last_name, first_name");
    $clients = $stmt->fetchAll();

    // Récupérer la liste des véhicules disponibles
    $stmt = $pdo->query("SELECT id, brand, model, year, price FROM vehicles WHERE status = 'available' ORDER BY brand, model");
    $vehicles = $stmt->fetchAll();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Validation des données
        $required_fields = ['customer_id', 'vehicle_id', 'transaction_type', 'price', 'payment_method'];
        $errors = [];

        foreach ($required_fields as $field) {
            if (empty($_POST[$field])) {
                $errors[] = "Le champ " . str_replace('_', ' ', $field) . " est requis.";
            }
        }

        if (empty($errors)) {
            $pdo->beginTransaction();

            try {
                // Générer le numéro de facture
                $invoice_number = 'INV-' . date('Y') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);

                // Insérer la transaction
                $stmt = $pdo->prepare("
                    INSERT INTO transactions (
                        customer_id, vehicle_id, user_id, transaction_type,
                        price, payment_method, invoice_number, notes
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                ");

                $stmt->execute([
                    $_POST['customer_id'],
                    $_POST['vehicle_id'],
                    $_SESSION['user_id'],
                    $_POST['transaction_type'],
                    $_POST['price'],
                    $_POST['payment_method'],
                    $invoice_number,
                    $_POST['notes'] ?? null
                ]);

                $transaction_id = $pdo->lastInsertId(); // Récupérer l'ID de la transaction

                // Mettre à jour le statut du véhicule
                $stmt = $pdo->prepare("UPDATE vehicles SET status = 'sold' WHERE id = ?");
                $stmt->execute([$_POST['vehicle_id']]);

                // Gérer l'upload des documents
                if (!empty($_FILES['documents']['name'][0])) {
                    $upload_dir = '../uploads/transactions/' . $transaction_id . '/';
                    if (!file_exists($upload_dir)) {
                        mkdir($upload_dir, 0777, true);
                    }

                    foreach ($_FILES['documents']['tmp_name'] as $key => $tmp_name) {
                        if ($_FILES['documents']['error'][$key] === UPLOAD_ERR_OK) {
                            $file_name = $_FILES['documents']['name'][$key];
                            $file_type = $_POST['document_types'][$key];
                            
                            $extension = pathinfo($file_name, PATHINFO_EXTENSION);
                            $new_file_name = uniqid() . '.' . $extension;
                            $file_path = $upload_dir . $new_file_name;
                            
                            if (move_uploaded_file($tmp_name, $file_path)) {
                                $stmt = $pdo->prepare("
                                    INSERT INTO documents (
                                        transaction_id, document_type, file_name, file_path
                                    ) VALUES (?, ?, ?, ?)
                                ");
                                $stmt->execute([
                                    $transaction_id,
                                    $file_type,
                                    $new_file_name,
                                    '/uploads/transactions/' . $transaction_id . '/' . $new_file_name
                                ]);
                            }
                        }
                    }
                }

                if ($pdo->commit()) {
                    $_SESSION['success'] = "Transaction enregistrée avec succès";
                    header('Location: /transactions/');  // Retour à la liste des transactions
                    exit;
                }
            } catch (Exception $e) {
                $pdo->rollBack();
                throw $e;
            }
        }
    }
} catch (PDOException $e) {
    $_SESSION['error'] = "Erreur lors de l'ajout de la transaction: " . $e->getMessage();
}
?>

<div class="container mt-4">
    <div class="mb-3">
        <a href="/transactions/" class="btn btn-outline-secondary">
            <i class="fa fa-arrow-left"></i> Retour
        </a>
    </div>

    <div class="card">
        <div class="card-header">
            <h3>Nouvelle Transaction</h3>
        </div>
        <div class="card-body">
            <form method="POST" enctype="multipart/form-data">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="customer_id" class="form-label">Client *</label>
                            <select class="form-control" id="customer_id" name="customer_id" required>
                                <option value="">Sélectionnez un client</option>
                                <?php foreach ($clients as $client): ?>
                                    <option value="<?php echo $client['id']; ?>">
                                        <?php echo htmlspecialchars($client['last_name'] . ' ' . $client['first_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="vehicle_id" class="form-label">Véhicule *</label>
                            <select class="form-control" id="vehicle_id" name="vehicle_id" required>
                                <option value="">Sélectionnez un véhicule</option>
                                <?php foreach ($vehicles as $vehicle): ?>
                                    <option value="<?php echo $vehicle['id']; ?>" data-price="<?php echo $vehicle['price']; ?>">
                                        <?php echo htmlspecialchars($vehicle['brand'] . ' ' . $vehicle['model'] . ' (' . $vehicle['year'] . ')'); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="transaction_type" class="form-label">Type de transaction *</label>
                            <select class="form-control" id="transaction_type" name="transaction_type" required>
                                <option value="sale">Vente</option>
                                <option value="purchase">Achat</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="price" class="form-label">Prix *</label>
                            <input type="number" class="form-control" id="price" name="price" step="0.01" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="payment_method" class="form-label">Méthode de paiement *</label>
                            <select class="form-control" id="payment_method" name="payment_method" required>
                                <option value="card">Carte bancaire</option>
                                <option value="cash">Espèces</option>
                                <option value="transfer">Virement</option>
                                <option value="check">Chèque</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="notes" class="form-label">Notes</label>
                    <textarea class="form-control" id="notes" name="notes" rows="3"></textarea>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">Enregistrer la transaction</button>
                    <a href="/transactions/" class="btn btn-secondary">Annuler</a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Auto-remplir le prix quand un véhicule est sélectionné
document.getElementById('vehicle_id').addEventListener('change', function() {
    const selectedOption = this.options[this.selectedIndex];
    if (selectedOption.value) {
        document.getElementById('price').value = selectedOption.dataset.price;
    }
});
</script>

<?php require_once '../includes/footer.php'; ?> 