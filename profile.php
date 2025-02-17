<?php
session_start();
require_once 'config/database.php';

$page_title = "Mon Profil";
require_once 'includes/header.php';

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $updates = [];
        $params = [];

        // Gestion de l'upload de la photo
        if (!empty($_FILES['profile_image']['name'])) {
            $upload_dir = 'uploads/profiles/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            $file_extension = strtolower(pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION));
            $new_filename = uniqid('profile_') . '.' . $file_extension;
            $upload_path = $upload_dir . $new_filename;

            if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $upload_path)) {
                // Supprimer l'ancienne image si elle existe et n'est pas l'image par défaut
                if (!empty($user['profile_image']) && 
                    $user['profile_image'] !== '/assets/images/defaults/default-profile.png' && 
                    file_exists($user['profile_image'])) {
                    unlink($user['profile_image']);
                }
                
                $updates[] = "profile_image = ?";
                $params[] = '/' . $upload_path; // Ajouter le slash pour le chemin web
            }
        }

        // Mise à jour des autres informations du profil
        if (empty($updates)) {
            $_SESSION['error'] = "Aucune mise à jour nécessaire";
            header('Location: /profile.php');
            exit;
        }

        $stmt = $pdo->prepare("
            UPDATE users 
            SET " . implode(', ', $updates) . "
            WHERE id = ?
        ");
        
        $stmt->execute(array_merge($params, [$_SESSION['user_id']]));

        $_SESSION['success'] = "Profil mis à jour avec succès";
        header('Location: /profile.php');
        exit;
    }

    // Récupérer les informations de l'utilisateur
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();

    // Récupérer les transactions de l'utilisateur
    $stmt = $pdo->prepare("
        SELECT t.*, v.brand, v.model, c.first_name, c.last_name 
        FROM transactions t
        JOIN vehicles v ON t.vehicle_id = v.id
        JOIN customers c ON t.customer_id = c.id
        WHERE t.user_id = ?
        ORDER BY t.transaction_date DESC
        LIMIT 5
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $transactions = $stmt->fetchAll();
} catch (PDOException $e) {
    $_SESSION['error'] = "Erreur: " . $e->getMessage();
}
?>

<div class="container mt-5">
    <div class="row">
        <div class="col-md-4">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3>Profil Utilisateur</h3>
                    <button class="btn btn-primary btn-sm" onclick="toggleEditMode()">
                        <i class="fa fa-edit"></i> Modifier
                    </button>
                </div>
                <div class="card-body text-center">
                    <!-- Affichage du profil -->
                    <div id="profile-display">
                        <div class="profile-image-container mb-3">
                            <img src="<?php echo get_profile_image($user['profile_image']); ?>" 
                                 class="img-thumbnail rounded-circle" 
                                 alt="Photo de profil"
                                 style="width: 150px; height: 150px; object-fit: cover;">
                        </div>
                        <div class="mb-3">
                            <strong>Nom d'utilisateur:</strong>
                            <p><?php echo htmlspecialchars($user['username']); ?></p>
                        </div>
                        <div class="mb-3">
                            <strong>Email:</strong>
                            <p><?php echo htmlspecialchars($user['email']); ?></p>
                        </div>
                        <div class="mb-3">
                            <strong>Rôle:</strong>
                            <p><?php echo htmlspecialchars($user['role']); ?></p>
                        </div>
                        <div class="mb-3">
                            <strong>Date d'inscription:</strong>
                            <p><?php echo date('d/m/Y', strtotime($user['created_at'])); ?></p>
                        </div>
                    </div>

                    <!-- Formulaire de modification (caché par défaut) -->
                    <div id="profile-edit" style="display: none;">
                        <form method="POST" enctype="multipart/form-data">
                            <div class="text-center mb-4">
                                <div class="profile-image-container position-relative">
                                    <img src="<?php echo get_profile_image($user['profile_image']); ?>" 
                                         class="rounded-circle mb-3" 
                                         style="width: 150px; height: 150px; object-fit: cover;"
                                         alt="Photo de profil"
                                         id="profile-preview">
                                    
                                    <div class="mt-2 d-flex justify-content-center gap-2">
                                        <label for="profile_image" class="btn btn-sm btn-primary">
                                            <i class="fa fa-camera"></i> Changer
                                        </label>
                                        <?php if ($user['profile_image'] && $user['profile_image'] !== '/assets/images/defaults/default-profile.png'): ?>
                                            <button type="button" class="btn btn-sm btn-danger" onclick="deleteProfileImage()">
                                                <i class="fa fa-trash"></i>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <input type="file" id="profile_image" name="profile_image" class="d-none" accept="image/*">
                            </div>

                            <div class="mb-3">
                                <label for="username" class="form-label">Nom d'utilisateur</label>
                                <input type="text" class="form-control" id="username" name="username" 
                                       value="<?php echo htmlspecialchars($user['username']); ?>" required>
                            </div>

                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?php echo htmlspecialchars($user['email']); ?>" required>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">Enregistrer</button>
                                <button type="button" class="btn btn-secondary" onclick="toggleEditMode()">Annuler</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3>Dernières Transactions</h3>
                </div>
                <div class="card-body">
                    <?php if (!empty($transactions)): ?>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Client</th>
                                        <th>Véhicule</th>
                                        <th>Type</th>
                                        <th>Montant</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($transactions as $transaction): ?>
                                        <tr>
                                            <td><?php echo date('d/m/Y', strtotime($transaction['transaction_date'])); ?></td>
                                            <td><?php echo htmlspecialchars($transaction['first_name'] . ' ' . $transaction['last_name']); ?></td>
                                            <td><?php echo htmlspecialchars($transaction['brand'] . ' ' . $transaction['model']); ?></td>
                                            <td><?php echo $transaction['transaction_type'] === 'sale' ? 'Vente' : 'Achat'; ?></td>
                                            <td><?php echo number_format($transaction['price'], 2, ',', ' ') . ' €'; ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p>Aucune transaction trouvée.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <div class="container mt-4">
        <div class="mb-3">
            <a href="javascript:history.back()" class="btn btn-outline-secondary">
                <i class="fa fa-arrow-left"></i> Retour
            </a>
        </div>
    </div>
</div>

<script>
function toggleEditMode() {
    const displayEl = document.getElementById('profile-display');
    const editEl = document.getElementById('profile-edit');
    
    if (displayEl.style.display === 'none') {
        displayEl.style.display = 'block';
        editEl.style.display = 'none';
    } else {
        displayEl.style.display = 'none';
        editEl.style.display = 'block';
    }
}

// Prévisualisation de l'image
document.getElementById('profile_image').addEventListener('change', function(e) {
    if (e.target.files && e.target.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('profile-preview').src = e.target.result;
        }
        reader.readAsDataURL(e.target.files[0]);
    }
});

function deleteProfileImage() {
    if (confirm('Êtes-vous sûr de vouloir supprimer votre photo de profil ?')) {
        fetch('delete_profile_image.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Mettre à jour l'image affichée avec l'image par défaut
                document.querySelectorAll('.profile-image-container img').forEach(img => {
                    img.src = '/assets/images/defaults/default-profile.png';
                });
                // Recharger la page pour mettre à jour l'interface
                location.reload();
            } else {
                alert(data.error || 'Une erreur est survenue');
            }
        });
    }
}
</script>

<?php require_once 'includes/footer.php'; ?> 