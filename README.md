# Système de Monitoring ECG avec Raspberry Pi et AD8232

Ce projet implémente un système complet de surveillance cardiaque (ECG) utilisant un capteur AD8232 connecté à un Raspberry Pi, avec des serveurs web pour la configuration, l'acquisition, la visualisation et le diagnostic.

## 🎯 Objectif

Développer un système de surveillance cardiaque capable de :
- Capter les signaux cardiaques via un capteur AD8232
- Traiter et afficher ces signaux en temps réel
- Enregistrer les données pour analyse ultérieure
- Fournir une interface de diagnostic pour les professionnels

## 🧩 Composants

- **Hardware** :
  - Capteur ECG AD8232
  - Raspberry Pi 3 B+
  - Câbles et électrodes
  
- **Software** :
  - Serveur Web PHP
  - Base de données MySQL
  - Interface utilisateur HTML/CSS/JavaScript
  - Librairie Chart.js pour la visualisation

## 🔒 Sécurité des données

Toutes les données sensibles sont protégées :
- Hashage avec la clé de sécurité "test2025"
- Encodage en base64 des informations personnelles
- Communication sécurisée entre les composants

## 📦 Structure du projet

```
/ecg-monitoring-system
├── docker-compose.yml       # Orchestration des conteneurs
├── Dockerfile               # Configuration du conteneur web
├── .env                     # Variables d'environnement
├── database/
│   └── init.sql             # Initialisation de la base de données
├── web/
│   ├── config/              # Fichiers de configuration
│   ├── public/              # Fichiers accessibles publiquement
│   ├── includes/            # Composants réutilisables
│   └── api/                 # Points d'entrée API
└── README.md                # Documentation
```

## ⚙️ Installation

### Prérequis

- Docker et Docker Compose
- Raspberry Pi avec Raspbian/Raspberry Pi OS
- Capteur AD8232 configuré

### Installation avec Docker

1. Clonez ce dépôt sur votre Raspberry Pi :
   ```bash
   git clone https://github.com/votre-utilisateur/ecg-monitoring-system.git
   cd ecg-monitoring-system
   ```

2. Créez un fichier `.env` en copiant le fichier `.env.example` :
   ```bash
   cp .env.example .env
   ```

3. Modifiez les valeurs dans le fichier `.env` selon votre configuration

4. Lancez les conteneurs Docker :
   ```bash
   docker-compose up -d
   ```

5. L'application est accessible à l'adresse : `http://localhost:80`
   L'interface d'administration de la base de données (PHPMyAdmin) est disponible à : `http://localhost:8080`

## 🖥️ Fonctionnalités

### 1. Configuration du patient et de l'acquisition

- Enregistrement des données du patient (cryptées)
- Configuration du temps d'acquisition
- Attribution d'un ID de session unique

### 2. Acquisition des signaux ECG

- Captation des signaux via l'AD8232
- Transmission au serveur web pour stockage
- Prétraitement du signal

### 3. Visualisation des données

- Graphique interactif du signal ECG
- Identification des ondes P, Q, R, S, T
- Mesure des intervalles et amplitudes

### 4. Diagnostic médical

- Interface pour les professionnels de santé
- Saisie du compte rendu médical
- Impression des résultats avec les analyses

## 🚀 Utilisation

1. Accédez à l'interface web via un navigateur
2. Créez une nouvelle configuration en saisissant les informations du patient
3. Démarrez l'acquisition des données ECG
4. Visualisez les résultats en temps réel
5. Créez un diagnostic une fois l'acquisition terminée

## 🔌 API

L'application expose plusieurs endpoints API :

- `GET /api/get_ecg_data.php?config_id=X` : Récupère les données ECG pour une configuration
- `GET /api/get_diagnostic.php?id=X` : Récupère les informations d'un diagnostic
- `POST /api/save_config.php` : Enregistre une nouvelle configuration
- `POST /api/save_ecg.php` : Enregistre des données ECG

## 🔧 Développement

Pour étendre ou modifier le projet :

1. Arrêtez les conteneurs : `docker-compose down`
2. Modifiez le code source
3. Reconstruisez les conteneurs : `docker-compose up -d --build`

## ⚠️ Limitations

- Ce système n'est PAS certifié pour un usage médical professionnel
- Il est conçu à des fins éducatives et expérimentales
- La précision et la fiabilité des mesures dépendent de la qualité du capteur et du positionnement des électrodes

## 👨‍💻 Auteur

Ce projet a été développé dans le cadre d'un projet étudiant (IR1).

## 📄 Licence

Ce projet est distribué sous licence MIT. 