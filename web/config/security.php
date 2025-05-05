<?php
/**
 * Fonctions de sécurité, cryptage et hashage
 * 
 * Ce fichier contient toutes les fonctions liées à la sécurité et au traitement 
 * des données sensibles avec la clé de hashage "test2025"
 */

// Inclusion de la configuration d'environnement
require_once __DIR__ . '/env.php';

/**
 * Hashe une donnée sensible avec la clé définie
 * 
 * @param string $data La donnée à hasher
 * @return string La donnée hashée
 */
function hashData($data) {
    // Utilise la clé de hashage définie dans les variables d'environnement
    return hash('sha256', $data . HASH_KEY);
}

/**
 * Encode une donnée en base64
 * 
 * @param string $data La donnée à encoder
 * @return string La donnée encodée en base64
 */
function encodeBase64($data) {
    return base64_encode($data);
}

/**
 * Décode une donnée en base64
 * 
 * @param string $data La donnée encodée à décoder
 * @return string La donnée décodée
 */
function decodeBase64($data) {
    return base64_decode($data);
}

/**
 * Vérifie si un hash correspond à une donnée
 * 
 * @param string $data La donnée brute
 * @param string $hash Le hash stocké
 * @return bool True si le hash correspond, false sinon
 */
function verifyHash($data, $hash) {
    return hashData($data) === $hash;
}

/**
 * Crée un hash de mot de passe sécurisé
 * 
 * @param string $password Le mot de passe en clair
 * @return string Le hash du mot de passe
 */
function hashPassword($password) {
    return password_hash($password, PASSWORD_BCRYPT);
}

/**
 * Vérifie un mot de passe par rapport à son hash
 * 
 * @param string $password Le mot de passe en clair
 * @param string $hash Le hash du mot de passe
 * @return bool True si le mot de passe correspond, false sinon
 */
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * Génère un jeton CSRF pour protéger les formulaires
 * 
 * @return string Le jeton CSRF
 */
function generateCsrfToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Vérifie la validité d'un jeton CSRF
 * 
 * @param string $token Le jeton à vérifier
 * @return bool True si le jeton est valide, false sinon
 */
function verifyCsrfToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Nettoie les données d'entrée pour éviter les injections
 * 
 * @param string $input La chaîne à nettoyer
 * @return string La chaîne nettoyée
 */
function sanitizeInput($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
} 