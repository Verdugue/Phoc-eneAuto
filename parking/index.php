<?php
session_start();
require_once '../includes/init.php';

$page_title = "Parking Interactif";
require_once '../includes/header.php';

// Récupérer les véhicules disponibles
$stmt = $pdo->query("
    SELECT v.*, ps.spot_number 
    FROM vehicles v 
    LEFT JOIN parking_spots ps ON v.id = ps.vehicle_id 
    WHERE v.status = 'available'
    ORDER BY v.brand, v.model
");
$vehicles = $stmt->fetchAll();

// Récupérer les places de parking
$stmt = $pdo->query("
    SELECT ps.*, 
           CASE WHEN v.id IS NULL THEN NULL ELSE v.id END as vehicle_id,
           v.brand, 
           v.model, 
           v.registration_number 
    FROM parking_spots ps
    LEFT JOIN vehicles v ON ps.vehicle_id = v.id
    WHERE (ps.vehicle_id IS NULL OR v.id IS NOT NULL)
    ORDER BY ps.spot_number
");
$spots = $stmt->fetchAll();
?>

<div class="container mt-4">
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <h3 class="mb-0">Parking Interactif</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <!-- Ajouter une zone "Sortie" au-dessus de la liste des véhicules -->
                <div class="col-md-3">
                    <div class="card mb-3">
                        <div class="card-header bg-danger text-white">
                            <h5 class="mb-0">Sortie du parking</h5>
                        </div>
                        <div class="card-body">
                            <div id="parking-exit" 
                                 class="parking-exit-zone"
                                 data-spot="exit">
                                Déposer ici pour retirer du parking
                            </div>
                        </div>
                    </div>
                    <!-- Liste des véhicules disponibles -->
                    <div class="card">
                        <div class="card-header bg-light">
                            <h5 class="mb-0">Véhicules disponibles</h5>
                        </div>
                        <div class="card-body">
                            <div id="vehicles-list">
                                <?php foreach ($vehicles as $vehicle): ?>
                                    <?php if (!$vehicle['spot_number']): ?>
                                        <div class="vehicle-item mb-2" 
                                             draggable="true"
                                             data-vehicle-id="<?= $vehicle['id'] ?>">
                                            <div class="card">
                                                <div class="card-body p-2">
                                                    <strong><?= htmlspecialchars($vehicle['brand'] . ' ' . $vehicle['model']) ?></strong>
                                                    <br>
                                                    <small><?= htmlspecialchars($vehicle['registration_number']) ?></small>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Grille du parking -->
                <div class="col-md-9">
                    <div class="parking-layout">
                        <!-- Section supérieure -->
                        <div class="top-section">
                            <div class="left-row">
                                <?php for($i = 1; $i <= 4; $i++): ?>
                                    <div class="parking-spot" 
                                         data-spot="<?= $i ?>"
                                         data-vehicle-id="<?= $spots[$i-1]['vehicle_id'] ?? '' ?>">
                                        <div class="spot-number"><?= $i ?></div>
                                        <?php if (isset($spots[$i-1]) && $spots[$i-1]['vehicle_id']): ?>
                                            <div class="vehicle-card" draggable="true">
                                                <strong><?= htmlspecialchars($spots[$i-1]['brand'] . ' ' . $spots[$i-1]['model']) ?></strong>
                                                <br>
                                                <small><?= htmlspecialchars($spots[$i-1]['registration_number']) ?></small>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php endfor; ?>
                            </div>
                            <div class="right-row">
                                <?php for($i = 5; $i <= 8; $i++): ?>
                                    <div class="parking-spot" 
                                         data-spot="<?= $i ?>"
                                         data-vehicle-id="<?= $spots[$i-1]['vehicle_id'] ?? '' ?>">
                                        <div class="spot-number"><?= $i ?></div>
                                        <?php if (isset($spots[$i-1]) && $spots[$i-1]['vehicle_id']): ?>
                                            <div class="vehicle-card" draggable="true">
                                                <strong><?= htmlspecialchars($spots[$i-1]['brand'] . ' ' . $spots[$i-1]['model']) ?></strong>
                                                <br>
                                                <small><?= htmlspecialchars($spots[$i-1]['registration_number']) ?></small>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php endfor; ?>
                            </div>
                        </div>
                        <!-- Couloir central -->
                        <div class="center-aisle">
                            <?php for($i = 9; $i <= 10; $i++): ?>
                                <div class="parking-spot" 
                                     data-spot="<?= $i ?>"
                                     data-vehicle-id="<?= $spots[$i-1]['vehicle_id'] ?? '' ?>">
                                    <div class="spot-number"><?= $i ?></div>
                                    <?php if (isset($spots[$i-1]) && $spots[$i-1]['vehicle_id']): ?>
                                        <div class="vehicle-card" draggable="true">
                                            <strong><?= htmlspecialchars($spots[$i-1]['brand'] . ' ' . $spots[$i-1]['model']) ?></strong>
                                            <br>
                                            <small><?= htmlspecialchars($spots[$i-1]['registration_number']) ?></small>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endfor; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.parking-layout {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 8px;
}

.top-section {
    display: flex;
    justify-content: space-between;
    margin-bottom: 20px;
}

.left-row, .right-row {
    display: flex;
    flex-direction: column;
    gap: 10px;
    width: 45%;
}

.center-aisle {
    width: 30%;
    margin: 0 auto;
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.parking-spot {
    background: #fff;
    border: 2px solid #dee2e6;
    border-radius: 8px;
    padding: 10px;
    position: relative;
    height: 100px;
    transition: all 0.3s ease;
}

.parking-spot:hover {
    border-color: #0d6efd;
}

.spot-number {
    position: absolute;
    top: 5px;
    left: 5px;
    background: #e9ecef;
    padding: 2px 6px;
    border-radius: 4px;
    font-size: 0.8rem;
}

.vehicle-card {
    height: 100%;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    text-align: center;
    cursor: move;
    background: #e3f2fd;
    border-radius: 6px;
    padding: 10px;
}

.vehicle-item {
    cursor: move;
}

.vehicle-item .card:hover {
    background: #e3f2fd;
}

.parking-exit-zone {
    border: 2px dashed #dc3545;
    border-radius: 8px;
    padding: 20px;
    text-align: center;
    color: #dc3545;
    min-height: 100px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
}

.parking-exit-zone.drag-over {
    background-color: #dc354522;
    border-style: solid;
}

.parking-spot[data-vehicle-id]:not([data-vehicle-id=""]) {
    background-color: #e3f2fd;
}

/* Ajouter ces styles pour les animations */
.vehicle-card, .vehicle-item {
    transition: all 0.3s ease;
}

.dragging {
    opacity: 0.5;
    transform: scale(0.95);
}

.drag-over {
    border-color: #0d6efd;
    transform: scale(1.02);
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const vehicles = document.querySelectorAll('.vehicle-item, .vehicle-card');
    const spots = document.querySelectorAll('.parking-spot');
    const exitZone = document.getElementById('parking-exit');

    // Drag & Drop pour les véhicules
    vehicles.forEach(vehicle => {
        vehicle.addEventListener('dragstart', handleDragStart);
        vehicle.addEventListener('dragend', handleDragEnd);
    });

    // Zones de dépôt pour les places de parking
    spots.forEach(spot => {
        spot.addEventListener('dragover', handleDragOver);
        spot.addEventListener('drop', handleDrop);
    });

    // Zone de sortie
    exitZone.addEventListener('dragover', handleDragOver);
    exitZone.addEventListener('drop', handleExitDrop);

    function handleDragStart(e) {
        const vehicleCard = e.target.closest('.vehicle-card, .vehicle-item');
        e.dataTransfer.setData('text/plain', vehicleCard.closest('[data-vehicle-id]').dataset.vehicleId);
        e.dataTransfer.setData('source-spot', vehicleCard.closest('[data-spot]').dataset.spot);
        vehicleCard.classList.add('dragging');
    }

    function handleDragEnd(e) {
        e.target.classList.remove('dragging');
        document.querySelectorAll('.drag-over').forEach(el => el.classList.remove('drag-over'));
    }

    function handleDragOver(e) {
        e.preventDefault();
        e.currentTarget.classList.add('drag-over');
    }

    function handleDrop(e) {
        e.preventDefault();
        const spot = e.target.closest('.parking-spot');
        const vehicleId = e.dataTransfer.getData('text/plain');
        const sourceSpot = e.dataTransfer.getData('source-spot');
        const targetSpot = spot.dataset.spot;

        // Si la place est déjà occupée, on échange les véhicules
        if (spot.dataset.vehicleId) {
            updateParkingSpots({
                vehicle_id: vehicleId,
                source_spot: sourceSpot,
                target_spot: targetSpot,
                action: 'swap'
            });
        } else {
            // Sinon on déplace simplement le véhicule
            updateParkingSpots({
                vehicle_id: vehicleId,
                source_spot: sourceSpot,
                target_spot: targetSpot,
                action: 'move'
            });
        }
    }

    function handleExitDrop(e) {
        e.preventDefault();
        const vehicleId = e.dataTransfer.getData('text/plain');
        const sourceSpot = e.dataTransfer.getData('source-spot');

        updateParkingSpots({
            vehicle_id: vehicleId,
            source_spot: sourceSpot,
            action: 'remove'
        });
    }

    function updateParkingSpots(data) {
        fetch('/parking/update_spot.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Au lieu de recharger, on met à jour le DOM
                switch (data.action) {
                    case 'move':
                        handleMoveUpdate(data.vehicle_id, data.source_spot, data.target_spot);
                        break;
                    case 'swap':
                        handleSwapUpdate(data.vehicle_id, data.source_spot, data.target_spot);
                        break;
                    case 'remove':
                        handleRemoveUpdate(data.vehicle_id, data.source_spot);
                        break;
                }
            } else {
                alert('Erreur lors de la mise à jour du parking: ' + data.error);
            }
        });
    }

    function handleMoveUpdate(vehicleId, sourceSpot, targetSpot) {
        const sourceElement = document.querySelector(`[data-spot="${sourceSpot}"]`);
        const targetElement = document.querySelector(`[data-spot="${targetSpot}"]`);
        const vehicleCard = sourceElement.querySelector('.vehicle-card');

        if (vehicleCard) {
            // Animer le déplacement
            vehicleCard.style.transition = 'all 0.3s ease';
            targetElement.appendChild(vehicleCard);
            
            // Mettre à jour les attributs
            sourceElement.dataset.vehicleId = '';
            targetElement.dataset.vehicleId = vehicleId;
        }
    }

    function handleSwapUpdate(vehicleId, sourceSpot, targetSpot) {
        const sourceElement = document.querySelector(`[data-spot="${sourceSpot}"]`);
        const targetElement = document.querySelector(`[data-spot="${targetSpot}"]`);
        const sourceCard = sourceElement.querySelector('.vehicle-card');
        const targetCard = targetElement.querySelector('.vehicle-card');

        if (sourceCard && targetCard) {
            // Animer l'échange
            sourceCard.style.transition = targetCard.style.transition = 'all 0.3s ease';
            
            // Créer des clones pour l'animation
            const sourceClone = sourceCard.cloneNode(true);
            const targetClone = targetCard.cloneNode(true);
            
            // Échanger les cartes
            sourceElement.removeChild(sourceCard);
            targetElement.removeChild(targetCard);
            sourceElement.appendChild(targetClone);
            targetElement.appendChild(sourceClone);

            // Mettre à jour les attributs
            const tempVehicleId = targetElement.dataset.vehicleId;
            targetElement.dataset.vehicleId = sourceElement.dataset.vehicleId;
            sourceElement.dataset.vehicleId = tempVehicleId;
        }
    }

    function handleRemoveUpdate(vehicleId, sourceSpot) {
        const sourceElement = document.querySelector(`[data-spot="${sourceSpot}"]`);
        const vehicleCard = sourceElement.querySelector('.vehicle-card');

        if (vehicleCard) {
            // Animer la suppression
            vehicleCard.style.transition = 'all 0.3s ease';
            vehicleCard.style.opacity = '0';
            
            setTimeout(() => {
                sourceElement.removeChild(vehicleCard);
                sourceElement.dataset.vehicleId = '';

                // Ajouter à la liste des véhicules disponibles
                const vehicleData = {
                    brand: vehicleCard.querySelector('strong').textContent,
                    registration_number: vehicleCard.querySelector('small').textContent
                };
                addToVehiclesList(vehicleData, vehicleId);
            }, 300);
        }
    }

    function addToVehiclesList(vehicleData, vehicleId) {
        const vehiclesList = document.getElementById('vehicles-list');
        const newVehicle = document.createElement('div');
        newVehicle.className = 'vehicle-item mb-2';
        newVehicle.draggable = true;
        newVehicle.dataset.vehicleId = vehicleId;
        
        newVehicle.innerHTML = `
            <div class="card">
                <div class="card-body p-2">
                    <strong>${vehicleData.brand}</strong>
                    <br>
                    <small>${vehicleData.registration_number}</small>
                </div>
            </div>
        `;

        // Ajouter les événements drag & drop
        newVehicle.addEventListener('dragstart', handleDragStart);
        newVehicle.addEventListener('dragend', handleDragEnd);

        // Ajouter avec animation
        newVehicle.style.opacity = '0';
        vehiclesList.appendChild(newVehicle);
        setTimeout(() => {
            newVehicle.style.transition = 'opacity 0.3s ease';
            newVehicle.style.opacity = '1';
        }, 10);
    }
});
</script>

<?php require_once '../includes/footer.php'; ?> 