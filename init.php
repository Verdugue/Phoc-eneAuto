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