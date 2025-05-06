<?php
// Page d'accueil du système de monitoring ECG
$pageTitle = "Accueil";
$extraCss = "/css/pages/index.css";
// La session est déjà démarrée par auth.php
include_once '../../includes/header.php';
?>

<div class="welcome-section">
    <h1 class="welcome-title">
        <i class="fas fa-heartbeat text-danger"></i> Système de Monitoring ECG
    </h1>
    <p class="welcome-subtitle">Plateforme de suivi et d'analyse d'électrocardiogrammes avec Raspberry Pi et capteur AD8232</p>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <div class="card feature-card">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-cog icon-spacing"></i>Configuration</h3>
        </div>
        <div class="card-body">
            <p>Configurez une nouvelle session d'enregistrement ECG avec les informations du patient:</p>
            <ul class="mb-4">
                <li>Données personnelles sécurisées et cryptées</li>
                <li>Configuration de la durée d'acquisition</li>
                <li>Paramétrage du système</li>
            </ul>
            <div class="text-center">
                <a href="/pages/configuration.php" class="btn btn-primary btn-lg">
                    <i class="fas fa-sliders-h"></i>Configurer
                </a>
            </div>
        </div>
    </div>
    
    <div class="card feature-card">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-stethoscope icon-spacing"></i>Diagnostic</h3>
        </div>
        <div class="card-body">
            <p>Visualisez et analysez les données ECG enregistrées:</p>
            <ul class="mb-4">
                <li>Affichage des signaux ECG avec marquage des ondes P, Q, R, S, T</li>
                <li>Analyses et diagnostics médicaux</li>
                <li>Impression des résultats</li>
            </ul>
            <div class="text-center">
                <a href="/pages/diagnostic.php" class="btn btn-success btn-lg">
                    <i class="fas fa-chart-line"></i>Diagnostiquer
                </a>
            </div>
        </div>
    </div>
</div>

<div class="mt-5">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-info-circle icon-spacing"></i>À propos du système</h3>
        </div>
        <div class="card-body">
            <p>Ce système a été développé pour capturer, analyser et visualiser les signaux cardiaques à l'aide d'un capteur AD8232 connecté à un Raspberry Pi.</p>
            
            <h4 class="mt-4 mb-3">Composants du système</h4>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <ul>
                        <li><strong>Hardware</strong>: Raspberry Pi 3 B+, Capteur ECG AD8232</li>
                        <li><strong>Backend</strong>: PHP, MySQL</li>
                        <li><strong>Frontend</strong>: HTML, CSS, JavaScript, Chart.js</li>
                    </ul>
                </div>
                <div>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle icon-spacing"></i>
                        <strong>Important:</strong> Ce système n'est pas certifié pour un usage médical professionnel.
                        Il est destiné à des fins éducatives et expérimentales.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once '../../includes/footer.php'; ?> 