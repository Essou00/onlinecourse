<?php
// Démarre la session si elle n'a pas déjà été démarrée
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Génère un jeton CSRF unique pour chaque session utilisateur
$csrf_token = bin2hex(random_bytes(32));

// Stocke le jeton CSRF dans un cookie ou dans une variable de session
$_SESSION['csrf_token'] = $csrf_token;

// Fonction pour insérer le jeton CSRF dans un formulaire
function csrf_field() {
    global $csrf_token;
    echo '<input type="hidden" name="csrf_token" value="' . $_SESSION['csrf_token'] . '">';
}
?>