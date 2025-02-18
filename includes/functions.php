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