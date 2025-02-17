<?php
// Vérifier si la session n'est pas déjà démarrée
if (session_status() === PHP_SESSION_NONE) {
    // Configuration de la session
    ini_set('session.gc_maxlifetime', 3600);
    ini_set('session.cookie_lifetime', 3600);
    session_set_cookie_params(3600);
    
    // Démarrer la session
    session_start();
} 

// Fonction helper pour obtenir l'image de profil
function get_profile_image($image_path) {
    $default_image = '/assets/images/defaults/default-profile.png';
    
    // Si un chemin d'image est fourni, vérifier s'il existe
    if (!empty($image_path)) {
        $server_path = $_SERVER['DOCUMENT_ROOT'] . $image_path;
        if (file_exists($server_path)) {
            return $image_path;
        }
    }
    
    // Vérifier si l'image par défaut existe
    $default_server_path = $_SERVER['DOCUMENT_ROOT'] . $default_image;
    if (!file_exists($default_server_path)) {
        create_default_profile_image();
    }
    
    return $default_image;
}

// Fonction pour créer l'image par défaut si elle n'existe pas
function create_default_profile_image() {
    $dir = $_SERVER['DOCUMENT_ROOT'] . '/assets/images/defaults';
    if (!file_exists($dir)) {
        mkdir($dir, 0777, true);
    }
    
    // Créer une image circulaire
    $width = 150;
    $height = 150;
    $image = imagecreatetruecolor($width, $height);
    
    // Activer la transparence
    imagealphablending($image, true);
    imagesavealpha($image, true);
    
    // Couleurs
    $transparent = imagecolorallocatealpha($image, 0, 0, 0, 127);
    $bg_color = imagecolorallocate($image, 100, 149, 237); // Bleu cornflower
    $text_color = imagecolorallocate($image, 255, 255, 255);
    
    // Remplir avec la transparence
    imagefilledrectangle($image, 0, 0, $width, $height, $transparent);
    
    // Dessiner un cercle
    $center_x = $width / 2;
    $center_y = $height / 2;
    $radius = min($width, $height) / 2;
    imagefilledellipse($image, $center_x, $center_y, $radius * 2, $radius * 2, $bg_color);
    
    // Ajouter les initiales "PA" (pour Phocéenne Auto)
    $text = "PA";
    $font_size = 5; // Taille de police plus grande
    $text_box = imagettfbbox($font_size, 0, "Arial", $text);
    $text_width = abs($text_box[4] - $text_box[0]);
    $text_height = abs($text_box[5] - $text_box[1]);
    $text_x = $center_x - ($text_width / 2);
    $text_y = $center_y + ($text_height / 2);
    imagestring($image, $font_size, $text_x, $text_y - 10, $text, $text_color);
    
    // Sauvegarder l'image
    imagepng($image, $dir . '/default-profile.png');
    imagedestroy($image);
} 