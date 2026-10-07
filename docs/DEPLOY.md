# Déployer l'API Taskly

L'API a besoin de : **PHP 8.2+**, **MySQL 8 (ou MariaDB 10.6+)** et d'un endroit pour garder les fichiers envoyés.

## Variables d'environnement (production)

| Variable | Valeur |
|---|---|
| `APP_ENV` | `production` |
| `APP_DEBUG` | `false` |
| `APP_KEY` | générée avec `php artisan key:generate --show` |
| `APP_URL` | l'adresse publique de l'API, ex. `https://taskly-api.example.com` |
| `DB_CONNECTION` | `mysql` |
| `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` | fournis par ton hébergeur MySQL |
| `FRONTEND_URL` | l'adresse publique du site React, ex. `https://taskly.example.com` (sans `/` final) |
| `SESSION_DRIVER` / `CACHE_STORE` | `database` (déjà le défaut) |

## Option A : hébergeur Docker (Render, Railway, Fly.io...)
1. Crée une base MySQL chez l'hébergeur (ou chez un service MySQL géré) et note ses identifiants.
2. Crée un **Web Service** à partir de ce dépôt, type **Docker** : le `Dockerfile` à la racine est utilisé.
3. Renseigne les variables du tableau ci-dessus.
4. Au démarrage, le conteneur lance `php artisan migrate --force` tout seul.
5. Ouvre `https://TON-API/up` : la page doit répondre 200.
6. **Fichiers joints** : le disque d'un conteneur est effacé à chaque déploiement. Ajoute un **disque persistant** monté sur `/var/www/html/storage/app/private` (Render : *Disks*, Railway : *Volumes*), sinon les fichiers envoyés disparaissent.

Données de démonstration (une seule fois, depuis la console de l'hébergeur) : `php artisan db:seed`.

## Option B : hébergement mutualisé (cPanel)
1. Envoie le projet sur le serveur, puis lance `composer install --no-dev --optimize-autoloader`.
2. Pointe la racine web du domaine sur le dossier `public/`.
3. Copie `.env.example` vers `.env`, remplis les variables, puis `php artisan key:generate`.
4. Lance `php artisan migrate --force` et `php artisan config:cache`.
5. Rends `storage/` et `bootstrap/cache/` inscriptibles (`chmod -R 775`).
6. Rappels d'échéance : ajoute une tâche cron toutes les minutes : `php /chemin/artisan schedule:run`.

## Rappels d'échéance
La commande `taskly:send-reminders` est programmée chaque jour à 8 h. Sur un serveur, il faut qu'un cron lance `php artisan schedule:run` chaque minute (sur Docker : ajoute un second service « Cron Job » qui exécute cette commande).

## Vérifications après déploiement
1. `GET /up` répond 200.
2. Depuis le site, inscription puis connexion fonctionnent.
3. Crée un board, une tâche, envoie un PDF, télécharge-le.
4. Aucune erreur CORS dans la console du navigateur : si besoin, corrige `FRONTEND_URL`.
