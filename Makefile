# Makefile pour l'administration du système de monitoring ECG
# Auteur: Projet étudiant IR1

# Variables
DOCKER_COMPOSE = docker-compose
DOCKER = docker
ENV_FILE = .env
ENV_EXAMPLE = .env.example

# Commandes principales
.PHONY: up down restart build logs clean setup backup restore shell help

# Aide/documentation
help:
	@echo "Makefile pour l'administration du système de monitoring ECG"
	@echo ""
	@echo "Usage:"
	@echo "  make <commande>"
	@echo ""
	@echo "Commandes:"
	@echo "  up              - Démarrer les conteneurs"
	@echo "  down            - Arrêter les conteneurs"
	@echo "  restart         - Redémarrer les conteneurs"
	@echo "  build           - Reconstruire les images Docker"
	@echo "  logs            - Afficher les logs des conteneurs"
	@echo "  clean           - Nettoyer complètement le projet (conteneurs, volumes, images)"
	@echo "  setup           - Configurer l'environnement initial (.env)"
	@echo "  backup          - Sauvegarder la base de données"
	@echo "  restore         - Restaurer la base de données depuis une sauvegarde"
	@echo "  shell-web       - Ouvrir un shell dans le conteneur web"
	@echo "  shell-db        - Ouvrir un shell dans le conteneur MySQL"
	@echo "  status          - Afficher l'état des conteneurs"
	@echo "  prune           - Supprimer les conteneurs et les volumes non utilisés"
	@echo "  help            - Afficher cette aide"

# Démarrer les conteneurs
up:
	@echo "Démarrage des conteneurs..."
	$(DOCKER_COMPOSE) up -d
	@echo "Conteneurs démarrés."
	@echo "Web: http://localhost:80"
	@echo "PHPMyAdmin: http://localhost:8080"

# Arrêter les conteneurs
down:
	@echo "Arrêt des conteneurs..."
	$(DOCKER_COMPOSE) down
	@echo "Conteneurs arrêtés."

# Redémarrer les conteneurs
restart:
	@echo "Redémarrage des conteneurs..."
	$(DOCKER_COMPOSE) restart
	@echo "Conteneurs redémarrés."

# Reconstruire les images Docker
build:
	@echo "Construction des images Docker..."
	$(DOCKER_COMPOSE) build
	@echo "Images construites."

# Afficher les logs des conteneurs
logs:
	$(DOCKER_COMPOSE) logs -f

# Nettoyer complètement le projet
clean:
	@echo "Nettoyage du projet..."
	$(DOCKER_COMPOSE) down -v --rmi all
	@echo "Projet nettoyé."

# Configurer l'environnement initial
setup:
	@if [ ! -f $(ENV_FILE) ]; then \
		echo "Création du fichier $(ENV_FILE) à partir de $(ENV_EXAMPLE)..."; \
		cp $(ENV_EXAMPLE) $(ENV_FILE); \
		echo "Fichier $(ENV_FILE) créé. Modifiez-le selon vos besoins."; \
	else \
		echo "Le fichier $(ENV_FILE) existe déjà."; \
	fi

# Sauvegarder la base de données
backup:
	@echo "Sauvegarde de la base de données..."
	@mkdir -p backups
	$(DOCKER_COMPOSE) exec mysql mysqldump -u $(shell grep DB_USER $(ENV_FILE) | cut -d= -f2) \
		-p$(shell grep DB_PASSWORD $(ENV_FILE) | cut -d= -f2) \
		$(shell grep DB_NAME $(ENV_FILE) | cut -d= -f2) > backups/backup-$$(date +%Y%m%d-%H%M%S).sql
	@echo "Base de données sauvegardée dans backups/."

# Restaurer la base de données depuis une sauvegarde
restore:
	@echo "Restauration de la base de données..."
	@if [ -z "$(file)" ]; then \
		echo "Erreur: Spécifiez le fichier de sauvegarde avec file=backups/fichier.sql"; \
	else \
		cat $(file) | $(DOCKER_COMPOSE) exec -T mysql mysql -u $(shell grep DB_USER $(ENV_FILE) | cut -d= -f2) \
			-p$(shell grep DB_PASSWORD $(ENV_FILE) | cut -d= -f2) \
			$(shell grep DB_NAME $(ENV_FILE) | cut -d= -f2); \
		echo "Base de données restaurée depuis $(file)."; \
	fi

# Ouvrir un shell dans le conteneur web
shell-web:
	@echo "Ouverture d'un shell dans le conteneur web..."
	$(DOCKER_COMPOSE) exec web bash

# Ouvrir un shell dans le conteneur MySQL
shell-db:
	@echo "Ouverture d'un shell dans le conteneur MySQL..."
	$(DOCKER_COMPOSE) exec mysql bash

# Afficher l'état des conteneurs
status:
	@echo "État des conteneurs:"
	$(DOCKER_COMPOSE) ps

# Supprimer les conteneurs et volumes non utilisés
prune:
	@echo "Suppression des conteneurs et volumes non utilisés..."
	$(DOCKER) system prune -f
	$(DOCKER) volume prune -f
	@echo "Nettoyage terminé."

# Installer les dépendances front-end (si nécessaire)
frontend-deps:
	@echo "Installation des dépendances front-end (à implémenter si nécessaire)..."
	@echo "Dépendances installées."

# Par défaut, afficher l'aide
default: help 