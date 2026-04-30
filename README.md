# QueueLess

QueueLess est une application web de reservation de creneaux horaires developpee en PHP natif, MySQL, HTML, CSS et JavaScript. Le projet a ete pense pour une demonstration academique ou un PFE, avec une structure simple, claire et facile a executer localement sous XAMPP ou Laragon.

## Fonctionnalites principales

- inscription et connexion avec sessions PHP
- navigation publique avec landing page moderne
- consultation des services et de leurs details
- reservation et annulation de creneaux
- espace utilisateur avec profil, dashboard, reservations et avis
- espace administrateur avec gestion des services, slots, reservations, utilisateurs et statistiques

## Technologies

- PHP 8+
- MySQL / MariaDB
- PDO
- HTML5
- CSS3
- JavaScript natif

## Structure du projet

```text
/queueless
    /actions
    /admin
    /assets
        /css
        /js
        /uploads
    /config
    /database
    /includes
    /pages
    /storage
        /sessions
    index.php
```

## Installation locale

1. Copier le dossier `queueless` dans `htdocs` de XAMPP ou dans le dossier web de Laragon.
2. Demarrer Apache et MySQL.
3. Creer une base de donnees `queueless` dans phpMyAdmin.
4. Importer le fichier [`database/queueless.sql`](./database/queueless.sql).
5. Verifier les informations de connexion dans [`config/database.php`](./config/database.php).
6. Ouvrir le projet dans le navigateur :

```text
http://localhost/queueless/
```

## Comptes de demonstration

- Administrateur
  - Email : `admin@queueless.local`
  - Mot de passe : `Admin123!`
- Utilisateur
  - Email : `user@queueless.local`
  - Mot de passe : `User123!`

## Configuration utile

- Le fichier [`config/app.php`](./config/app.php) contient les constantes globales du projet.
- Les sessions sont stockees dans `storage/sessions`.
- Les images uploadees sont stockees dans `assets/uploads`.

## Pages principales

- `index.php` : page d accueil
- `pages/services.php` : liste des services
- `pages/service_details.php` : details d un service et creneaux
- `pages/login.php` : connexion
- `pages/register.php` : inscription
- `pages/user_dashboard.php` : dashboard utilisateur
- `admin/dashboard.php` : dashboard administrateur

## Bonnes pratiques appliquees

- `password_hash()` et `password_verify()`
- requetes preparees avec PDO
- validation cote serveur et cote client
- separation entre pages, includes, actions et configuration
- controle d acces utilisateur et administrateur
- upload d images securise

## Remarques

- Le projet est volontairement en PHP natif pour rester simple a comprendre et facile a maintenir.
- Si la base de donnees n est pas encore importee, certaines pages basculent en mode demo ou affichent un message clair.
