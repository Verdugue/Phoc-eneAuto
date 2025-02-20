<?php
session_start();
require_once '../includes/init.php';

header('Content-Type: application/json');

try {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['vehicle_id']) || !isset($data['action'])) {
        throw new Exception('Données manquantes');
    }

    $pdo->beginTransaction();

    switch ($data['action']) {
        case 'add':
            // Vérifier si la place est déjà occupée
            $stmt = $pdo->prepare("SELECT vehicle_id FROM parking_spots WHERE spot_number = ?");
            $stmt->execute([$data['target_spot']]);
            $existing_vehicle = $stmt->fetchColumn();
            
            if ($existing_vehicle) {
                throw new Exception('Cette place est déjà occupée par un autre véhicule.');
            }

            // Insérer ou mettre à jour la place
            $stmt = $pdo->prepare("
                INSERT INTO parking_spots (spot_number, vehicle_id, coordinates) 
                VALUES (?, ?, CONCAT(?, ',0'))
                ON DUPLICATE KEY UPDATE vehicle_id = VALUES(vehicle_id), coordinates = VALUES(coordinates)
            ");
            $stmt->execute([
                $data['target_spot'], 
                $data['vehicle_id'],
                $data['target_spot']
            ]);
            break;

        case 'move':
            // Libérer l'ancienne place et vérifier la nouvelle
            $stmt = $pdo->prepare("SELECT vehicle_id FROM parking_spots WHERE spot_number = ?");
            $stmt->execute([$data['target_spot']]);
            $existing_vehicle = $stmt->fetchColumn();
            
            if ($existing_vehicle) {
                throw new Exception('La nouvelle place est déjà occupée.');
            }
            
            $stmt = $pdo->prepare("UPDATE parking_spots SET vehicle_id = NULL WHERE spot_number = ?");
            $stmt->execute([$data['source_spot']]);

            $stmt = $pdo->prepare("UPDATE parking_spots SET vehicle_id = ?, coordinates = CONCAT(?, ',0') WHERE spot_number = ?");
            $stmt->execute([
                $data['vehicle_id'],
                $data['target_spot'],
                $data['target_spot']
            ]);
            break;

        case 'swap':
            // Échanger deux véhicules
            $stmt = $pdo->prepare("SELECT vehicle_id FROM parking_spots WHERE spot_number = ?");
            $stmt->execute([$data['target_spot']]);
            $target_vehicle = $stmt->fetchColumn();
            
            if (!$target_vehicle) {
                throw new Exception('Aucun véhicule à échanger.');
            }
            
            $stmt = $pdo->prepare("UPDATE parking_spots SET vehicle_id = CASE WHEN spot_number = ? THEN ? WHEN spot_number = ? THEN ? END WHERE spot_number IN (?, ?)");
            $stmt->execute([
                $data['source_spot'], $target_vehicle,
                $data['target_spot'], $data['vehicle_id'],
                $data['source_spot'], $data['target_spot']
            ]);
            break;

        case 'remove':
            // Retirer un véhicule du parking
            $stmt = $pdo->prepare("UPDATE parking_spots SET vehicle_id = NULL WHERE spot_number = ?");
            $stmt->execute([$data['source_spot']]);
            break;

        default:
            throw new Exception('Action non reconnue');
    }

    $pdo->commit();
    echo json_encode([
        'success' => true,
        'action' => $data['action'],
        'vehicle_id' => $data['vehicle_id'],
        'source_spot' => $data['source_spot'] ?? null,
        'target_spot' => $data['target_spot'] ?? null
    ]);

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
