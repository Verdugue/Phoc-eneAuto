<?php
session_start();
require_once '../config/database.php';

$page_title = isset($_GET['id']) ? "Modifier un Véhicule" : "Ajouter un Véhicule";
require_once '../includes/header.php';

$vehicle = [
    'brand' => '',
    'model' => '',
    'year' => date('Y'),
    'mileage' => 0,
    'price' => '',
    'vehicle_condition' => 'new',
    'color' => '',
    'fuel_type' => '',
    'transmission' => '',
    'registration_number' => '',
    'vin_number' => '',
    'status' => 'available'
];

if (isset($_GET['id'])) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM vehicles WHERE id = ?");
        $stmt->execute([$_GET['id']]);
        $vehicle = $stmt->fetch();
        
        if (!$vehicle) {
            $_SESSION['error'] = "Véhicule non trouvé";
            header('Location: index.php');
            exit;
        }
    } catch (PDOException $e) {
        $error = "Erreur lors de la récupération du véhicule: " . $e->getMessage();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $vehicle = [
        'brand' => trim($_POST['brand']),
        'model' => trim($_POST['model']),
        'year' => (int)$_POST['year'],
        'mileage' => (int)$_POST['mileage'],
        'price' => (float)$_POST['price'],
        'vehicle_condition' => $_POST['vehicle_condition'],
        'color' => trim($_POST['color']),
        'fuel_type' => trim($_POST['fuel_type']),
        'transmission' => trim($_POST['transmission']),
        'registration_number' => trim($_POST['registration_number']),
        'vin_number' => trim($_POST['vin_number']),
        'status' => $_POST['status']
    ];

    $errors = [];

    // Validations
    if (empty($vehicle['brand'])) $errors[] = "La marque est requise";
    if (empty($vehicle['model'])) $errors[] = "Le modèle est requis";
    if ($vehicle['year'] < 1900 || $vehicle['year'] > date('Y') + 1) $errors[] = "L'année n'est pas valide";
    if ($vehicle['mileage'] < 0) $errors[] = "Le kilométrage ne peut pas être négatif";
    if ($vehicle['price'] <= 0) $errors[] = "Le prix doit être supérieur à 0";

    if (empty($errors)) {
        try {
            $pdo->beginTransaction();

            if (isset($_GET['id'])) {
                $stmt = $pdo->prepare("
                    UPDATE vehicles 
                    SET brand = ?, model = ?, year = ?, mileage = ?, price = ?,
                        vehicle_condition = ?, color = ?, fuel_type = ?, transmission = ?,
                        registration_number = ?, vin_number = ?, status = ?,
                        updated_at = CURRENT_TIMESTAMP
                    WHERE id = ?
                ");
                $stmt->execute([
                    $vehicle['brand'], $vehicle['model'], $vehicle['year'],
                    $vehicle['mileage'], $vehicle['price'], $vehicle['vehicle_condition'],
                    $vehicle['color'], $vehicle['fuel_type'], $vehicle['transmission'],
                    $vehicle['registration_number'], $vehicle['vin_number'],
                    $vehicle['status'], $_GET['id']
                ]);
                $vehicle_id = $_GET['id'];
            } else {
                $stmt = $pdo->prepare("
                    INSERT INTO vehicles (brand, model, year, mileage, price,
                        vehicle_condition, color, fuel_type, transmission,
                        registration_number, vin_number, status)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $vehicle['brand'], $vehicle['model'], $vehicle['year'],
                    $vehicle['mileage'], $vehicle['price'], $vehicle['vehicle_condition'],
                    $vehicle['color'], $vehicle['fuel_type'], $vehicle['transmission'],
                    $vehicle['registration_number'], $vehicle['vin_number'],
                    $vehicle['status']
                ]);
                $vehicle_id = $pdo->lastInsertId();
            }

            // Gérer l'upload des images
            if (!empty($_FILES['images']['name'][0])) {
                $upload_dir = '../uploads/vehicles/' . $vehicle_id . '/';
                if (!file_exists($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }

                foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
                    if ($_FILES['images']['error'][$key] === UPLOAD_ERR_OK) {
                        $file_name = $_FILES['images']['name'][$key];
                        $file_type = $_FILES['images']['type'][$key];
                        
                        if (in_array($file_type, ['image/jpeg', 'image/png', 'image/webp'])) {
                            $extension = pathinfo($file_name, PATHINFO_EXTENSION);
                            $new_file_name = uniqid() . '.' . $extension;
                            $file_path = $upload_dir . $new_file_name;
                            
                            if (move_uploaded_file($tmp_name, $file_path)) {
                                // Vérifier s'il y a déjà des images
                                $stmt = $pdo->prepare("SELECT COUNT(*) FROM vehicle_images WHERE vehicle_id = ?");
                                $stmt->execute([$vehicle_id]);
                                $count = $stmt->fetchColumn();
                                
                                $stmt = $pdo->prepare("
                                    INSERT INTO vehicle_images (vehicle_id, file_name, file_path, is_primary)
                                    VALUES (?, ?, ?, ?)
                                ");
                                $stmt->execute([
                                    $vehicle_id,
                                    $new_file_name,
                                    '/uploads/vehicles/' . $vehicle_id . '/' . $new_file_name,
                                    ($count == 0) // Première image = image principale
                                ]);
                            }
                        }
                    }
                }
            }

            $pdo->commit();
            $_SESSION['success'] = isset($_GET['id']) ? "Véhicule modifié avec succès" : "Véhicule ajouté avec succès";

            // Rediriger vers la page de détails si c'est une modification
            if (isset($_GET['id'])) {
                header('Location: view.php?id=' . $_GET['id']);
            } else {
                // Pour un nouveau véhicule, rediriger vers sa page de détails
                header('Location: view.php?id=' . $vehicle_id);
            }
            exit;
        } catch (PDOException $e) {
            $pdo->rollBack();
            $errors[] = "Erreur lors de l'enregistrement: " . $e->getMessage();
        }
    }
}
?>

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
                    <h3><?php echo isset($_GET['id']) ? 'Modifier' : 'Ajouter'; ?> un Véhicule</h3>
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
                                <label for="brand" class="form-label">Marque *</label>
                                <input type="text" class="form-control" id="brand" name="brand" 
                                       value="<?php echo htmlspecialchars($vehicle['brand'] ?? ''); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label for="model" class="form-label">Modèle *</label>
                                <input type="text" class="form-control" id="model" name="model" 
                                       value="<?php echo htmlspecialchars($vehicle['model'] ?? ''); ?>" required>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="year" class="form-label">Année *</label>
                                <input type="number" class="form-control" id="year" name="year" 
                                       value="<?php echo htmlspecialchars($vehicle['year'] ?? ''); ?>" required>
                            </div>
                            <div class="col-md-4">
                                <label for="mileage" class="form-label">Kilométrage *</label>
                                <input type="number" class="form-control" id="mileage" name="mileage" 
                                       value="<?php echo htmlspecialchars($vehicle['mileage'] ?? 0); ?>" required>
                            </div>
                            <div class="col-md-4">
                                <label for="price" class="form-label">Prix *</label>
                                <input type="number" step="0.01" class="form-control" id="price" name="price" 
                                       value="<?php echo htmlspecialchars($vehicle['price'] ?? 0); ?>" required>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="vehicle_condition" class="form-label">État *</label>
                                <select class="form-select" id="vehicle_condition" name="vehicle_condition" required>
                                    <option value="new" <?php echo $vehicle['vehicle_condition'] === 'new' ? 'selected' : ''; ?>>Neuf</option>
                                    <option value="used" <?php echo $vehicle['vehicle_condition'] === 'used' ? 'selected' : ''; ?>>Occasion</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="color" class="form-label">Couleur</label>
                                <input type="text" class="form-control" id="color" name="color" 
                                       value="<?php echo htmlspecialchars($vehicle['color'] ?? ''); ?>">
                            </div>
                            <div class="col-md-4">
                                <label for="status" class="form-label">Statut *</label>
                                <select class="form-select" id="status" name="status" required>
                                    <option value="available" <?php echo $vehicle['status'] === 'available' ? 'selected' : ''; ?>>Disponible</option>
                                    <option value="reserved" <?php echo $vehicle['status'] === 'reserved' ? 'selected' : ''; ?>>Réservé</option>
                                    <option value="sold" <?php echo $vehicle['status'] === 'sold' ? 'selected' : ''; ?>>Vendu</option>
                                </select>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="fuel_type" class="form-label">Carburant</label>
                                <input type="text" class="form-control" id="fuel_type" name="fuel_type" 
                                       value="<?php echo htmlspecialchars($vehicle['fuel_type'] ?? ''); ?>">
                            </div>
                            <div class="col-md-6">
                                <label for="transmission" class="form-label">Transmission</label>
                                <input type="text" class="form-control" id="transmission" name="transmission" 
                                       value="<?php echo htmlspecialchars($vehicle['transmission'] ?? ''); ?>">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="registration_number" class="form-label">Numéro d'immatriculation</label>
                                <input type="text" class="form-control" id="registration_number" name="registration_number" 
                                       value="<?php echo htmlspecialchars($vehicle['registration_number'] ?? ''); ?>">
                            </div>
                            <div class="col-md-6">
                                <label for="vin_number" class="form-label">Numéro de série (VIN)</label>
                                <input type="text" class="form-control" id="vin_number" name="vin_number" 
                                       value="<?php echo htmlspecialchars($vehicle['vin_number'] ?? ''); ?>">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="mb-0">Photos du véhicule</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <div class="dropzone-container p-4 border rounded text-center" 
                                                 id="dropzone" 
                                                 ondrop="handleDrop(event)" 
                                                 ondragover="handleDragOver(event)"
                                                 ondragleave="handleDragLeave(event)">
                                                <i class="fa fa-cloud-upload fa-3x mb-3 text-muted"></i>
                                                <p class="mb-2">Glissez vos photos ici ou</p>
                                                <label class="btn btn-primary mb-0">
                                                    <span>Sélectionnez des fichiers</span>
                                                    <input type="file" name="images[]" multiple accept="image/*" class="d-none" onchange="handleFiles(this.files)">
                                                </label>
                                                <p class="mt-2 text-muted small">Formats acceptés : JPG, PNG, WEBP - Max 5MB par image</p>
                                            </div>
                                            <div id="preview-container" class="row mt-3">
                                                <!-- Les aperçus des nouvelles images seront ajoutés ici -->
                                            </div>
                                        </div>

                                        <?php if (isset($_GET['id'])): ?>
                                            <?php
                                            // Récupérer les images existantes
                                            $stmt = $pdo->prepare("SELECT * FROM vehicle_images WHERE vehicle_id = ? ORDER BY is_primary DESC");
                                            $stmt->execute([$_GET['id']]);
                                            $images = $stmt->fetchAll();
                                            ?>
                                            
                                            <?php if (!empty($images)): ?>
                                                <h6 class="mb-3">Images existantes</h6>
                                                <div class="row">
                                                    <?php foreach ($images as $image): ?>
                                                        <div class="col-md-3 col-sm-4 col-6 mb-3" id="image-<?php echo $image['id']; ?>">
                                                            <div class="card h-100">
                                                                <div class="position-relative">
                                                                    <img src="<?php echo htmlspecialchars($image['file_path']); ?>" 
                                                                         class="card-img-top" 
                                                                         style="height: 150px; object-fit: cover;"
                                                                         alt="Photo véhicule">
                                                                    <?php if ($image['is_primary']): ?>
                                                                        <span class="badge bg-primary position-absolute top-0 start-0 m-2">
                                                                            Principale
                                                                        </span>
                                                                    <?php endif; ?>
                                                                </div>
                                                                <div class="card-body p-2">
                                                                    <div class="btn-group w-100">
                                                                        <?php if (!$image['is_primary']): ?>
                                                                            <button type="button" 
                                                                                    class="btn btn-sm btn-outline-primary"
                                                                                    onclick="setPrimaryImage(<?php echo $image['id']; ?>)">
                                                                                <i class="fa fa-star"></i>
                                                                            </button>
                                                                        <?php endif; ?>
                                                                        <button type="button" 
                                                                                class="btn btn-sm btn-outline-danger"
                                                                                onclick="deleteImage(<?php echo $image['id']; ?>)">
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
.preview-item {
    position: relative;
}
.preview-item .remove-btn {
    position: absolute;
    top: 5px;
    right: 5px;
    background: rgba(0,0,0,0.5);
    border: none;
    color: white;
    border-radius: 50%;
    width: 25px;
    height: 25px;
    line-height: 25px;
    padding: 0;
    text-align: center;
    cursor: pointer;
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
    const droppedFiles = e.dataTransfer.files;
    handleFiles(droppedFiles);
}

function handleFiles(fileList) {
    Array.from(fileList).forEach(file => {
        if (!file.type.startsWith('image/')) {
            alert(`Le fichier ${file.name} n'est pas une image`);
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
    const reader = new FileReader();
    reader.onload = function(e) {
        const div = document.createElement('div');
        div.className = 'col-md-3 col-sm-4 col-6 mb-3 preview-item';
        div.innerHTML = `
            <div class="card">
                <div class="position-relative">
                    <img src="${e.target.result}" class="card-img-top" style="height: 150px; object-fit: cover;">
                    <button type="button" class="remove-btn" onclick="removeFile('${file.name}')">
                        <i class="fa fa-times"></i>
                    </button>
                </div>
                <div class="card-body p-2">
                    <small class="text-muted">${file.name}</small>
                </div>
            </div>
        `;
        previewContainer.appendChild(div);
    };
    reader.readAsDataURL(file);
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

// Modifier le comportement du formulaire pour inclure les fichiers
document.querySelector('form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    files.forEach(file => {
        formData.append('images[]', file);
    });
    
    fetch(this.action, {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (response.redirected) {
            window.location.href = response.url;
        } else {
            return response.text().then(text => {
                try {
                    const data = JSON.parse(text);
                    // Si c'est une modification, rediriger vers la page de détails
                    if (data.success) {
                        const urlParams = new URLSearchParams(window.location.search);
                        const vehicleId = urlParams.get('id') || data.vehicle_id;
                        window.location.href = `view.php?id=${vehicleId}`;
                    } else {
                        alert(data.error);
                    }
                } catch (e) {
                    document.write(text);
                }
            });
        }
    });
});

function deleteImage(imageId) {
    if (confirm('Êtes-vous sûr de vouloir supprimer cette image ?')) {
        fetch('delete_image.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'image_id=' + imageId
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert(data.error);
            }
        });
    }
}

function setPrimaryImage(imageId) {
    fetch('set_primary_image.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'image_id=' + imageId
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.error);
        }
    });
}
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 