<?php
// Page de diagnostic du système de monitoring ECG
$pageTitle = "Diagnostic";
$extraCss = "/css/diagnostic.css";
$extraJs = "/js/ecg-visualization.js";
session_start();
include_once '../../includes/header.php';

// Récupération des diagnostics existants ou du diagnostic spécifique demandé
$diagnosticId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$diagnostic = null;
$patient = null;
$configuration = null;

// Si on a un ID de diagnostic spécifique
if ($diagnosticId > 0) {
    try {
        // Récupération des données du diagnostic
        $sql = "SELECT d.*, c.temps_acquisition, c.patient_id 
                FROM diagnostics d 
                JOIN configurations c ON d.configuration_id = c.id 
                WHERE d.id = ?";
        $diagnostic = fetchOne($sql, [$diagnosticId]);
        
        if ($diagnostic) {
            // Récupération des informations du patient
            $sql = "SELECT * FROM patients WHERE id = ?";
            $patient = fetchOne($sql, [$diagnostic['patient_id']]);
            
            // Récupération de la configuration
            $sql = "SELECT * FROM configurations WHERE id = ?";
            $configuration = fetchOne($sql, [$diagnostic['configuration_id']]);
        }
    } catch (Exception $e) {
        $_SESSION['error'] = 'Erreur lors de la récupération du diagnostic: ' . ($DEBUG ? $e->getMessage() : 'Contactez l\'administrateur');
    }
}

// Récupération de tous les diagnostics pour la liste
try {
    $sql = "SELECT d.id, d.nom_professeur, d.date_diagnostic, p.nom_encoded, p.groupe_sanguin
            FROM diagnostics d
            JOIN configurations c ON d.configuration_id = c.id
            JOIN patients p ON c.patient_id = p.id
            ORDER BY d.date_diagnostic DESC";
    $diagnostics = fetchAll($sql);
} catch (Exception $e) {
    $diagnostics = [];
    $_SESSION['error'] = 'Erreur lors de la récupération des diagnostics: ' . ($DEBUG ? $e->getMessage() : 'Contactez l\'administrateur');
}

// Traitement du formulaire de création/modification de diagnostic
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $configId = isset($_POST['configuration_id']) ? (int)$_POST['configuration_id'] : 0;
    $nomProfesseur = isset($_POST['nom_professeur']) ? sanitizeInput($_POST['nom_professeur']) : '';
    $adresseConsultation = isset($_POST['adresse_consultation']) ? sanitizeInput($_POST['adresse_consultation']) : '';
    $compteRendu = isset($_POST['compte_rendu']) ? sanitizeInput($_POST['compte_rendu']) : '';
    $tempsRelachement = isset($_POST['temps_relachement']) ? (float)$_POST['temps_relachement'] : null;
    $ondeP = isset($_POST['onde_p']) ? (float)$_POST['onde_p'] : null;
    $ondeQ = isset($_POST['onde_q']) ? (float)$_POST['onde_q'] : null;
    $ondeR = isset($_POST['onde_r']) ? (float)$_POST['onde_r'] : null;
    $ondeS = isset($_POST['onde_s']) ? (float)$_POST['onde_s'] : null;
    $ondeT = isset($_POST['onde_t']) ? (float)$_POST['onde_t'] : null;
    
    // Validation des données
    if (empty($nomProfesseur) || empty($adresseConsultation) || empty($compteRendu) || $configId <= 0) {
        $_SESSION['error'] = 'Tous les champs marqués * sont obligatoires.';
    } else {
        try {
            // Préparation des données
            $diagnosticData = [
                'configuration_id' => $configId,
                'nom_professeur' => $nomProfesseur,
                'adresse_consultation' => $adresseConsultation,
                'compte_rendu' => $compteRendu,
                'temps_relachement_oreillettes' => $tempsRelachement,
                'onde_p' => $ondeP,
                'onde_q' => $ondeQ,
                'onde_r' => $ondeR,
                'onde_s' => $ondeS,
                'onde_t' => $ondeT
            ];
            
            if ($diagnosticId > 0) {
                // Mise à jour d'un diagnostic existant
                $updated = update('diagnostics', $diagnosticData, 'id', $diagnosticId);
                if ($updated) {
                    $_SESSION['success'] = 'Diagnostic mis à jour avec succès.';
                    redirect('/pages/diagnostic.php?id=' . $diagnosticId);
                } else {
                    $_SESSION['error'] = 'Erreur lors de la mise à jour du diagnostic.';
                }
            } else {
                // Création d'un nouveau diagnostic
                $newDiagnosticId = insert('diagnostics', $diagnosticData);
                if ($newDiagnosticId) {
                    $_SESSION['success'] = 'Nouveau diagnostic créé avec succès.';
                    redirect('/pages/diagnostic.php?id=' . $newDiagnosticId);
                } else {
                    $_SESSION['error'] = 'Erreur lors de la création du diagnostic.';
                }
            }
        } catch (Exception $e) {
            $_SESSION['error'] = 'Une erreur est survenue: ' . ($DEBUG ? $e->getMessage() : 'Contactez l\'administrateur');
        }
    }
}

// Récupération des configurations disponibles pour le formulaire de nouveau diagnostic
try {
    $sql = "SELECT c.id, c.temps_acquisition, p.nom_encoded, c.date_configuration
            FROM configurations c
            JOIN patients p ON c.patient_id = p.id
            LEFT JOIN diagnostics d ON c.id = d.configuration_id
            WHERE d.id IS NULL
            ORDER BY c.date_configuration DESC";
    $configurationsDisponibles = fetchAll($sql);
} catch (Exception $e) {
    $configurationsDisponibles = [];
}
?>

<div class="row mb-4">
    <div class="col-md-12">
        <h2><i class="fas fa-stethoscope me-2"></i>Diagnostic ECG</h2>
        <p class="lead">Visualisez et analysez les enregistrements ECG</p>
    </div>
</div>

<?php if ($diagnostic): ?>
<!-- Affichage d'un diagnostic spécifique -->
<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title">
            <i class="fas fa-file-medical me-2"></i>
            Diagnostic #<?php echo $diagnostic['id']; ?> - 
            <?php echo decodeBase64($patient['nom_encoded']); ?>
        </h3>
        <div>
            <button class="btn btn-outline-primary print-button" onclick="printPage()">
                <i class="fas fa-print me-2"></i>Imprimer
            </button>
        </div>
    </div>
    <div class="card-body">
        <!-- Informations patient -->
        <div class="row mb-4">
            <div class="col-md-6">
                <h4 class="mb-3">Information du patient</h4>
                <p><strong>Nom:</strong> <?php echo decodeBase64($patient['nom_encoded']); ?></p>
                <p><strong>Téléphone:</strong> <?php echo $patient['telephone']; ?></p>
                <p><strong>Adresse:</strong> <?php echo decodeBase64($patient['adresse_encoded']); ?></p>
            </div>
            <div class="col-md-6">
                <h4 class="mb-3">Détails de l'acquisition</h4>
                <p><strong>Groupe sanguin:</strong> <span class="badge bg-primary blood-type-badge"><?php echo $patient['groupe_sanguin']; ?></span></p>
                <p><strong>Temps d'acquisition:</strong> <?php echo $configuration['temps_acquisition']; ?> secondes</p>
                <p><strong>Date d'acquisition:</strong> <?php echo formatDate($configuration['date_configuration']); ?></p>
            </div>
        </div>
        
        <!-- Visualisation ECG -->
        <div class="ecg-visualization">
            <h3>Électrocardiogramme</h3>
            <div id="loading-indicator" class="text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Chargement...</span>
                </div>
                <p class="mt-2">Chargement des données ECG...</p>
            </div>
            <div id="ecg-chart-container" style="display: none;">
                <canvas id="ecg-chart" class="ecg-chart-container"></canvas>
                
                <div class="row mt-4">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5>Temps de relâchement des oreillettes</h5>
                            </div>
                            <div class="card-body">
                                <h3 id="atrial-release-time" class="text-center">
                                    <?php echo $diagnostic['temps_relachement_oreillettes'] ? $diagnostic['temps_relachement_oreillettes'] . ' ms' : 'N/A'; ?>
                                </h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5>Niveaux des ondes</h5>
                            </div>
                            <div class="card-body">
                                <div class="waves-info-container">
                                    <div class="wave-info-card p-wave">
                                        <h5>Onde P</h5>
                                        <div id="p-wave-value" class="value">
                                            <?php echo $diagnostic['onde_p'] ? number_format($diagnostic['onde_p'], 2) . ' mV' : 'N/A'; ?>
                                        </div>
                                    </div>
                                    <div class="wave-info-card q-wave">
                                        <h5>Onde Q</h5>
                                        <div id="q-wave-value" class="value">
                                            <?php echo $diagnostic['onde_q'] ? number_format($diagnostic['onde_q'], 2) . ' mV' : 'N/A'; ?>
                                        </div>
                                    </div>
                                    <div class="wave-info-card r-wave">
                                        <h5>Onde R</h5>
                                        <div id="r-wave-value" class="value">
                                            <?php echo $diagnostic['onde_r'] ? number_format($diagnostic['onde_r'], 2) . ' mV' : 'N/A'; ?>
                                        </div>
                                    </div>
                                    <div class="wave-info-card s-wave">
                                        <h5>Onde S</h5>
                                        <div id="s-wave-value" class="value">
                                            <?php echo $diagnostic['onde_s'] ? number_format($diagnostic['onde_s'], 2) . ' mV' : 'N/A'; ?>
                                        </div>
                                    </div>
                                    <div class="wave-info-card t-wave">
                                        <h5>Onde T</h5>
                                        <div id="t-wave-value" class="value">
                                            <?php echo $diagnostic['onde_t'] ? number_format($diagnostic['onde_t'], 2) . ' mV' : 'N/A'; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Compte rendu médical -->
        <div class="medical-report mt-4">
            <h4>Compte rendu médical</h4>
            <div class="doctor-info mb-3">
                <div>
                    <strong>Professeur:</strong> <?php echo $diagnostic['nom_professeur']; ?>
                </div>
                <div>
                    <strong>Date:</strong> <?php echo formatDate($diagnostic['date_diagnostic'], true); ?>
                </div>
            </div>
            <div>
                <strong>Adresse de la consultation:</strong>
                <p><?php echo $diagnostic['adresse_consultation']; ?></p>
            </div>
            <div class="mt-3">
                <strong>Compte rendu:</strong>
                <div class="p-3 bg-light rounded mt-2">
                    <?php echo nl2br($diagnostic['compte_rendu']); ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Initialisation de la visualisation ECG lorsque la page est chargée
document.addEventListener('DOMContentLoaded', function() {
    initEcgVisualization(<?php echo $diagnostic['id']; ?>);
});
</script>

<?php else: ?>
<!-- Liste des diagnostics ou formulaire de création -->
<div class="row">
    <div class="col-md-7">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Diagnostics récents</h3>
            </div>
            <div class="card-body">
                <?php if (empty($diagnostics)): ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        Aucun diagnostic n'a encore été enregistré.
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Patient</th>
                                    <th>Médecin</th>
                                    <th>Date</th>
                                    <th>Groupe sanguin</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($diagnostics as $d): ?>
                                <tr>
                                    <td><?php echo $d['id']; ?></td>
                                    <td><?php echo decodeBase64($d['nom_encoded']); ?></td>
                                    <td><?php echo $d['nom_professeur']; ?></td>
                                    <td><?php echo formatDate($d['date_diagnostic']); ?></td>
                                    <td><span class="badge bg-primary"><?php echo $d['groupe_sanguin']; ?></span></td>
                                    <td>
                                        <a href="?id=<?php echo $d['id']; ?>" class="btn btn-sm btn-primary">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="col-md-5">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Nouveau diagnostic</h3>
            </div>
            <div class="card-body">
                <?php if (empty($configurationsDisponibles)): ?>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Aucune configuration disponible pour créer un nouveau diagnostic.
                        <div class="mt-2">
                            <a href="/pages/configuration.php" class="btn btn-sm btn-primary">
                                <i class="fas fa-plus me-1"></i>Nouvelle configuration
                            </a>
                        </div>
                    </div>
                <?php else: ?>
                    <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST">
                        <div class="mb-3">
                            <label for="configuration_id" class="form-label">Configuration / Patient *</label>
                            <select class="form-select" id="configuration_id" name="configuration_id" required>
                                <option value="" selected disabled>Sélectionnez une configuration...</option>
                                <?php foreach ($configurationsDisponibles as $config): ?>
                                <option value="<?php echo $config['id']; ?>">
                                    Config #<?php echo $config['id']; ?> - 
                                    <?php echo decodeBase64($config['nom_encoded']); ?> - 
                                    <?php echo formatDate($config['date_configuration']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="nom_professeur" class="form-label">Nom du professeur *</label>
                            <input type="text" class="form-control" id="nom_professeur" name="nom_professeur" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="adresse_consultation" class="form-label">Adresse de la consultation *</label>
                            <textarea class="form-control" id="adresse_consultation" name="adresse_consultation" rows="2" required></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label for="compte_rendu" class="form-label">Compte rendu médical *</label>
                            <textarea class="form-control" id="compte_rendu" name="compte_rendu" rows="4" required></textarea>
                        </div>
                        
                        <h5 class="mt-4 mb-3">Paramètres avancés</h5>
                        
                        <div class="mb-3">
                            <label for="temps_relachement" class="form-label">Temps de relâchement des oreillettes (ms)</label>
                            <input type="number" step="0.01" class="form-control" id="temps_relachement" name="temps_relachement">
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="onde_p" class="form-label">Onde P (mV)</label>
                                <input type="number" step="0.01" class="form-control" id="onde_p" name="onde_p">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="onde_q" class="form-label">Onde Q (mV)</label>
                                <input type="number" step="0.01" class="form-control" id="onde_q" name="onde_q">
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="onde_r" class="form-label">Onde R (mV)</label>
                                <input type="number" step="0.01" class="form-control" id="onde_r" name="onde_r">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="onde_s" class="form-label">Onde S (mV)</label>
                                <input type="number" step="0.01" class="form-control" id="onde_s" name="onde_s">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="onde_t" class="form-label">Onde T (mV)</label>
                                <input type="number" step="0.01" class="form-control" id="onde_t" name="onde_t">
                            </div>
                        </div>
                        
                        <div class="mt-4">
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-save me-2"></i>Enregistrer le diagnostic
                            </button>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<?php include_once '../../includes/footer.php'; ?> 