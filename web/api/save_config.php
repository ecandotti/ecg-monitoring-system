<?php
/**
 * API pour sauvegarder une configuration
 * Accepte des données au format JSON dans le corps de la requête POST
 * Structure attendue (JSON):
 * {
 *    "name": "John Doe",
 *    "secu": "123456789012345",
 *    "phone": "0123456789",
 *    "address": "123 Rue Example, Ville, Pays",
 *    "blood_type": "O+",
 *    "acquisition_time": 60
 * }
 * 
 * Accepte également des données de formulaire traditionnel avec les noms:
 * nom, numero_secu, telephone, adresse, groupe_sanguin, temps_acquisition
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

// Détecter le type de contenu
$contentType = isset($_SERVER['CONTENT_TYPE']) ? $_SERVER['CONTENT_TYPE'] : '';
$isJsonRequest = strpos($contentType, 'application/json') !== false;

if ($isJsonRequest) {
    // Récupération des données JSON du corps de la requête
    $jsonData = file_get_contents('php://input');
    $data = json_decode($jsonData, true);
    
    // Vérification de la validité des données JSON
    if ($data === null) {
        http_response_code(400); // Bad Request
        echo json_encode(['error' => 'Données JSON invalides']);
        exit;
    }
} else {
    // Récupération des données de formulaire traditionnelles
    $data = [
        'name' => isset($_POST['nom']) ? $_POST['nom'] : '',
        'secu' => isset($_POST['numero_secu']) ? $_POST['numero_secu'] : '',
        'phone' => isset($_POST['telephone']) ? $_POST['telephone'] : '',
        'address' => isset($_POST['adresse']) ? $_POST['adresse'] : '',
        'blood_type' => isset($_POST['groupe_sanguin']) ? $_POST['groupe_sanguin'] : '',
        'acquisition_time' => isset($_POST['temps_acquisition']) ? (int)$_POST['temps_acquisition'] : 0
    ];
}

// Vérification des paramètres requis
$requiredFields = ['name', 'secu', 'phone', 'address', 'blood_type', 'acquisition_time'];
$missingFields = [];

foreach ($requiredFields as $field) {
    if (!isset($data[$field]) || empty($data[$field])) {
        $missingFields[] = $field;
    }
}

if (!empty($missingFields)) {
    http_response_code(400); // Bad Request
    echo json_encode([
        'error' => 'Paramètres manquants',
        'missing_fields' => $missingFields
    ]);
    exit;
}

// Récupération et validation des données
$name = trim($data['name']);
$secu = trim($data['secu']);
$phone = trim($data['phone']);
$address = trim($data['address']);
$bloodType = trim($data['blood_type']);
$acquisitionTime = (int)$data['acquisition_time'];

// Validation supplémentaire
if (strlen($secu) !== 15 || !ctype_digit($secu)) {
    http_response_code(400); // Bad Request
    echo json_encode(['error' => 'Numéro de sécurité sociale invalide (15 chiffres attendus)']);
    exit;
}

if ($acquisitionTime <= 0) {
    http_response_code(400); // Bad Request
    echo json_encode(['error' => 'Temps d\'acquisition invalide (doit être supérieur à 0)']);
    exit;
}

try {
    // Hashage et encodage des données sensibles
    $nameHash = hashSensitiveData($name);
    $nameEncoded = encodeBase64($name);
    $secuHash = hashSensitiveData($secu);
    $secuEncoded = encodeBase64($secu);
    $addressEncoded = encodeBase64($address);
    
    // Insertion des données du patient
    $patientData = [
        'name_hash' => $nameHash,
        'name_encoded' => $nameEncoded,
        'secu_hash' => $secuHash,
        'secu_encoded' => $secuEncoded,
        'phone' => $phone,
        'address_encoded' => $addressEncoded,
        'blood_type' => $bloodType
    ];
    
    $patientId = insert('patients', $patientData);
    
    if ($patientId) {
        // Insertion de la configuration
        $configData = [
            'patient_id' => $patientId,
            'acquisition_time' => $acquisitionTime
        ];
        
        $configId = insert('configurations', $configData);
        
        if ($configId) {
            // Réponse réussie
            echo json_encode([
                'success' => true,
                'message' => 'Configuration enregistrée avec succès',
                'config_id' => $configId,
                'patient_id' => $patientId
            ]);
        } else {
            // Erreur lors de l'insertion de la configuration
            http_response_code(500); // Internal Server Error
            echo json_encode(['error' => 'Erreur lors de l\'enregistrement de la configuration']);
        }
    } else {
        // Erreur lors de l'insertion du patient
        http_response_code(500); // Internal Server Error
        echo json_encode(['error' => 'Erreur lors de l\'enregistrement des informations du patient']);
    }
} catch (Exception $e) {
    // En cas d'erreur, retourner un message approprié
    http_response_code(500); // Internal Server Error
    echo json_encode([
        'error' => 'Erreur lors de l\'enregistrement des données',
        'details' => DEBUG ? $e->getMessage() : null
    ]);
} 