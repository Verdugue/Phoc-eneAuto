<?php
session_start();
require_once '../config/database.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}

$client = [
    'first_name' => '',
    'last_name' => '',
    'email' => '',
    'phone' => '',
    'address' => '',
    'postal_code' => '',
    'city' => ''
];

if (isset($_GET['id'])) {
    // Mode édition
    try {
        $stmt = $pdo->prepare("SELECT * FROM customers WHERE id = ?");
        $stmt->execute([$_GET['id']]);
        $client = $stmt->fetch();
        
        if (!$client) {
            $_SESSION['error'] = "Client non trouvé";
            header('Location: index.php');
            exit;
        }
    } catch (PDOException $e) {
        $error = "Erreur lors de la récupération du client: " . $e->getMessage();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $client = [
        'first_name' => trim($_POST['first_name']),
        'last_name' => trim($_POST['last_name']),
        'email' => trim($_POST['email']),
        'phone' => trim($_POST['phone']),
        'address' => trim($_POST['address']),
        'postal_code' => trim($_POST['postal_code']),
        'city' => trim($_POST['city'])
    ];

    $errors = [];

    // Validations
    if (empty($client['first_name'])) $errors[] = "Le prénom est requis";
    if (empty($client['last_name'])) $errors[] = "Le nom est requis";
    if (!empty($client['email']) && !filter_var($client['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = "L'email n'est pas valide";
    }

    if (empty($errors)) {
        try {
            $pdo->beginTransaction();

            if (isset($_GET['id'])) {
                // Update client existant
                $stmt = $pdo->prepare("
                    UPDATE customers 
                    SET first_name = ?, last_name = ?, email = ?, phone = ?,
                        address = ?, postal_code = ?, city = ?,
                        updated_at = CURRENT_TIMESTAMP
                    WHERE id = ?
                ");
                $stmt->execute([
                    $client['first_name'], $client['last_name'], $client['email'],
                    $client['phone'], $client['address'], $client['postal_code'],
                    $client['city'], $_GET['id']
                ]);
                $client_id = $_GET['id'];
            } else {
                // Nouveau client
                $stmt = $pdo->prepare("
                    INSERT INTO customers (first_name, last_name, email, phone, address, postal_code, city)
                    VALUES (?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $client['first_name'], $client['last_name'], $client['email'],
                    $client['phone'], $client['address'], $client['postal_code'],
                    $client['city']
                ]);
                $client_id = $pdo->lastInsertId();
            }

            // Gérer l'upload des documents
            if (!empty($_FILES['documents']['name'][0])) {
                $upload_dir = '../uploads/customers/' . $client_id . '/';
                if (!file_exists($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }

                foreach ($_FILES['documents']['tmp_name'] as $key => $tmp_name) {
                    if ($_FILES['documents']['error'][$key] === UPLOAD_ERR_OK) {
                        $file_name = $_FILES['documents']['name'][$key];
                        $file_type = $_FILES['documents']['type'][$key];
                        
                        if (in_array($file_type, ['image/jpeg', 'image/png', 'image/webp', 'application/pdf'])) {
                            $extension = pathinfo($file_name, PATHINFO_EXTENSION);
                            $new_file_name = uniqid() . '.' . $extension;
                            $file_path = $upload_dir . $new_file_name;
                            
                            if (move_uploaded_file($tmp_name, $file_path)) {
                                $stmt = $pdo->prepare("
                                    INSERT INTO customer_documents (customer_id, document_type, file_name, file_path)
                                    VALUES (?, ?, ?, ?)
                                ");
                                $stmt->execute([
                                    $client_id,
                                    'identity_document', // ou vous pouvez ajouter un champ pour spécifier le type
                                    $new_file_name,
                                    '/uploads/customers/' . $client_id . '/' . $new_file_name
                                ]);
                            }
                        }
                    }
                }
            }

            $pdo->commit();
            $_SESSION['success'] = isset($_GET['id']) ? "Client modifié avec succès" : "Client ajouté avec succès";
            header('Location: view.php?id=' . $client_id);
            exit;

        } catch (PDOException $e) {
            $pdo->rollBack();
            $errors[] = "Erreur lors de l'enregistrement: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($_GET['id']) ? 'Modifier' : 'Ajouter'; ?> un Client - Phocéenne Auto</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <div class="mb-3">
            <a href="javascript:history.back()" class="btn btn-outline-secondary">
                <i class="fa fa-arrow-left"></i> Retour
            </a>
        </div>
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h3><?php echo isset($_GET['id']) ? 'Modifier' : 'Ajouter'; ?> un Client</h3>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($errors)): ?>
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    <?php foreach ($errors as $error): ?>
                                        <li><?php echo $error; ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>

                        <form method="POST" enctype="multipart/form-data">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="first_name" class="form-label">Prénom *</label>
                                    <input type="text" class="form-control" id="first_name" name="first_name" 
                                           value="<?php echo htmlspecialchars($client['first_name']); ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="last_name" class="form-label">Nom *</label>
                                    <input type="text" class="form-control" id="last_name" name="last_name" 
                                           value="<?php echo htmlspecialchars($client['last_name']); ?>" required>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" class="form-control" id="email" name="email" 
                                           value="<?php echo htmlspecialchars($client['email']); ?>">
                                </div>
                                <div class="col-md-6">
                                    <label for="phone" class="form-label">Téléphone</label>
                                    <input type="tel" class="form-control" id="phone" name="phone" 
                                           value="<?php echo htmlspecialchars($client['phone']); ?>">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="address" class="form-label">Adresse</label>
                                <input type="text" class="form-control" id="address" name="address" 
                                       value="<?php echo htmlspecialchars($client['address']); ?>">
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label for="postal_code" class="form-label">Code Postal</label>
                                    <input type="text" class="form-control" id="postal_code" name="postal_code" 
                                           value="<?php echo htmlspecialchars($client['postal_code']); ?>">
                                </div>
                                <div class="col-md-8">
                                    <label for="city" class="form-label">Ville</label>
                                    <input type="text" class="form-control" id="city" name="city" 
                                           value="<?php echo htmlspecialchars($client['city']); ?>">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-12">
                                    <div class="card">
                                        <div class="card-header">
                                            <h5 class="mb-0">Documents d'identité</h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="mb-3">
                                                <div class="dropzone-container p-4 border rounded text-center" 
                                                     id="dropzone" 
                                                     ondrop="handleDrop(event)" 
                                                     ondragover="handleDragOver(event)"
                                                     ondragleave="handleDragLeave(event)">
                                                    <i class="fa fa-cloud-upload fa-3x mb-3 text-muted"></i>
                                                    <p class="mb-2">Glissez les documents ici ou</p>
                                                    <label class="btn btn-primary mb-0">
                                                        <span>Sélectionnez des fichiers</span>
                                                        <input type="file" name="documents[]" multiple accept="image/*,.pdf" class="d-none" onchange="handleFiles(this.files)">
                                                    </label>
                                                    <p class="mt-2 text-muted small">Formats acceptés : JPG, PNG, PDF - Max 5MB par fichier</p>
                                                </div>
                                                <div id="preview-container" class="row mt-3">
                                                    <!-- Les aperçus des nouveaux fichiers seront ajoutés ici -->
                                                </div>
                                            </div>

                                            <?php if (isset($_GET['id'])): ?>
                                                <?php
                                                // Récupérer les documents existants
                                                $stmt = $pdo->prepare("SELECT * FROM customer_documents WHERE customer_id = ? ORDER BY uploaded_at DESC");
                                                $stmt->execute([$_GET['id']]);
                                                $documents = $stmt->fetchAll();
                                                ?>
                                                
                                                <?php if (!empty($documents)): ?>
                                                    <h6 class="mb-3">Documents existants</h6>
                                                    <div class="row">
                                                        <?php foreach ($documents as $doc): ?>
                                                            <div class="col-md-4 col-sm-6 mb-3" id="doc-<?php echo $doc['id']; ?>">
                                                                <div class="card h-100">
                                                                    <div class="position-relative">
                                                                        <?php if (pathinfo($doc['file_name'], PATHINFO_EXTENSION) === 'pdf'): ?>
                                                                            <div class="text-center p-3">
                                                                                <i class="fa fa-file-pdf-o fa-3x text-danger"></i>
                                                                            </div>
                                                                        <?php else: ?>
                                                                            <img src="<?php echo htmlspecialchars($doc['file_path']); ?>" 
                                                                                 class="card-img-top" 
                                                                                 style="height: 150px; object-fit: cover;"
                                                                                 alt="Document">
                                                                        <?php endif; ?>
                                                                    </div>
                                                                    <div class="card-body p-2">
                                                                        <p class="small text-muted mb-2"><?php echo htmlspecialchars($doc['document_type']); ?></p>
                                                                        <div class="btn-group w-100">
                                                                            <a href="<?php echo htmlspecialchars($doc['file_path']); ?>" 
                                                                               class="btn btn-sm btn-outline-primary"
                                                                               target="_blank">
                                                                                <i class="fa fa-eye"></i>
                                                                            </a>
                                                                            <button type="button" 
                                                                                    class="btn btn-sm btn-outline-danger"
                                                                                    onclick="deleteDocument(<?php echo $doc['id']; ?>)">
                                                                                <i class="fa fa-trash"></i>
                                                                            </button>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        <?php endforeach; ?>
                                                    </div>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-between">
                                <a href="index.php" class="btn btn-secondary">Annuler</a>
                                <button type="submit" class="btn btn-primary">
                                    <?php echo isset($_GET['id']) ? 'Modifier' : 'Ajouter'; ?>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
    .dropzone-container {
        border: 2px dashed #ccc !important;
        transition: all 0.3s ease;
        cursor: pointer;
    }
    .dropzone-container.dragover {
        background-color: rgba(52, 152, 219, 0.1);
        border-color: #3498db !important;
    }
    </style>

    <script>
    const dropzone = document.getElementById('dropzone');
    const previewContainer = document.getElementById('preview-container');
    let files = new Set();

    function handleDragOver(e) {
        e.preventDefault();
        dropzone.classList.add('dragover');
    }

    function handleDragLeave(e) {
        e.preventDefault();
        dropzone.classList.remove('dragover');
    }

    function handleDrop(e) {
        e.preventDefault();
        dropzone.classList.remove('dragover');
        handleFiles(e.dataTransfer.files);
    }

    function handleFiles(fileList) {
        Array.from(fileList).forEach(file => {
            if (!file.type.match(/^image\/(jpeg|png|gif)$/) && file.type !== 'application/pdf') {
                alert(`Le fichier ${file.name} n'est pas un format accepté`);
                return;
            }
            if (file.size > 5 * 1024 * 1024) {
                alert(`Le fichier ${file.name} dépasse la limite de 5MB`);
                return;
            }
            files.add(file);
            displayPreview(file);
        });
    }

    function displayPreview(file) {
        const div = document.createElement('div');
        div.className = 'col-md-4 col-sm-6 mb-3';
        
        if (file.type === 'application/pdf') {
            div.innerHTML = `
                <div class="card">
                    <div class="text-center p-3">
                        <i class="fa fa-file-pdf-o fa-3x text-danger"></i>
                    </div>
                    <div class="card-body p-2">
                        <p class="small text-muted mb-2">${file.name}</p>
                        <button type="button" class="btn btn-sm btn-outline-danger w-100" onclick="removeFile('${file.name}')">
                            <i class="fa fa-times"></i> Retirer
                        </button>
                    </div>
                </div>
            `;
        } else {
            const reader = new FileReader();
            reader.onload = function(e) {
                div.innerHTML = `
                    <div class="card">
                        <img src="${e.target.result}" class="card-img-top" style="height: 150px; object-fit: cover;">
                        <div class="card-body p-2">
                            <p class="small text-muted mb-2">${file.name}</p>
                            <button type="button" class="btn btn-sm btn-outline-danger w-100" onclick="removeFile('${file.name}')">
                                <i class="fa fa-times"></i> Retirer
                            </button>
                        </div>
                    </div>
                `;
            };
            reader.readAsDataURL(file);
        }
        previewContainer.appendChild(div);
    }

    function removeFile(fileName) {
        files.forEach(file => {
            if (file.name === fileName) {
                files.delete(file);
            }
        });
        updatePreviews();
    }

    function updatePreviews() {
        previewContainer.innerHTML = '';
        files.forEach(file => displayPreview(file));
    }

    function deleteDocument(docId) {
        if (confirm('Êtes-vous sûr de vouloir supprimer ce document ?')) {
            fetch('delete_document.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'id=' + docId
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('doc-' + docId).remove();
                } else {
                    alert(data.error || 'Une erreur est survenue');
                }
            });
        }
    }
    </script>
</body>
</html> 