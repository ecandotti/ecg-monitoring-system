<?php
/**
 * Fonctions utilitaires pour l'application
 */

/**
 * Redirige vers une URL spécifiée
 *
 * @param string $url L'URL de redirection
 * @return void
 */
function redirect($url) {
    header("Location: $url");
    exit;
}

/**
 * Affiche un message de succès dans une alerte
 *
 * @param string $message Le message à afficher
 * @return string Code HTML pour l'alerte
 */
function showSuccess($message) {
    return '<div class="alert alert-success">' . $message . '</div>';
}

/**
 * Affiche un message d'erreur dans une alerte
 *
 * @param string $message Le message à afficher
 * @return string Code HTML pour l'alerte
 */
function showError($message) {
    return '<div class="alert alert-danger">' . $message . '</div>';
}

/**
 * Affiche un message d'information dans une alerte
 *
 * @param string $message Le message à afficher
 * @return string Code HTML pour l'alerte
 */
function showInfo($message) {
    return '<div class="alert alert-info">' . $message . '</div>';
}

/**
 * Vérifie si l'utilisateur est connecté
 *
 * @return bool True si l'utilisateur est connecté, false sinon
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Vérifie si l'utilisateur a un rôle spécifique
 *
 * @param string $role Le rôle à vérifier
 * @return bool True si l'utilisateur a le rôle spécifié, false sinon
 */
function hasRole($role) {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === $role;
}

/**
 * Affiche la date au format français
 *
 * @param string $date Date au format MySQL
 * @return string Date au format français (jj/mm/aaaa)
 */
function formatDate($date) {
    $timestamp = strtotime($date);
    return date('d/m/Y', $timestamp);
}

/**
 * Formate une valeur ECG pour l'affichage
 *
 * @param float $value La valeur à formater
 * @return string Valeur formatée
 */
function formatEcgValue($value) {
    return number_format($value, 2, ',', ' ') . ' mV';
}

/**
 * Fonction pour générer un ID unique pour une session d'acquisition
 *
 * @return string ID unique
 */
function generateSessionId() {
    return 'ECG-' . date('Ymd') . '-' . bin2hex(random_bytes(4));
}

/**
 * Crée un nom de fichier pour l'exportation de données ECG
 * 
 * @param int $patientId ID du patient
 * @param string $date Date au format Y-m-d
 * @return string Nom du fichier
 */
function createEcgFileName($patientId, $date = null) {
    $date = $date ?: date('Y-m-d');
    return "ecg_" . $patientId . "_" . $date . ".csv";
} 