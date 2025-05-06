    </div> <!-- Fermeture du .container -->

    <!-- Footer -->
    <footer class="footer bg-light mt-5 py-3">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <span class="text-muted">Système de Monitoring ECG - Raspberry Pi</span>
                    <a href="/pages/index.php" class="ms-3 text-primary">
                        <i class="fas fa-home"></i> Accueil
                    </a>
                </div>
                <div class="col-md-6 text-end">
                    <span class="text-muted">&copy; <?php echo date('Y'); ?> ECG Monitoring</span>
                </div>
            </div>
        </div>
    </footer>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Chart.js pour les graphiques -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <!-- JS personnalisé commun -->
    <script src="/js/main.js"></script>
    
    <?php if (isset($extraJs)): ?>
        <!-- JS supplémentaire spécifique à la page -->
        <script src="<?php echo $extraJs; ?>"></script>
    <?php endif; ?>
    
    <?php if (isset($inlineJs)): ?>
        <!-- JS inline spécifique à la page -->
        <script>
            <?php echo $inlineJs; ?>
        </script>
    <?php endif; ?>
</body>
</html> 