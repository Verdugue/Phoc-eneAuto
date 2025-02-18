<?php
session_start();
require_once '../includes/init.php';

$page_title = "Nouvelle Transaction";
require_once '../includes/header.php';

try {
    // Récupérer la liste des clients
    $stmt = $pdo->query("SELECT id, first_name, last_name FROM customers WHERE is_active = true ORDER BY last_name, first_name");
    $clients = $stmt->fetchAll();

    // Récupérer la liste des véhicules disponibles uniquement
    $stmt = $pdo->query("SELECT id, brand, model, year, price 
                         FROM vehicles 
                         WHERE status = 'available' 
                         ORDER BY brand, model");
    $vehicles = $stmt->fetchAll();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Validation des données
        $required_fields = ['customer_id', 'vehicle_id', 'transaction_type', 'price', 'payment_method', 'payment_type'];
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

                // Préparer les données de base de la transaction
                $data = [
                    'customer_id' => $_POST['customer_id'],
                    'vehicle_id' => $_POST['vehicle_id'],
                    'user_id' => $_SESSION['user_id'],
                    'transaction_type' => $_POST['transaction_type'],
                    'price' => $_POST['price'],
                    'payment_method' => $_POST['payment_method'],
                    'payment_type' => $_POST['payment_type'],
                    'invoice_number' => $invoice_number,
                    'notes' => $_POST['notes'] ?? null,
                    'status' => 'completed'
                ];

                // Avant d'insérer la transaction, vérifier si le véhicule est disponible
                if ($data['transaction_type'] === 'sale') {
                    $stmt = $pdo->prepare("SELECT status FROM vehicles WHERE id = ?");
                    $stmt->execute([$data['vehicle_id']]);
                    $vehicle_status = $stmt->fetchColumn();

                    if ($vehicle_status !== 'available') {
                        throw new Exception("Ce véhicule n'est plus disponible à la vente");
                    }
                }

                // Ajouter les champs pour le paiement mensuel si nécessaire
                if ($_POST['payment_type'] === 'monthly') {
                    $data['installments'] = $_POST['installments'];
                    $data['first_payment_date'] = $_POST['first_payment_date'];
                    
                    // Calculer le montant restant après l'acompte
                    $down_payment = floatval($_POST['down_payment']);
                    $remaining_amount = $data['price'] - $down_payment;
                }

                // Construire la requête SQL en fonction du type de paiement
                $sql = "INSERT INTO transactions (
                    customer_id, vehicle_id, user_id, transaction_type,
                    price, payment_method, payment_type, invoice_number, notes, status";
                
                $values = "(?, ?, ?, ?, ?, ?, ?, ?, ?, ?";
                $params = [
                    $data['customer_id'], $data['vehicle_id'], $data['user_id'],
                    $data['transaction_type'], $data['price'], $data['payment_method'],
                    $data['payment_type'], $data['invoice_number'], $data['notes'],
                    $data['status']
                ];

                if ($_POST['payment_type'] === 'monthly') {
                    $sql .= ", installments, first_payment_date";
                    $values .= ", ?, ?";
                    $params[] = $data['installments'];
                    $params[] = $data['first_payment_date'];
                }

                $sql .= ") VALUES " . $values . ")";

                // Insérer la transaction
                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);
                $transaction_id = $pdo->lastInsertId();

                // Enregistrer l'acompte s'il y en a un
                if ($down_payment > 0) {
                $stmt = $pdo->prepare("
                        INSERT INTO payments (
                            transaction_id, amount, payment_date, status, payment_method, 
                            payment_type
                        ) VALUES (?, ?, CURRENT_DATE(), 'paid', ?, 'down_payment')
                    ");
                    $stmt->execute([
                        $transaction_id,
                        $down_payment,
                        $data['payment_method']
                    ]);
                }

                // Créer les mensualités pour le montant restant
                if ($remaining_amount > 0) {
                    $monthly_amount = $remaining_amount / $data['installments'];
                    $payment_date = new DateTime($data['first_payment_date']);

                    for ($i = 0; $i < $data['installments']; $i++) {
                        $stmt = $pdo->prepare("
                            INSERT INTO payments (
                                transaction_id, amount, payment_date, status, payment_method,
                                payment_type
                            ) VALUES (?, ?, ?, 'pending', ?, 'installment')
                        ");
                $stmt->execute([
                            $transaction_id,
                            $monthly_amount,
                            $payment_date->format('Y-m-d'),
                            $data['payment_method']
                        ]);
                        
                        $payment_date->modify('+1 month');
                    }
                }

                // Mettre à jour le statut du véhicule
                if ($data['transaction_type'] === 'sale') {
                $stmt = $pdo->prepare("UPDATE vehicles SET status = 'sold' WHERE id = ?");
                    $stmt->execute([$data['vehicle_id']]);
                } else if ($data['transaction_type'] === 'purchase') {
                    $stmt = $pdo->prepare("UPDATE vehicles SET status = 'available' WHERE id = ?");
                    $stmt->execute([$data['vehicle_id']]);
                }

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

                $pdo->commit();
                    $_SESSION['success'] = "Transaction enregistrée avec succès";
                header('Location: /transactions/');
                    exit;

            } catch (Exception $e) {
                $pdo->rollBack();
                $errors[] = "Erreur lors de l'enregistrement : " . $e->getMessage();
            }
        }
    }
} catch (Exception $e) {
    $errors[] = "Erreur : " . $e->getMessage();
}
?>

<div class="container mt-4">
    <div class="mb-3">
        <a href="/transactions/" class="btn btn-outline-secondary">
            <i class="fa fa-arrow-left"></i> Retour
        </a>
    </div>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <ul class="mb-0">
                <?php foreach ($errors as $error): ?>
                    <li><?php echo htmlspecialchars($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <h3 class="mb-0">Nouvelle Transaction</h3>
        </div>
        <div class="card-body">
            <form method="POST" enctype="multipart/form-data">
                <!-- Informations principales -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="customer_id" class="form-label">Client *</label>
                            <select class="form-select select2" id="customer_id" name="customer_id" required>
                                <option value="">Rechercher un client...</option>
                                <?php foreach ($clients as $client): ?>
                                    <option value="<?php echo $client['id']; ?>" 
                                            data-search="<?php echo htmlspecialchars($client['first_name'] . ' ' . $client['last_name']); ?>">
                                        <?php echo htmlspecialchars($client['last_name'] . ' ' . $client['first_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="vehicle_id" class="form-label">Véhicule *</label>
                            <select class="form-select select2" id="vehicle_id" name="vehicle_id" required>
                                <option value="">Rechercher un véhicule...</option>
                                <?php foreach ($vehicles as $vehicle): ?>
                                    <option value="<?php echo $vehicle['id']; ?>" 
                                            data-price="<?php echo $vehicle['price']; ?>"
                                            data-search="<?php echo htmlspecialchars($vehicle['brand'] . ' ' . $vehicle['model']); ?>">
                                        <?php echo htmlspecialchars($vehicle['brand'] . ' ' . $vehicle['model'] . 
                                              ' (' . number_format($vehicle['price'], 2, ',', ' ') . ' €)'); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Type de transaction et prix -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="transaction_type" class="form-label">Type de transaction *</label>
                            <select class="form-select" name="transaction_type" required>
                                <option value="sale">Vente</option>
                                <option value="purchase">Achat</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="price" class="form-label">Prix *</label>
                            <div class="input-group">
                            <input type="number" class="form-control" id="price" name="price" step="0.01" required>
                                <span class="input-group-text">€</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Options de paiement -->
                <div class="card mb-4">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">Détails du paiement</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="payment_type" class="form-label">Type de paiement *</label>
                                    <select class="form-select" name="payment_type" id="payment_type" required>
                                        <option value="full">Paiement comptant</option>
                                        <option value="monthly">Paiement mensuel</option>
                                    </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="payment_method" class="form-label">Méthode de paiement *</label>
                                    <select class="form-select" name="payment_method" required>
                                <option value="card">Carte bancaire</option>
                                <option value="cash">Espèces</option>
                                <option value="transfer">Virement</option>
                                <option value="check">Chèque</option>
                            </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="down_payment" class="form-label">Acompte versé</label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" id="down_payment" name="down_payment" 
                                               step="0.01" value="0" min="0">
                                        <span class="input-group-text">€</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Options de paiement mensuel -->
                        <div id="monthly_options" style="display: none;">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="installments" class="form-label">Nombre de mensualités</label>
                                        <select class="form-select" name="installments" id="installments">
                                            <?php for($i = 2; $i <= 24; $i++): ?>
                                                <option value="<?= $i ?>"><?= $i ?> mois</option>
                                            <?php endfor; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="first_payment_date" class="form-label">Date du premier paiement</label>
                                        <input type="date" class="form-control" name="first_payment_date" id="first_payment_date">
                                    </div>
                                </div>
                            </div>

                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6>Résumé du paiement</h6>
                                    <div id="payment_summary">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <p>Prix total: <span id="total_price">0.00</span> €</p>
                                            </div>
                                            <div class="col-md-4">
                                                <p>Acompte versé: <span id="down_payment_display">0.00</span> €</p>
                                            </div>
                                            <div class="col-md-4">
                                                <p>Reste à payer: <span id="remaining_amount">0.00</span> €</p>
                                            </div>
                                        </div>
                                        <p>Mensualité: <span id="monthly_amount">0.00</span> €</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Notes -->
                <div class="mb-4">
                    <label for="notes" class="form-label">Notes</label>
                    <textarea class="form-control" id="notes" name="notes" rows="3"></textarea>
                </div>

                <!-- Documents -->
                <div class="mb-4">
                    <label class="form-label">Documents</label>
                    <div id="document_container">
                        <div class="document-row mb-2">
                            <div class="row">
                                <div class="col-md-6">
                                    <input type="file" name="documents[]" class="form-control">
                            </div>
                                <div class="col-md-6">
                                    <select name="document_types[]" class="form-select">
                                        <option value="invoice">Facture</option>
                                        <option value="contract">Contrat</option>
                                        <option value="registration">Carte grise</option>
                                        <option value="insurance">Assurance</option>
                                        <option value="other">Autre</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <button type="button" class="btn btn-outline-secondary btn-sm mt-2" onclick="addDocumentRow()">
                        <i class="fa fa-plus"></i> Ajouter un document
                    </button>
                </div>

                <div class="text-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-save me-2"></i>Enregistrer la transaction
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />

<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const paymentType = document.getElementById('payment_type');
    const monthlyOptions = document.getElementById('monthly_options');
    const priceInput = document.getElementById('price');
    const installmentsSelect = document.getElementById('installments');
    const downPaymentInput = document.getElementById('down_payment');
    const totalPriceSpan = document.getElementById('total_price');
    const monthlyAmountSpan = document.getElementById('monthly_amount');
    const downPaymentDisplay = document.getElementById('down_payment_display');
    const remainingAmountSpan = document.getElementById('remaining_amount');
    const vehicleSelect = document.getElementById('vehicle_id');

    // Définir la date minimale pour le premier paiement
    const firstPaymentDate = document.getElementById('first_payment_date');
    const today = new Date();
    firstPaymentDate.min = today.toISOString().split('T')[0];
    firstPaymentDate.value = today.toISOString().split('T')[0];

    // Configuration commune pour Select2
    const select2Config = {
        theme: 'bootstrap-5',
        width: '100%',
        minimumInputLength: 0, // Permettre l'affichage de tous les éléments au début
        dropdownParent: $('body'), // Assurer que le dropdown s'affiche correctement
        language: {
            noResults: function() {
                return "Aucun résultat trouvé";
            },
            searching: function() {
                return "Recherche...";
            }
        }
    };

    // Configuration pour les véhicules
    $('#vehicle_id').select2({
        ...select2Config,
        placeholder: 'Rechercher un véhicule...',
        allowClear: true,
        templateResult: function(vehicle) {
            if (!vehicle.id) return vehicle.text;
            return $(`<span>${vehicle.text}</span>`);
        },
        matcher: function(params, data) {
            // Afficher tous les éléments si pas de recherche
            if (!params.term) {
                return data;
            }

            const searchText = params.term.toLowerCase();
            const vehicle = $(data.element);
            const searchStr = (
                vehicle.data('search') + ' ' + // Marque et modèle
                data.text.toLowerCase() // Texte affiché (inclut le prix)
            );

            // Retourner l'élément si le texte correspond
            if (searchStr.toLowerCase().includes(searchText)) {
                return data;
            }

            // Sinon ne pas l'afficher
            return null;
        }
    });

    // Configuration pour les clients
    $('#customer_id').select2({
        ...select2Config,
        placeholder: 'Rechercher un client...',
        allowClear: true,
        templateResult: function(client) {
            if (!client.id) return client.text;
            return $(`<span>${client.text}</span>`);
        },
        matcher: function(params, data) {
            // Afficher tous les éléments si pas de recherche
            if (!params.term) {
                return data;
            }

            const searchText = params.term.toLowerCase();
            const searchStr = (
                $(data.element).data('search') + ' ' + // Prénom et nom
                data.text.toLowerCase() // Texte affiché
            );

            // Retourner l'élément si le texte correspond
            if (searchStr.toLowerCase().includes(searchText)) {
                return data;
            }

            // Sinon ne pas l'afficher
            return null;
        }
    });

    // Mettre à jour le prix quand un véhicule est sélectionné
    $('#vehicle_id').on('select2:select', function(e) {
        const selectedOption = $(this).find('option:selected');
        if (selectedOption.data('price')) {
            $('#price').val(selectedOption.data('price'));
            updatePaymentSummary();
        }
    });

    // Afficher/masquer les options de paiement mensuel
    paymentType.addEventListener('change', function() {
        monthlyOptions.style.display = this.value === 'monthly' ? 'block' : 'none';
        updatePaymentSummary();
    });

    // Mettre à jour le résumé du paiement
    function updatePaymentSummary() {
        if (paymentType.value === 'monthly') {
            const price = parseFloat(priceInput.value) || 0;
            const downPayment = parseFloat(downPaymentInput.value) || 0;
            const remainingAmount = price - downPayment;
            const installments = parseInt(installmentsSelect.value) || 1;
            const monthlyAmount = remainingAmount / installments;

            totalPriceSpan.textContent = price.toFixed(2);
            downPaymentDisplay.textContent = downPayment.toFixed(2);
            remainingAmountSpan.textContent = remainingAmount.toFixed(2);
            monthlyAmountSpan.textContent = monthlyAmount.toFixed(2);

            // Désactiver la validation si l'acompte est supérieur au prix total
            const submitButton = document.querySelector('button[type="submit"]');
            if (downPayment > price) {
                submitButton.disabled = true;
                alert("L'acompte ne peut pas être supérieur au prix total");
            } else {
                submitButton.disabled = false;
            }
        }
    }

    priceInput.addEventListener('input', updatePaymentSummary);
    downPaymentInput.addEventListener('input', updatePaymentSummary);
    installmentsSelect.addEventListener('change', updatePaymentSummary);
});

function addDocumentRow() {
    const container = document.getElementById('document_container');
    const newRow = document.createElement('div');
    newRow.className = 'document-row mb-2';
    newRow.innerHTML = `
        <div class="row">
            <div class="col-md-6">
                <input type="file" name="documents[]" class="form-control">
            </div>
            <div class="col-md-6">
                <select name="document_types[]" class="form-select">
                    <option value="invoice">Facture</option>
                    <option value="contract">Contrat</option>
                    <option value="registration">Carte grise</option>
                    <option value="insurance">Assurance</option>
                    <option value="other">Autre</option>
                </select>
            </div>
        </div>
    `;
    container.appendChild(newRow);
}
</script>

<?php require_once '../includes/footer.php'; ?> 