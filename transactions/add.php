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

                <div class="row mb-3">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Documents</h5>
                            </div>
                            <div class="card-body">
                                <div class="dropzone-container p-4 border rounded text-center" 
                                     id="dropzone" 
                                     ondrop="handleDrop(event)" 
                                     ondragover="handleDragOver(event)"
                                     ondragleave="handleDragLeave(event)">
                                    <i class="fa fa-file-pdf-o fa-2x mb-2 text-muted"></i>
                                    <p class="mb-2">Glissez et déposez vos documents ici</p>
                                    <p class="text-muted small mb-2">ou</p>
                                    <label class="btn btn-outline-primary mb-0">
                                        <input type="file" name="documents[]" id="fileInput" multiple accept=".pdf,.doc,.docx" style="display: none;" onchange="handleFiles(this.files)">
                                        Parcourir
                                    </label>
                                    <p class="text-muted small mt-2">PDF, Word (max 10 Mo)</p>
                                </div>
                                <div id="preview" class="mt-3"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">Enregistrer la transaction</button>
                    <a href="/transactions/" class="btn btn-secondary">Annuler</a>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
/* Style général du formulaire */
.form-control, .form-select {
    border-radius: 8px;
    padding: 0.75rem;
    border: 1px solid #dee2e6;
    transition: all 0.3s ease;
}

.form-control:focus, .form-select:focus {
    border-color: #0d6efd;
    box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.15);
}

.form-label {
    font-weight: 500;
    margin-bottom: 0.5rem;
    color: #495057;
}

/* Style de la zone de dépôt */
.dropzone-container {
    border: 2px dashed #dee2e6 !important;
    transition: all 0.3s ease;
    min-height: 200px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    border-radius: 12px;
    background-color: #f8f9fa;
}

.dropzone-container:hover {
    border-color: #0d6efd !important;
    background-color: #f1f4ff;
}

.dropzone-container.dragover {
    border-color: #0d6efd !important;
    background-color: #f1f4ff;
    transform: scale(1.01);
}

/* Style des prévisualisations de documents */
.document-preview {
    display: flex;
    align-items: center;
    padding: 12px;
    border: 1px solid #e9ecef;
    border-radius: 8px;
    margin-bottom: 10px;
    background-color: white;
    transition: all 0.2s ease;
    box-shadow: 0 1px 3px rgba(0,0,0,0.05);
}

.document-preview:hover {
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    transform: translateY(-1px);
}

.document-preview .fa {
    margin-right: 12px;
    color: #0d6efd;
}

.document-preview .remove-file {
    margin-left: auto;
    cursor: pointer;
    color: #dc3545;
    opacity: 0.7;
    transition: all 0.2s ease;
    padding: 5px;
}

.document-preview .remove-file:hover {
    opacity: 1;
    transform: scale(1.1);
}

/* Style des cartes */
.card {
    border: none;
    border-radius: 12px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}

.card-header {
    background-color: #f8f9fa;
    border-bottom: 1px solid #e9ecef;
    padding: 1rem 1.25rem;
    border-radius: 12px 12px 0 0 !important;
}

.card-body {
    padding: 1.5rem;
}

/* Style des boutons */
.btn {
    padding: 0.6rem 1.2rem;
    border-radius: 8px;
    font-weight: 500;
    transition: all 0.3s ease;
}

.btn-primary {
    background: linear-gradient(45deg, #0d6efd, #0b5ed7);
    border: none;
}

.btn-primary:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(13, 110, 253, 0.2);
}

.btn-outline-primary {
    border: 2px solid #0d6efd;
}

.btn-outline-primary:hover {
    background: linear-gradient(45deg, #0d6efd, #0b5ed7);
}

/* Animation pour les messages d'erreur */
@keyframes shake {
    0%, 100% { transform: translateX(0); }
    25% { transform: translateX(-5px); }
    75% { transform: translateX(5px); }
}

.is-invalid {
    animation: shake 0.4s ease-in-out;
}

/* Style responsive */
@media (max-width: 768px) {
    .card-body {
        padding: 1rem;
    }
    
    .dropzone-container {
        min-height: 150px;
    }
}

.document-preview select {
    margin: 0 10px;
    max-width: 200px;
}
</style>

<script>
function handleDrop(e) {
    e.preventDefault();
    e.stopPropagation();
    
    const dt = e.dataTransfer;
    const files = dt.files;
    
    handleFiles(files);
    document.getElementById('dropzone').classList.remove('dragover');
}

function handleDragOver(e) {
    e.preventDefault();
    e.stopPropagation();
    document.getElementById('dropzone').classList.add('dragover');
}

function handleDragLeave(e) {
    e.preventDefault();
    e.stopPropagation();
    document.getElementById('dropzone').classList.remove('dragover');
}

function handleFiles(files) {
    const preview = document.getElementById('preview');
    const maxSize = 10 * 1024 * 1024; // 10 Mo

    Array.from(files).forEach((file, index) => {
        if (file.size > maxSize) {
            alert(`Le fichier ${file.name} est trop volumineux. Taille maximum : 10 Mo`);
            return;
        }

        const div = document.createElement('div');
        div.className = 'document-preview';
        
        div.innerHTML = `
            <i class="fa fa-file-pdf-o"></i>
            <span>${file.name}</span>
            <select name="document_types[]" class="form-select form-select-sm mx-2" style="width: auto;" required>
                <option value="">Type de document</option>
                <option value="facture">Facture</option>
                <option value="carte_grise">Carte grise</option>
                <option value="controle_technique">Contrôle technique</option>
                <option value="assurance">Assurance</option>
                <option value="autre">Autre</option>
            </select>
            <i class="fa fa-times remove-file" onclick="this.parentElement.remove()"></i>
        `;
        
        preview.appendChild(div);
    });
}
</script>

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