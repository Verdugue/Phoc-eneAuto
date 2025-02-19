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
            // Vérifier si la place existe déjà
            $stmt = $pdo->prepare("SELECT spot_number FROM parking_spots WHERE spot_number = ?");
            $stmt->execute([$data['target_spot']]);
            
            if ($stmt->fetch()) {
                // Si la place existe, faire une mise à jour
                $stmt = $pdo->prepare("
                    UPDATE parking_spots 
                    SET vehicle_id = ?, 
                        coordinates = CONCAT(?, ',0')
                    WHERE spot_number = ?
                ");
                $stmt->execute([
                    $data['vehicle_id'],
                    $data['target_spot'],
                    $data['target_spot']
                ]);
            } else {
                // Si la place n'existe pas, faire une insertion
                $stmt = $pdo->prepare("
                    INSERT INTO parking_spots (spot_number, vehicle_id, coordinates) 
                    VALUES (?, ?, CONCAT(?, ',0'))
                ");
                $stmt->execute([
                    $data['target_spot'], 
                    $data['vehicle_id'],
                    $data['target_spot']
                ]);
            }
            break;

        case 'move':
            // Libérer l'ancienne place
            $stmt = $pdo->prepare("UPDATE parking_spots SET vehicle_id = NULL WHERE spot_number = ?");
            $stmt->execute([$data['source_spot']]);

            // Mettre à jour la nouvelle place
            $stmt = $pdo->prepare("
                UPDATE parking_spots 
                SET vehicle_id = ?,
                    coordinates = CONCAT(?, ',0')
                WHERE spot_number = ?
            ");
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