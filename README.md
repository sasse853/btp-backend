# BTP MASTER PRO — Backend (API Laravel 11)

API REST de gestion de chantiers BTP pour **MS-GROUP AFRIQUE**.
Stack : **PHP 8.2 · Laravel 11 · MySQL 8.0 · Sanctum · DomPDF · Pusher**.

Toutes les routes sont versionnées sous `/api/v1` et renvoient un format JSON homogène :

```json
{ "success": true, "message": "...", "data": { } }
```

---

## 1. Prérequis

- PHP **8.2+** (extensions : `pdo_mysql`, `mbstring`, `openssl`, `gd`, `fileinfo`, `zip`)
- **Composer** 2.x
- **MySQL** 8.0 (ou MariaDB 10.5+)
- (Optionnel) Compte **Pusher** pour le temps réel

> Sous Windows, le plus simple est d'installer **[Laragon](https://laragon.org/)**
> (PHP + MySQL + Apache inclus).

## 2. Installation pas à pas

```bash
# 1. Dépendances PHP
composer install

# 2. Fichier d'environnement + clé applicative
cp .env.example .env
php artisan key:generate

# 3. Configurer la base dans .env (DB_DATABASE=btp_master_pro, DB_USERNAME, DB_PASSWORD)
#    Puis créer la base (via phpMyAdmin / HeidiSQL) :
#    CREATE DATABASE btp_master_pro CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

# 4. Migrations + données de démonstration
php artisan migrate --seed

# 5. Lien symbolique pour les fichiers uploadés
php artisan storage:link

# 6. Démarrer le serveur
php artisan serve   # http://localhost:8000
```

## 3. Comptes de démonstration

| Rôle          | Email           | Mot de passe |
|---------------|-----------------|--------------|
| Administrateur| `admin@btp.com` | `password`   |
| Chef chantier | `chef@btp.com`  | `password`   |

## 4. Temps réel (Pusher) — optionnel

Renseigner dans `.env` : `PUSHER_APP_ID`, `PUSHER_APP_KEY`, `PUSHER_APP_SECRET`,
`PUSHER_APP_CLUSTER`, puis `BROADCAST_CONNECTION=pusher`. Les mêmes clés publiques
doivent être reportées côté frontend (`VITE_PUSHER_APP_KEY`, `VITE_PUSHER_APP_CLUSTER`).

## 5. Principaux endpoints

| Méthode | URI                                   | Rôle requis        |
|---------|---------------------------------------|--------------------|
| POST    | `/api/v1/login`                       | public             |
| GET     | `/api/v1/me`                          | authentifié        |
| GET     | `/api/v1/dashboard`                   | authentifié        |
| —       | `/api/v1/chantiers` (CRUD)            | authentifié        |
| —       | `/api/v1/personnel` (CRUD)           | authentifié        |
| POST    | `/api/v1/presences/batch`            | authentifié        |
| —       | `/api/v1/finances` (CRUD)            | authentifié        |
| PATCH   | `/api/v1/finances/{id}/valider`      | **admin**          |
| PATCH   | `/api/v1/avenants/{id}/valider`      | **admin**          |
| PATCH   | `/api/v1/documents/{id}/valider`     | **admin**          |
| POST    | `/api/v1/chantiers/{id}/rapport`     | authentifié        |

## 6. Règles métier automatisées (Observers)

- Génération automatique de la référence chantier `CH-AAAA-NNN`.
- Calcul du `montant_du` d'une présence (`taux_journalier × coefficient`).
- Recalcul du `budget_consolide` à l'approbation d'un avenant.
- Notification admin à la création d'une dépense / document en attente.
- Alerte automatique quand les dépenses validées atteignent **80 %** du budget.
