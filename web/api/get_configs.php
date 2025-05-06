<?php
/**
 * API pour récupérer la liste des configurations
 * Accepte un paramètre limit optionnel dans la requête GET pour limiter le nombre de résultats
 * Retourne la liste des configurations avec les informations patient associées
 */

// Inclusion des fichiers nécessaires
require_once '../config/env.php';
require_once '../config/database.php';
require_once '../config/security.php';

// Vérification que la requête est de type GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405); // Method Not Allowed
    echo json_encode(['error' => 'Méthode non autorisée']);
    exit;
}

// En-têtes pour la réponse JSON
header('Content-Type: application/json');
header('Cache-Control: no-cache, no-store, must-revalidate');

// Récupération du paramètre limit (optionnel)
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
if ($limit <= 0) $limit = 10;

try {
    // Requête pour récupérer les configurations avec les informations patient
    $sql = "SELECT c.id, c.acquisition_time, c.config_date, 
                p.id as patient_id, p.name_encoded, p.phone, p.blood_type
            FROM configurations c
            JOIN patients p ON c.patient_id = p.id
            ORDER BY c.config_date DESC
            LIMIT ?";
    
    $configs = fetchAll($sql, [$limit]);
    
    // Transformation des données pour la réponse
    $result = [];
    foreach ($configs as $config) {
        $result[] = [
            'id' => $config['id'],
            'acquisition_time' => $config['acquisition_time'],
            'config_date' => $config['config_date'],
            'patient' => [
                'id' => $config['patient_id'],
                'name' => decodeBase64($config['name_encoded']),
                'phone' => $config['phone'],
                'blood_type' => $config['blood_type']
            ]
        ];
    }
    
    // Envoi de la réponse
    echo json_encode([
        'success' => true,
        'count' => count($result),
        'configurations' => $result
    ]);
    
} catch (Exception $e) {
    // En cas d'erreur, retourner un message approprié
    http_response_code(500); // Internal Server Error
    echo json_encode([
        'error' => 'Erreur lors de la récupération des configurations',
        'details' => DEBUG ? $e->getMessage() : null
    ]);
} 