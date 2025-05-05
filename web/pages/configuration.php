<?php
// Page de configuration du système de monitoring ECG
$pageTitle = "Configuration";
include_once '../includes/header.php';

// Vérifier si le formulaire a été soumis
$formSubmitted = false;
$formSuccess = false;
$formError = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formSubmitted = true;
    
    // Récupération des données du formulaire
    $nom = isset($_POST['nom']) ? trim($_POST['nom']) : '';
    $numeroSecu = isset($_POST['numero_secu']) ? trim($_POST['numero_secu']) : '';
    $telephone = isset($_POST['telephone']) ? trim($_POST['telephone']) : '';
    $adresse = isset($_POST['adresse']) ? trim($_POST['adresse']) : '';
    $groupeSanguin = isset($_POST['groupe_sanguin']) ? trim($_POST['groupe_sanguin']) : '';
    $tempsAcquisition = isset($_POST['temps_acquisition']) ? (int)$_POST['temps_acquisition'] : 0;
    
    // Validation des données
    if (empty($nom) || empty($numeroSecu) || empty($telephone) || empty($adresse) || empty($groupeSanguin) || $tempsAcquisition <= 0) {
        $formError = 'Tous les champs sont obligatoires et le temps d\'acquisition doit être supérieur à 0.';
    } else {
        try {
            // Hashage et encodage des données sensibles
            $nomHash = hashData($nom);
            $nomEncoded = encodeBase64($nom);
            $numeroSecuHash = hashData($numeroSecu);
            $numeroSecuEncoded = encodeBase64($numeroSecu);
            $adresseEncoded = encodeBase64($adresse);
            
            // Connexion à la base de données
            $db = getDbConnection();
            
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
                    $formSuccess = true;
                    $_SESSION['success'] = 'Configuration enregistrée avec succès. ID de configuration: ' . $configId;
                    $_SESSION['config_id'] = $configId;
                    $_SESSION['patient_id'] = $patientId;
                } else {
                    $formError = 'Erreur lors de l\'enregistrement de la configuration.';
                }
            } else {
                $formError = 'Erreur lors de l\'enregistrement des informations du patient.';
            }
        } catch (Exception $e) {
            $formError = 'Une erreur est survenue: ' . ($DEBUG ? $e->getMessage() : 'Contactez l\'administrateur');
        }
    }
}
?>

<div class="row mb-4">
    <div class="col-md-12">
        <h2><i class="fas fa-cog me-2"></i>Configuration du système ECG</h2>
        <p class="lead">Entrez les informations du patient et configurez les paramètres d'acquisition</p>
    </div>
</div>

<?php if ($formSubmitted && $formSuccess): ?>
    <div class="alert alert-success">
        <i class="fas fa-check-circle me-2"></i>
        <strong>Succès!</strong> La configuration a été enregistrée avec succès.
        <div class="mt-3">
            <a href="/pages/diagnostic.php" class="btn btn-primary">
                <i class="fas fa-stethoscope me-2"></i>Aller au diagnostic
            </a>
        </div>
    </div>
<?php elseif ($formSubmitted && !empty($formError)): ?>
    <div class="alert alert-danger">
        <i class="fas fa-exclamation-circle me-2"></i>
        <strong>Erreur!</strong> <?php echo $formError; ?>
    </div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Informations du patient et paramètres</h3>
    </div>
    <div class="card-body">
        <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST" class="needs-validation" novalidate>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="nom" class="form-label">Nom complet</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-user"></i></span>
                        <input type="text" class="form-control" id="nom" name="nom" required>
                        <div class="invalid-feedback">Veuillez entrer le nom du patient.</div>
                    </div>
                    <small class="text-muted">Cette information sera cryptée</small>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="numero_secu" class="form-label">Numéro de sécurité sociale</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-id-card"></i></span>
                        <input type="text" class="form-control" id="numero_secu" name="numero_secu" pattern="[0-9]{15}" required>
                        <div class="invalid-feedback">Veuillez entrer un numéro de sécurité sociale valide (15 chiffres).</div>
                    </div>
                    <small class="text-muted">Cette information sera cryptée</small>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="telephone" class="form-label">Numéro de téléphone</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-phone"></i></span>
                        <input type="tel" class="form-control" id="telephone" name="telephone" required>
                        <div class="invalid-feedback">Veuillez entrer un numéro de téléphone.</div>
                    </div>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="groupe_sanguin" class="form-label">Groupe sanguin</label>
                    <select class="form-select" id="groupe_sanguin" name="groupe_sanguin" required>
                        <option value="" selected disabled>Sélectionnez...</option>
                        <option value="A+">A+</option>
                        <option value="A-">A-</option>
                        <option value="B+">B+</option>
                        <option value="B-">B-</option>
                        <option value="AB+">AB+</option>
                        <option value="AB-">AB-</option>
                        <option value="O+">O+</option>
                        <option value="O-">O-</option>
                    </select>
                    <div class="invalid-feedback">Veuillez sélectionner un groupe sanguin.</div>
                </div>
            </div>
            
            <div class="mb-3">
                <label for="adresse" class="form-label">Adresse</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-home"></i></span>
                    <textarea class="form-control" id="adresse" name="adresse" rows="3" required></textarea>
                    <div class="invalid-feedback">Veuillez entrer une adresse.</div>
                </div>
                <small class="text-muted">Cette information sera cryptée</small>
            </div>
            
            <hr class="my-4">
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="temps_acquisition" class="form-label">Temps d'acquisition (secondes)</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-clock"></i></span>
                        <input type="number" class="form-control" id="temps_acquisition" name="temps_acquisition" min="1" value="60" required>
                        <div class="invalid-feedback">Veuillez entrer un temps d'acquisition valide (minimum 1 seconde).</div>
                    </div>
                </div>
            </div>
            
            <div class="mt-4">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-2"></i>Enregistrer la configuration
                </button>
                <a href="/" class="btn btn-secondary ms-2">
                    <i class="fas fa-times me-2"></i>Annuler
                </a>
            </div>
        </form>
    </div>
</div>

<script>
// Script de validation du formulaire côté client
(function() {
    'use strict';
    
    // Récupérer tous les formulaires avec la classe .needs-validation
    var forms = document.querySelectorAll('.needs-validation');
    
    // Boucle pour empêcher la soumission
    Array.prototype.slice.call(forms).forEach(function(form) {
        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            
            form.classList.add('was-validated');
        }, false);
    });
})();
</script>

<?php include_once '../includes/footer.php'; ?> 