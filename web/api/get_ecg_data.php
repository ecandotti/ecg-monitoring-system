<?php
/**
 * API pour récupérer les données ECG
 * Accepte un paramètre config_id dans la requête GET
 * Retourne les données ECG au format JSON
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

// Récupération et validation du paramètre config_id
$configId = isset($_GET['config_id']) ? (int)$_GET['config_id'] : 0;

if ($configId <= 0) {
    http_response_code(400); // Bad Request
    echo json_encode(['error' => 'ID de configuration invalide']);
    exit;
}

// En-têtes pour la réponse JSON
header('Content-Type: application/json');
header('Cache-Control: no-cache, no-store, must-revalidate');

try {
    // Vérification de l'existence de la configuration
    $sql = "SELECT * FROM configurations WHERE id = ?";
    $config = fetchOne($sql, [$configId]);
    
    if (!$config) {
        http_response_code(404); // Not Found
        echo json_encode(['error' => 'Configuration non trouvée']);
        exit;
    }
    
    // Récupération des données ECG
    $sql = "SELECT * FROM ecg_data WHERE configuration_id = ? ORDER BY timestamp";
    $ecgData = fetchAll($sql, [$configId]);
    
    // Si aucune donnée ECG n'est trouvée, on simule des données
    if (empty($ecgData)) {
        // Pour la démonstration, création de données ECG simulées
        $samples = [];
        $sampleCount = 500; // Nombre d'échantillons simulés
        $frequence = 250; // Hz
        $duree = $config['temps_acquisition'];
        $sampleCount = $frequence * $duree; // Nombre total d'échantillons
        
        // Génération d'un signal ECG basique simulé
        for ($i = 0; $i < $sampleCount; $i++) {
            $time = $i / $frequence;
            
            // Simulation d'un signal ECG basique avec fonction sinus et impulsions
            $value = 0.5 * sin(2 * M_PI * 1 * $time); // Composante de base
            
            // Ajouter des ondes P, QRS et T
            if ($i % 250 >= 50 && $i % 250 <= 60) {
                // Onde P
                $value += 0.25 * sin(2 * M_PI * 10 * ($time - floor($time)));
            } else if ($i % 250 >= 100 && $i % 250 <= 110) {
                // Complexe QRS (onde Q)
                $value -= 0.2;
            } else if ($i % 250 >= 110 && $i % 250 <= 115) {
                // Complexe QRS (onde R)
                $value += 1.0;
            } else if ($i % 250 >= 115 && $i % 250 <= 125) {
                // Complexe QRS (onde S)
                $value -= 0.3;
            } else if ($i % 250 >= 150 && $i % 250 <= 180) {
                // Onde T
                $value += 0.3 * sin(2 * M_PI * 5 * ($time - floor($time)));
            }
            
            // Ajout de bruit aléatoire
            $value += (mt_rand(-10, 10) / 100);
            
            $samples[] = [
                'timestamp' => $time,
                'valeur' => $value
            ];
        }
        
        // Positions et valeurs des ondes pour la démonstration
        $wavePositions = [
            'p_index' => 55 + (0 * 250), // Position de l'onde P dans le tableau (premier battement)
            'q_index' => 100 + (0 * 250), // Position de l'onde Q
            'r_index' => 112 + (0 * 250), // Position de l'onde R
            's_index' => 120 + (0 * 250), // Position de l'onde S
            't_index' => 165 + (0 * 250)  // Position de l'onde T
        ];
        
        // Réponse avec les données simulées
        echo json_encode([
            'config_id' => $configId,
            'samples' => $samples,
            'wave_positions' => $wavePositions,
            'simulation' => true
        ]);
    } else {
        // Transformation des données réelles pour la réponse JSON
        $samples = array_map(function($row) {
            return [
                'timestamp' => (float)$row['timestamp'],
                'valeur' => (float)$row['valeur']
            ];
        }, $ecgData);
        
        // Récupération des positions des ondes depuis la base de données (si disponible)
        // Dans une implémentation réelle, cela serait stocké dans une table spécifique
        // Ici, nous utilisons des valeurs fictives pour la démonstration
        $sql = "SELECT * FROM diagnostics WHERE configuration_id = ? LIMIT 1";
        $diagnostic = fetchOne($sql, [$configId]);
        
        if ($diagnostic) {
            // Détermination des indices approximatifs des ondes
            // Ceci est une approximation simplifiée à des fins de démonstration
            $totalSamples = count($samples);
            $wavePositions = [
                'p_index' => (int)($totalSamples * 0.05),
                'q_index' => (int)($totalSamples * 0.1),
                'r_index' => (int)($totalSamples * 0.12),
                's_index' => (int)($totalSamples * 0.15),
                't_index' => (int)($totalSamples * 0.25)
            ];
        } else {
            $wavePositions = null;
        }
        
        // Réponse avec les données réelles
        echo json_encode([
            'config_id' => $configId,
            'samples' => $samples,
            'wave_positions' => $wavePositions,
            'simulation' => false
        ]);
    }
} catch (Exception $e) {
    // En cas d'erreur, retourner un message approprié
    http_response_code(500); // Internal Server Error
    echo json_encode([
        'error' => 'Erreur lors de la récupération des données ECG',
        'details' => DEBUG ? $e->getMessage() : null
    ]);
} 