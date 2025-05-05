<?php
/**
 * API pour sauvegarder les données ECG provenant du Raspberry Pi
 * Accepte des données au format JSON dans le corps de la requête POST
 * Structure attendue: 
 * {
 *    "config_id": 123,
 *    "samples": [
 *       { "timestamp": 0.0, "valeur": 0.5 },
 *       { "timestamp": 0.004, "valeur": 0.52 },
 *       ...
 *    ]
 * }
 */

// Inclusion des fichiers nécessaires
require_once '../config/env.php';
require_once '../config/database.php';
require_once '../config/security.php';

// Vérification que la requête est de type POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Method Not Allowed
    echo json_encode(['error' => 'Méthode non autorisée']);
    exit;
}

// En-têtes pour la réponse JSON
header('Content-Type: application/json');

// Récupération des données JSON du corps de la requête
$jsonData = file_get_contents('php://input');
$data = json_decode($jsonData, true);

// Vérification de la validité des données JSON
if ($data === null) {
    http_response_code(400); // Bad Request
    echo json_encode(['error' => 'Données JSON invalides']);
    exit;
}

// Vérification des paramètres requis
if (!isset($data['config_id']) || !isset($data['samples']) || !is_array($data['samples'])) {
    http_response_code(400); // Bad Request
    echo json_encode(['error' => 'Paramètres manquants ou invalides']);
    exit;
}

$configId = (int)$data['config_id'];
$samples = $data['samples'];

// Vérification de l'existence de la configuration
try {
    $sql = "SELECT * FROM configurations WHERE id = ?";
    $config = fetchOne($sql, [$configId]);
    
    if (!$config) {
        http_response_code(404); // Not Found
        echo json_encode(['error' => 'Configuration non trouvée']);
        exit;
    }
    
    // Connexion à la base de données
    $db = getDbConnection();
    
    // Début de la transaction
    $db->beginTransaction();
    
    try {
        // Préparation de la requête d'insertion
        $stmt = $db->prepare("INSERT INTO ecg_data (configuration_id, timestamp, valeur) VALUES (?, ?, ?)");
        
        // Compteur d'échantillons insérés
        $insertedSamples = 0;
        
        // Insertion de chaque échantillon
        foreach ($samples as $sample) {
            if (!isset($sample['timestamp']) || !isset($sample['valeur'])) {
                continue; // Ignorer les échantillons mal formés
            }
            
            $timestamp = (float)$sample['timestamp'];
            $valeur = (float)$sample['valeur'];
            
            $stmt->execute([$configId, $timestamp, $valeur]);
            $insertedSamples++;
        }
        
        // Validation de la transaction
        $db->commit();
        
        // Réponse réussie
        echo json_encode([
            'success' => true,
            'message' => "Données ECG enregistrées avec succès",
            'inserted_samples' => $insertedSamples
        ]);
        
    } catch (Exception $e) {
        // Annulation de la transaction en cas d'erreur
        $db->rollBack();
        throw $e;
    }
    
} catch (Exception $e) {
    // En cas d'erreur, retourner un message approprié
    http_response_code(500); // Internal Server Error
    echo json_encode([
        'error' => 'Erreur lors de l\'enregistrement des données ECG',
        'details' => DEBUG ? $e->getMessage() : null
    ]);
} 