<?php
/**
 * API pour sauvegarder une configuration
 * Accepte des données au format JSON dans le corps de la requête POST
 * Structure attendue:
 * {
 *    "nom": "John Doe",
 *    "numero_secu": "123456789012345",
 *    "telephone": "0123456789",
 *    "adresse": "123 Rue Example, Ville, Pays",
 *    "groupe_sanguin": "O+",
 *    "temps_acquisition": 60
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
$requiredFields = ['nom', 'numero_secu', 'telephone', 'adresse', 'groupe_sanguin', 'temps_acquisition'];
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
$nom = trim($data['nom']);
$numeroSecu = trim($data['numero_secu']);
$telephone = trim($data['telephone']);
$adresse = trim($data['adresse']);
$groupeSanguin = trim($data['groupe_sanguin']);
$tempsAcquisition = (int)$data['temps_acquisition'];

// Validation supplémentaire
if (strlen($numeroSecu) !== 15 || !ctype_digit($numeroSecu)) {
    http_response_code(400); // Bad Request
    echo json_encode(['error' => 'Numéro de sécurité sociale invalide (15 chiffres attendus)']);
    exit;
}

if ($tempsAcquisition <= 0) {
    http_response_code(400); // Bad Request
    echo json_encode(['error' => 'Temps d\'acquisition invalide (doit être supérieur à 0)']);
    exit;
}

try {
    // Hashage et encodage des données sensibles
    $nomHash = hashSensitiveData($nom);
    $nomEncoded = encodeBase64($nom);
    $numeroSecuHash = hashSensitiveData($numeroSecu);
    $numeroSecuEncoded = encodeBase64($numeroSecu);
    $adresseEncoded = encodeBase64($adresse);
    
    // Insertion des données du patient
    $patientData = [
        'nom_hash' => $nomHash,
        'nom_encoded' => $nomEncoded,
        'numero_secu_hash' => $numeroSecuHash,
        'numero_secu_encoded' => $numeroSecuEncoded,
        'telephone' => $telephone,
        'adresse_encoded' => $adresseEncoded,
        'groupe_sanguin' => $groupeSanguin
    ];
    
    $patientId = insert('patients', $patientData);
    
    if ($patientId) {
        // Insertion de la configuration
        $configData = [
            'patient_id' => $patientId,
            'temps_acquisition' => $tempsAcquisition
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