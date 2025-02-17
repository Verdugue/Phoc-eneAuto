<?php
// Configuration de la session avant son démarrage
ini_set('session.gc_maxlifetime', 3600); // 1 heure
ini_set('session.cookie_lifetime', 3600);
session_set_cookie_params(3600); 