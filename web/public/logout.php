<?php
// Script de déconnexion
session_start();

// Destruction des variables de session
$_SESSION = array();

// Destruction du cookie de session
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Suppression du cookie "remember_token" s'il existe
if (isset($_COOKIE['remember_token'])) {
    setcookie('remember_token', '', time() - 3600, '/', '', false, true);
}

// Destruction de la session
session_destroy();

// Redirection vers la page d'accueil
header('Location: /public/index.php');
exit; 