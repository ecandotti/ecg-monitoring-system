<?php
// Page d'accueil du système de monitoring ECG
$pageTitle = "Accueil";
session_start();
include_once '../includes/header.php';
?>

<div class="row mt-5">
    <div class="col-md-12 text-center">
        <h1 class="display-4">
            <i class="fas fa-heartbeat text-danger"></i> Système de Monitoring ECG
        </h1>
        <p class="lead">Plateforme de suivi et d'analyse d'électrocardiogrammes avec Raspberry Pi et capteur AD8232</p>
    </div>
</div>

<div class="row mt-5">
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-cog icon-spacing"></i>Configuration</h3>
            </div>
            <div class="card-body">
                <p>Configurez une nouvelle session d'enregistrement ECG avec les informations du patient:</p>
                <ul>
                    <li>Données personnelles sécurisées et cryptées</li>
                    <li>Configuration de la durée d'acquisition</li>
                    <li>Paramétrage du système</li>
                </ul>
                <div class="text-center mt-4">
                    <a href="/pages/configuration.php" class="btn btn-primary btn-lg">
                        <i class="fas fa-sliders-h me-2"></i>Configurer
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-stethoscope icon-spacing"></i>Diagnostic</h3>
            </div>
            <div class="card-body">
                <p>Visualisez et analysez les données ECG enregistrées:</p>
                <ul>
                    <li>Affichage des signaux ECG avec marquage des ondes P, Q, R, S, T</li>
                    <li>Analyses et diagnostics médicaux</li>
                    <li>Impression des résultats</li>
                </ul>
                <div class="text-center mt-4">
                    <a href="/pages/diagnostic.php" class="btn btn-success btn-lg">
                        <i class="fas fa-chart-line me-2"></i>Diagnostiquer
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mt-5">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-info-circle icon-spacing"></i>À propos du système</h3>
            </div>
            <div class="card-body">
                <p>Ce système a été développé pour capturer, analyser et visualiser les signaux cardiaques à l'aide d'un capteur AD8232 connecté à un Raspberry Pi.</p>
                
                <h4 class="mt-4">Composants du système</h4>
                <div class="row">
                    <div class="col-md-6">
                        <ul>
                            <li><strong>Hardware</strong>: Raspberry Pi 3 B+, Capteur ECG AD8232</li>
                            <li><strong>Backend</strong>: PHP, MySQL</li>
                            <li><strong>Frontend</strong>: HTML, CSS, JavaScript, Chart.js</li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>Important:</strong> Ce système n'est pas certifié pour un usage médical professionnel.
                            Il est destiné à des fins éducatives et expérimentales.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once '../includes/footer.php'; ?> 