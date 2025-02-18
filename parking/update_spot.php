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
        case 'move':
            // Déplacer un véhicule vers une place vide
            $stmt = $pdo->prepare("UPDATE parking_spots SET vehicle_id = NULL WHERE spot_number = ?");
            $stmt->execute([$data['source_spot']]);

            $stmt = $pdo->prepare("UPDATE parking_spots SET vehicle_id = ? WHERE spot_number = ?");
            $stmt->execute([$data['vehicle_id'], $data['target_spot']]);
            break;

        case 'swap':
            // Échanger deux véhicules
            $stmt = $pdo->prepare("SELECT vehicle_id FROM parking_spots WHERE spot_number = ?");
            $stmt->execute([$data['target_spot']]);
            $target_vehicle = $stmt->fetchColumn();

            $stmt = $pdo->prepare("
                UPDATE parking_spots 
                SET vehicle_id = CASE 
                    WHEN spot_number = ? THEN ?
                    WHEN spot_number = ? THEN ?
                    END
                WHERE spot_number IN (?, ?)
            ");
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
        'source_spot' => $data['source_spot'],
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