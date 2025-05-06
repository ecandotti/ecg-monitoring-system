<?php
/**
 * API pour récupérer les informations d'un diagnostic
 * Accepte un paramètre id dans la requête GET
 * Retourne les détails du diagnostic au format JSON
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

// Récupération et validation du paramètre id
$diagnosticId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($diagnosticId <= 0) {
    http_response_code(400); // Bad Request
    echo json_encode(['error' => 'ID de diagnostic invalide']);
    exit;
}

// En-têtes pour la réponse JSON
header('Content-Type: application/json');
header('Cache-Control: no-cache, no-store, must-revalidate');

try {
    // Récupération des données du diagnostic
    $sql = "SELECT d.*, c.acquisition_time, c.patient_id 
            FROM diagnostics d 
            JOIN configurations c ON d.configuration_id = c.id 
            WHERE d.id = ?";
    $diagnostic = fetchOne($sql, [$diagnosticId]);
    
    if (!$diagnostic) {
        http_response_code(404); // Not Found
        echo json_encode(['error' => 'Diagnostic non trouvé']);
        exit;
    }
    
    // Récupération des informations du patient
    $sql = "SELECT * FROM patients WHERE id = ?";
    $patient = fetchOne($sql, [$diagnostic['patient_id']]);
    
    if (!$patient) {
        http_response_code(404); // Not Found
        echo json_encode(['error' => 'Patient non trouvé']);
        exit;
    }
    
    // Préparation des données pour la réponse JSON
    $response = [
        'id' => $diagnostic['id'],
        'configuration_id' => $diagnostic['configuration_id'],
        'professor_name' => $diagnostic['professor_name'],
        'consultation_address' => $diagnostic['consultation_address'],
        'report' => $diagnostic['report'],
        'atrial_release_time' => (float)$diagnostic['atrial_release_time'],
        'p_wave' => (float)$diagnostic['p_wave'],
        'q_wave' => (float)$diagnostic['q_wave'],
        'r_wave' => (float)$diagnostic['r_wave'],
        's_wave' => (float)$diagnostic['s_wave'],
        't_wave' => (float)$diagnostic['t_wave'],
        'diagnosis_date' => $diagnostic['diagnosis_date'],
        'patient' => [
            'id' => $patient['id'],
            'name' => decodeBase64($patient['name_encoded']),
            'blood_type' => $patient['blood_type'],
            'phone' => $patient['phone']
        ],
        'acquisition' => [
            'acquisition_time' => (int)$diagnostic['acquisition_time']
        ]
    ];
    
    // Envoi de la réponse JSON
    echo json_encode($response);
    
} catch (Exception $e) {
    // En cas d'erreur, retourner un message approprié
    http_response_code(500); // Internal Server Error
    echo json_encode([
        'error' => 'Erreur lors de la récupération du diagnostic',
        'details' => DEBUG ? $e->getMessage() : null
    ]);
} 