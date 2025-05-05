-- Création de la base de données
CREATE DATABASE IF NOT EXISTS `ecg_database`;
USE `ecg_database`;

-- Table des patients
CREATE TABLE IF NOT EXISTS `patients` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `nom_hash` VARCHAR(255) NOT NULL COMMENT 'Nom hashé',
  `nom_encoded` VARCHAR(255) NOT NULL COMMENT 'Nom encodé en base64',
  `numero_secu_hash` VARCHAR(255) NOT NULL COMMENT 'Numéro de sécurité sociale hashé',
  `numero_secu_encoded` VARCHAR(255) NOT NULL COMMENT 'Numéro de sécurité sociale encodé en base64',
  `telephone` VARCHAR(20) NOT NULL,
  `adresse_encoded` TEXT NOT NULL COMMENT 'Adresse encodée en base64',
  `groupe_sanguin` VARCHAR(10) NOT NULL,
  `date_creation` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `date_modification` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table des configurations d'acquisition
CREATE TABLE IF NOT EXISTS `configurations` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `patient_id` INT NOT NULL,
  `temps_acquisition` INT NOT NULL COMMENT 'Temps d''acquisition en secondes',
  `date_configuration` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`patient_id`) REFERENCES `patients`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table des données ECG
CREATE TABLE IF NOT EXISTS `ecg_data` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `configuration_id` INT NOT NULL,
  `timestamp` TIMESTAMP NOT NULL COMMENT 'Horodatage de l''échantillon',
  `valeur` FLOAT NOT NULL COMMENT 'Valeur du signal ECG',
  FOREIGN KEY (`configuration_id`) REFERENCES `configurations`(`id`) ON DELETE CASCADE,
  INDEX `idx_timestamp` (`timestamp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table des diagnostics
CREATE TABLE IF NOT EXISTS `diagnostics` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `configuration_id` INT NOT NULL,
  `nom_professeur` VARCHAR(255) NOT NULL,
  `adresse_consultation` TEXT NOT NULL,
  `compte_rendu` TEXT NOT NULL,
  `temps_relachement_oreillettes` FLOAT DEFAULT NULL COMMENT 'Temps de relâchement des oreillettes en ms',
  `onde_p` FLOAT DEFAULT NULL COMMENT 'Niveau de l''onde P',
  `onde_q` FLOAT DEFAULT NULL COMMENT 'Niveau de l''onde Q',
  `onde_r` FLOAT DEFAULT NULL COMMENT 'Niveau de l''onde R',
  `onde_s` FLOAT DEFAULT NULL COMMENT 'Niveau de l''onde S',
  `onde_t` FLOAT DEFAULT NULL COMMENT 'Niveau de l''onde T',
  `date_diagnostic` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`configuration_id`) REFERENCES `configurations`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table des utilisateurs (pour l'authentification)
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `role` ENUM('admin', 'medecin', 'patient') NOT NULL DEFAULT 'patient',
  `date_creation` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `derniere_connexion` TIMESTAMP NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insertion d'un utilisateur admin par défaut (mot de passe: admin)
INSERT INTO `users` (`username`, `password`, `role`) VALUES
('admin', '$2y$10$9E4WKBuaGYmGJrmI/hXjreQ5WqojVx0.1.olR28/sm.px0S4HUh3K', 'admin'); 