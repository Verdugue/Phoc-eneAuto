<?php

/**
 * Génère un lien pour le tri des colonnes dans les tableaux
 *
 * @param string $field La colonne à trier
 * @param string $current_sort_field La colonne actuellement triée
 * @param string $current_sort_order L'ordre actuel du tri (ASC/DESC)
 * @return string L'URL avec les paramètres de tri
 */
function getSortLink($field, $current_sort_field, $current_sort_order) {
    $order = ($field === $current_sort_field && $current_sort_order === 'ASC') ? 'DESC' : 'ASC';
    $icon = '';
    if ($field === $current_sort_field) {
        $icon = $current_sort_order === 'ASC' ? ' ↑' : ' ↓';
    }
    return "?sort={$field}&order={$order}&page=" . ($_GET['page'] ?? 1) . $icon;
}

/**
 * Retourne l'URL de l'image principale d'un véhicule ou un emoji voiture par défaut
 * 
 * @param array $vehicle Les données du véhicule
 * @return string L'URL de l'image ou un emoji voiture
 */
function getVehicleImage($vehicle) {
    // Récupérer l'image principale du véhicule
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT file_path 
        FROM vehicle_images 
        WHERE vehicle_id = ? AND is_primary = 1 
        LIMIT 1
    ");
    $stmt->execute([$vehicle['id']]);
    $image = $stmt->fetchColumn();

    if ($image) {
        return $image;
    }

    // Si pas d'image, retourner une classe CSS pour l'emoji
    return 'car-emoji';
}

/**
 * Affiche la vignette d'un véhicule (image ou emoji)
 * 
 * @param array $vehicle Les données du véhicule
 * @param string $class Classes CSS additionnelles
 * @return string Le HTML de la vignette
 */
function renderVehicleThumbnail($vehicle, $class = '') {
    $image = getVehicleImage($vehicle);
    
    if ($image === 'car-emoji') {
        return "<div class='vehicle-emoji {$class}'>🚗</div>";
    }
    
    return "<img src='{$image}' alt='{$vehicle['brand']} {$vehicle['model']}' class='{$class}'>";
} 