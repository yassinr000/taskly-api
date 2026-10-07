# Taskly API

API REST de Taskly : Laravel 12, Sanctum (tokens), MySQL.

- Authentification par token, profil, mot de passe
- Boards, membres, tâches, dashboard, « mes tâches »
- Notes, fichiers privés, checklist, notifications, rappels d'échéance

## Prérequis
PHP 8.2+ (extensions `zip`, `mbstring`, `openssl`, `pdo_mysql`, `fileinfo`), Composer 2, MySQL ou MariaDB.

## Lancer en local (Windows + XAMPP)
Dans phpMyAdmin, crée une base nommée `taskly`. Puis, dans Git Bash :
```
composer install
cp .env.example .env
php artisan key:generate
```
Ouvre `.env` et règle `DB_PORT` (3306 ou 3307, selon XAMPP), puis :
```
php artisan migrate --seed
php artisan serve
```
L'API répond sur http://127.0.0.1:8000/api. Contrôle : http://127.0.0.1:8000/up

Comptes de démonstration (créés par `--seed`) : `ali@taskly.test` et `sara@taskly.test`,
mot de passe `Password123`. Pour tout remettre à zéro : `php artisan migrate:fresh --seed`
(⚠️ supprime toutes les données).

## Tests
```
php artisan test
```
Les tests utilisent SQLite en mémoire (réglé dans `phpunit.xml`) : aucune base à préparer,
mais l'extension PHP `pdo_sqlite` doit être active.

## Rappels d'échéance
`php artisan taskly:send-reminders` (lancée chaque jour à 8 h par le planificateur :
`php artisan schedule:work` en local, un cron `schedule:run` chaque minute sur un serveur).

## Déployer
Voir [docs/DEPLOY.md](docs/DEPLOY.md) : image Docker fournie (`Dockerfile`) ou hébergement PHP classique.

## Routes principales
`POST /api/register`, `POST /api/login`, `POST /api/logout`, `GET /api/me`,
`/api/boards`, `/api/boards/{id}/tasks`, `/api/tasks/{id}/notes|attachments|checklist`,
`/api/notifications`, `/api/dashboard`, `/api/my-tasks`. Liste complète : `php artisan route:list`.
