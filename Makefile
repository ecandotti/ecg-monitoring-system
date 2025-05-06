# Makefile for ECG monitoring system administration
# Author: IR1 Student Project

# Variables
ifeq ($(OS),Windows_NT)
	DOCKER_COMPOSE = docker-compose
else
	DOCKER_COMPOSE = docker compose
endif
DOCKER = docker
ENV_FILE = .env
ENV_EXAMPLE = .env.example

# Main commands
.PHONY: up down restart build logs clean setup backup restore shell help

# Help/documentation
help:
	@echo "Makefile for ECG monitoring system administration"
	@echo ""
	@echo "Usage:"
	@echo "  make <command>"
	@echo ""
	@echo "Commands:"
	@echo "  up              - Start containers"
	@echo "  down            - Stop containers" 
	@echo "  restart         - Restart containers"
	@echo "  build           - Rebuild Docker images"
	@echo "  logs            - Display container logs"
	@echo "  clean           - Clean project completely (containers, volumes, images)"
	@echo "  setup           - Configure initial environment (.env)"
	@echo "  backup          - Backup database"
	@echo "  restore         - Restore database from backup"
	@echo "  shell-web       - Open shell in web container"
	@echo "  shell-db        - Open shell in MySQL container"
	@echo "  status          - Show container status"
	@echo "  prune           - Remove unused containers and volumes"
	@echo "  help            - Show this help"

# Start containers
up:
	@echo "Starting containers..."
	$(DOCKER_COMPOSE) up -d
	@echo "Containers started."
	@echo "Web: http://localhost:80"
	@echo "PHPMyAdmin: http://localhost:8080"

# Stop containers
down:
	@echo "Stopping containers..."
	$(DOCKER_COMPOSE) down
	@echo "Containers stopped."

# Restart containers
restart:
	@echo "Restarting containers..."
	$(DOCKER_COMPOSE) restart
	@echo "Containers restarted."

# Rebuild Docker images
build:
	@echo "Building Docker images..."
	$(DOCKER_COMPOSE) build
	@echo "Images built."

# Display container logs
logs:
	$(DOCKER_COMPOSE) logs -f

# Clean project completely
clean:
	@echo "Cleaning project..."
	$(DOCKER_COMPOSE) down -v --rmi all
	@echo "Project cleaned."

# Configure initial environment
setup:
	@if [ ! -f $(ENV_FILE) ]; then \
		echo "Creating $(ENV_FILE) from $(ENV_EXAMPLE)..."; \
		cp $(ENV_EXAMPLE) $(ENV_FILE); \
		echo "$(ENV_FILE) created. Modify it as needed."; \
	else \
		echo "$(ENV_FILE) already exists."; \
	fi

# Backup database
backup:
	@echo "Backing up database..."
	@mkdir -p backups
	$(DOCKER_COMPOSE) exec mysql mysqldump -u $(shell grep DB_USER $(ENV_FILE) | cut -d= -f2) \
		-p$(shell grep DB_PASSWORD $(ENV_FILE) | cut -d= -f2) \
		$(shell grep DB_NAME $(ENV_FILE) | cut -d= -f2) > backups/backup-$$(date +%Y%m%d-%H%M%S).sql
	@echo "Database backed up to backups/."

# Restore database from backup
restore:
	@echo "Restoring database..."
	@if [ -z "$(file)" ]; then \
		echo "Error: Specify backup file with file=backups/file.sql"; \
	else \
		cat $(file) | $(DOCKER_COMPOSE) exec -T mysql mysql -u $(shell grep DB_USER $(ENV_FILE) | cut -d= -f2) \
			-p$(shell grep DB_PASSWORD $(ENV_FILE) | cut -d= -f2) \
			$(shell grep DB_NAME $(ENV_FILE) | cut -d= -f2); \
		echo "Database restored from $(file)."; \
	fi

# Open shell in web container
shell-web:
	@echo "Opening shell in web container..."
	$(DOCKER_COMPOSE) exec web bash

# Open shell in MySQL container
shell-db:
	@echo "Opening shell in MySQL container..."
	$(DOCKER_COMPOSE) exec mysql bash

# Show container status
status:
	@echo "Container status:"
	$(DOCKER_COMPOSE) ps

# Remove unused containers and volumes
prune:
	@echo "Removing unused containers and volumes..."
	$(DOCKER) system prune -f
	$(DOCKER) volume prune -f
	@echo "Cleanup complete."

# Install frontend dependencies (if needed)
frontend-deps:
	@echo "Installing frontend dependencies (to be implemented if needed)..."
	@echo "Dependencies installed."

# Default to showing help
default: help