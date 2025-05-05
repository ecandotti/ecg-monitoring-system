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
    $sql = "SELECT d.*, c.temps_acquisition, c.patient_id 
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
        'nom_professeur' => $diagnostic['nom_professeur'],
        'adresse_consultation' => $diagnostic['adresse_consultation'],
        'compte_rendu' => $diagnostic['compte_rendu'],
        'temps_relachement_oreillettes' => (float)$diagnostic['temps_relachement_oreillettes'],
        'onde_p' => (float)$diagnostic['onde_p'],
        'onde_q' => (float)$diagnostic['onde_q'],
        'onde_r' => (float)$diagnostic['onde_r'],
        'onde_s' => (float)$diagnostic['onde_s'],
        'onde_t' => (float)$diagnostic['onde_t'],
        'date_diagnostic' => $diagnostic['date_diagnostic'],
        'patient' => [
            'id' => $patient['id'],
            'nom' => decodeBase64($patient['nom_encoded']),
            'groupe_sanguin' => $patient['groupe_sanguin'],
            'telephone' => $patient['telephone']
        ],
        'acquisition' => [
            'temps_acquisition' => (int)$diagnostic['temps_acquisition']
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