#!/bin/bash

# Configuration des informations utilisateur
git config --global user.email matteo.candotti16@hotmail.fr

git config --global user.name mcandotti

# Ajouter tous les fichiers modifiés
git add -A

# Créer un commit
git commit -m "Correction des problèmes de navigation et ajout de la fonctionnalité de connexion"

# Configurer le dépôt distant GitHub (remplacez l'URL par votre dépôt GitHub)
# Si le dépôt est déjà configuré, cette étape sera ignorée
git remote add origin https://github.com/votre-nom/ecg-monitoring-system.git

# Pousser les modifications vers GitHub
git push -u origin master 