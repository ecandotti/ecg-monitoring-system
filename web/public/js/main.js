/**
 * Script JavaScript principal
 * Fonctions communes utilisées dans l'application
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialiser les tooltips Bootstrap
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Initialiser les popovers Bootstrap
    var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    var popoverList = popoverTriggerList.map(function(popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });
    
    // Fermeture automatique des alertes
    document.querySelectorAll('.alert').forEach(function(alert) {
        setTimeout(function() {
            var bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }, 5000); // Les alertes se ferment après 5 secondes
    });
    
    // Gestion des formulaires avec validation
    const forms = document.querySelectorAll('.needs-validation');
    
    Array.prototype.slice.call(forms).forEach(function(form) {
        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            
            form.classList.add('was-validated');
        }, false);
    });
});

/**
 * Fonction pour formater une date
 * @param {Date} date - L'objet Date à formater
 * @param {boolean} withTime - Inclure l'heure ou non
 * @returns {string} Date formatée
 */
function formatDate(date, withTime = false) {
    if (!(date instanceof Date)) {
        date = new Date(date);
    }
    
    const day = String(date.getDate()).padStart(2, '0');
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const year = date.getFullYear();
    
    let formatted = `${day}/${month}/${year}`;
    
    if (withTime) {
        const hours = String(date.getHours()).padStart(2, '0');
        const minutes = String(date.getMinutes()).padStart(2, '0');
        formatted += ` ${hours}:${minutes}`;
    }
    
    return formatted;
}

/**
 * Fonction d'impression de la page actuelle
 */
function printPage() {
    window.print();
}

/**
 * Fonction pour créer un grahpique ECG de base
 * @param {string} canvasId - L'ID du canvas
 * @param {Array} data - Données ECG
 * @param {Array} labels - Labels pour l'axe X
 */
function createEcgChart(canvasId, data, labels) {
    const ctx = document.getElementById(canvasId).getContext('2d');
    
    return new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Signal ECG',
                data: data,
                borderColor: 'rgb(13, 110, 253)',
                borderWidth: 1.5,
                tension: 0.1,
                pointRadius: 0,
                fill: false
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                intersect: false,
                mode: 'index'
            },
            scales: {
                x: {
                    title: {
                        display: true,
                        text: 'Temps (s)'
                    }
                },
                y: {
                    title: {
                        display: true,
                        text: 'Amplitude (mV)'
                    }
                }
            }
        }
    });
}

/**
 * Fonction pour confirmer une action
 * @param {string} message - Message de confirmation
 * @returns {boolean} - True si confirmé, false sinon
 */
function confirmAction(message) {
    return confirm(message);
} 