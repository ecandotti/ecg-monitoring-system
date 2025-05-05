/**
 * Script pour la visualisation avancée des signaux ECG
 * Inclut la détection et l'annotation des ondes PQRST
 */

/**
 * Fonction pour créer un graphique ECG avancé avec annotation des ondes
 * 
 * @param {string} canvasId - L'identifiant du canvas
 * @param {Array} ecgData - Les données ECG brutes
 * @param {Array} timeData - Les données temporelles (axe X)
 * @param {Object} wavePositions - Les positions des différentes ondes
 * @returns {Chart} Objet Chart.js
 */
function createAdvancedEcgChart(canvasId, ecgData, timeData, wavePositions) {
    const ctx = document.getElementById(canvasId).getContext('2d');
    
    // Configuration des points d'annotation
    const annotations = {
        p: {
            type: 'point',
            xValue: wavePositions.p ? timeData[wavePositions.p] : null,
            yValue: wavePositions.p ? ecgData[wavePositions.p] : null,
            backgroundColor: 'rgba(40, 167, 69, 0.7)',
            borderColor: '#28a745',
            borderWidth: 2,
            radius: 5,
            label: {
                content: 'P',
                enabled: true,
                position: 'top'
            }
        },
        q: {
            type: 'point',
            xValue: wavePositions.q ? timeData[wavePositions.q] : null,
            yValue: wavePositions.q ? ecgData[wavePositions.q] : null,
            backgroundColor: 'rgba(220, 53, 69, 0.7)',
            borderColor: '#dc3545',
            borderWidth: 2,
            radius: 5,
            label: {
                content: 'Q',
                enabled: true,
                position: 'bottom'
            }
        },
        r: {
            type: 'point',
            xValue: wavePositions.r ? timeData[wavePositions.r] : null,
            yValue: wavePositions.r ? ecgData[wavePositions.r] : null,
            backgroundColor: 'rgba(255, 193, 7, 0.7)',
            borderColor: '#ffc107',
            borderWidth: 2,
            radius: 6,
            label: {
                content: 'R',
                enabled: true,
                position: 'top'
            }
        },
        s: {
            type: 'point',
            xValue: wavePositions.s ? timeData[wavePositions.s] : null,
            yValue: wavePositions.s ? ecgData[wavePositions.s] : null,
            backgroundColor: 'rgba(23, 162, 184, 0.7)',
            borderColor: '#17a2b8',
            borderWidth: 2,
            radius: 5,
            label: {
                content: 'S',
                enabled: true,
                position: 'bottom'
            }
        },
        t: {
            type: 'point',
            xValue: wavePositions.t ? timeData[wavePositions.t] : null,
            yValue: wavePositions.t ? ecgData[wavePositions.t] : null,
            backgroundColor: 'rgba(111, 66, 193, 0.7)',
            borderColor: '#6f42c1',
            borderWidth: 2,
            radius: 5,
            label: {
                content: 'T',
                enabled: true,
                position: 'top'
            }
        }
    };
    
    // Filtrage des annotations valides uniquement
    const activeAnnotations = Object.values(annotations).filter(ann => ann.xValue !== null);
    
    // Création du graphique
    return new Chart(ctx, {
        type: 'line',
        data: {
            labels: timeData,
            datasets: [{
                label: 'Signal ECG',
                data: ecgData,
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
            },
            plugins: {
                annotation: {
                    annotations: activeAnnotations
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return `Amplitude: ${context.parsed.y.toFixed(2)} mV`;
                        },
                        title: function(context) {
                            return `Temps: ${context[0].parsed.x.toFixed(2)} s`;
                        }
                    }
                }
            }
        }
    });
}

/**
 * Fonction pour mettre à jour les valeurs des ondes affichées
 * 
 * @param {Object} waveValues - Valeurs des ondes {p, q, r, s, t}
 */
function updateWaveValues(waveValues) {
    // Mettre à jour les éléments HTML avec les valeurs des ondes
    if (waveValues.p !== undefined && document.getElementById('p-wave-value')) {
        document.getElementById('p-wave-value').textContent = waveValues.p.toFixed(2) + ' mV';
    }
    
    if (waveValues.q !== undefined && document.getElementById('q-wave-value')) {
        document.getElementById('q-wave-value').textContent = waveValues.q.toFixed(2) + ' mV';
    }
    
    if (waveValues.r !== undefined && document.getElementById('r-wave-value')) {
        document.getElementById('r-wave-value').textContent = waveValues.r.toFixed(2) + ' mV';
    }
    
    if (waveValues.s !== undefined && document.getElementById('s-wave-value')) {
        document.getElementById('s-wave-value').textContent = waveValues.s.toFixed(2) + ' mV';
    }
    
    if (waveValues.t !== undefined && document.getElementById('t-wave-value')) {
        document.getElementById('t-wave-value').textContent = waveValues.t.toFixed(2) + ' mV';
    }
}

/**
 * Fonction pour charger les données ECG depuis l'API
 * 
 * @param {number} configId - ID de la configuration
 * @returns {Promise} Promesse contenant les données
 */
async function loadEcgData(configId) {
    try {
        const response = await fetch(`/api/get_ecg_data.php?config_id=${configId}`);
        if (!response.ok) {
            throw new Error('Erreur lors du chargement des données');
        }
        return await response.json();
    } catch (error) {
        console.error('Erreur:', error);
        return null;
    }
}

/**
 * Initialise la visualisation ECG pour un ID de diagnostic
 * 
 * @param {number} diagnosticId - ID du diagnostic
 */
async function initEcgVisualization(diagnosticId) {
    // Récupération des éléments du DOM
    const loadingIndicator = document.getElementById('loading-indicator');
    const ecgContainer = document.getElementById('ecg-chart-container');
    
    if (loadingIndicator) loadingIndicator.style.display = 'block';
    
    try {
        // Récupération des données du diagnostic et des données ECG associées
        const diagnosticResponse = await fetch(`/api/get_diagnostic.php?id=${diagnosticId}`);
        if (!diagnosticResponse.ok) throw new Error('Erreur de chargement du diagnostic');
        
        const diagnostic = await diagnosticResponse.json();
        const ecgData = await loadEcgData(diagnostic.configuration_id);
        
        if (!ecgData) throw new Error('Données ECG non disponibles');
        
        // Préparation des données pour le graphique
        const timeData = ecgData.samples.map(sample => sample.timestamp);
        const amplitudeData = ecgData.samples.map(sample => sample.valeur);
        
        // Positions des ondes (indices dans le tableau de données)
        const wavePositions = {
            p: ecgData.wave_positions?.p_index || null,
            q: ecgData.wave_positions?.q_index || null,
            r: ecgData.wave_positions?.r_index || null,
            s: ecgData.wave_positions?.s_index || null,
            t: ecgData.wave_positions?.t_index || null
        };
        
        // Valeurs des ondes (amplitudes)
        const waveValues = {
            p: diagnostic.onde_p,
            q: diagnostic.onde_q,
            r: diagnostic.onde_r,
            s: diagnostic.onde_s,
            t: diagnostic.onde_t
        };
        
        // Création du graphique
        if (loadingIndicator) loadingIndicator.style.display = 'none';
        if (ecgContainer) ecgContainer.style.display = 'block';
        
        const chart = createAdvancedEcgChart('ecg-chart', amplitudeData, timeData, wavePositions);
        updateWaveValues(waveValues);
        
        // Mise à jour des informations de relâchement des oreillettes si disponible
        if (diagnostic.temps_relachement_oreillettes && document.getElementById('atrial-release-time')) {
            document.getElementById('atrial-release-time').textContent = 
                diagnostic.temps_relachement_oreillettes.toFixed(2) + ' ms';
        }
        
    } catch (error) {
        console.error('Erreur d\'initialisation:', error);
        if (loadingIndicator) loadingIndicator.style.display = 'none';
        if (ecgContainer) {
            ecgContainer.innerHTML = `<div class="alert alert-danger">Erreur de chargement des données ECG: ${error.message}</div>`;
            ecgContainer.style.display = 'block';
        }
    }
} 