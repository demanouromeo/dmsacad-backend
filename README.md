# DMS ACAD Backend

## Vue d'ensemble

DMS ACAD Backend est une API REST développée en Laravel 11, servant de socle applicatif à un système de
gestion académique multi-établissements. Il permet de gérer, pour plusieurs écoles indépendantes, l'ensemble
des données pédagogiques et administratives : personnel, élèves, classes, matières, notes, absences, années
scolaires, filières, etc. L'application est hébergée sous XAMPP/Apache (`http://localhost/dmsacad_backend_dev`),
toutes les routes étant exposées sous le préfixe `/api`. Elle constitue le pendant serveur d'une application
front-end React logée dans le sous-dossier `dms_acad_react`, avec laquelle certains traitements (comme la
génération des bulletins) sont volontairement partagés côté client plutôt que dupliqués côté API.

## Prérequis

- PHP 8.2+ avec l'extension `pdo_mysql` activée
- Composer
- MySQL/MariaDB (une base par établissement, plus une base transverse)
- XAMPP (Apache + PHP + MySQL) — l'application n'est pas conçue pour tourner via `php artisan serve`
- Node.js/npm (uniquement pour les quelques assets front compilés via Vite, l'essentiel du front vivant dans
  `dms_acad_react`)

⚠️ Si plusieurs binaires PHP sont installés sur la machine (ex. un PHP de XAMPP et un PHP autonome sur le
`PATH`), assurez-vous d'utiliser celui de XAMPP pour toute commande touchant la base de données
(`artisan migrate`, `artisan tinker`, ...) : c'est le seul à disposer de `pdo_mysql` dans ce projet.

```bash
"/c/xampp/php/php.exe" artisan migrate
"/c/xampp/php/php.exe" artisan tinker --execute="..."
```

## Installation

```bash
composer install
copy .env.example .env      # (ou cp .env.example .env sous bash)
"/c/xampp/php/php.exe" artisan key:generate
```

## Configuration

La configuration se fait via le fichier `.env`. Points spécifiques à ce projet :

- `DB_*` : connexion générique `mysql` (base transverse : comptes, personnel, administrateurs, liaison des
  comptes élèves, ...).
- `<ETABLISSEMENT>_DB_*` : un jeu de variables par établissement (ex. `CES_DE_DABAYE_DB_*`), déclarées dans
  `config/database.php`. Chaque établissement dispose de sa propre base, avec le même schéma.
- `JWT_SECRET`, `ACCESS_TOKEN_DURATION`, `REFRESH_TOKEN_DURATION` : paramètres de l'authentification JWT
  maison (voir plus bas).

Placer le projet dans le répertoire servi par Apache (`htdocs`) sous XAMPP, de sorte qu'il soit accessible à
l'URL `http://localhost/dmsacad_backend_dev`.

## Lancement

Ce projet tourne sous XAMPP/Apache, pas via `php artisan serve`. Il suffit de démarrer Apache et MySQL depuis
le panneau de contrôle XAMPP ; le projet doit être placé dans `htdocs` pour être servi.

## Tests

```bash
"/c/xampp/php/php.exe" artisan test
```

## Style de code

```bash
"/c/xampp/php/php.exe" vendor/bin/pint
```

## Documentation API

Swagger (`darkaonline/l5-swagger` + `zircote/swagger-php`) est installé et configuré
(`config/l5-swagger.php`), mais aucune annotation `@OA\*` n'existe encore dans le code : la génération
Swagger n'est pas branchée pour l'instant.

## Structure du projet

- `app/Http/Controllers` — contrôleurs REST, un par domaine métier (comptes, élèves, personnel, classes,
  matières, ...).
- `app/Http/Controllers/MyHelper.php` — classe utilitaire statique centrale (voir plus bas).
- `app/Http/Middleware` — `JwtMiddleware` (`jwt.auth`) et `RoleMiddleware` (`role:...`).
- `app/Models` — modèles Eloquent générés par Reliese, reflétant le schéma partagé entre établissements.
- `config/database.php` — déclaration des connexions MySQL (une par établissement + une générique).
- `routes/api.php` — toutes les routes de l'API, montées via `bootstrap/app.php`.
- `dms_acad_react` (sous-dossier, ignoré par Git dans ce dépôt) — application front-end React consommant
  cette API.

## Architecture multi-tenant : une base de données par établissement

La particularité structurante du projet est l'absence de base de données unique. `config/database.php`
déclare une connexion MySQL nommée par établissement (`CES_DE_DABAYE`, `LYCEE_DE_MERI`, `LB_BOGO`, ...),
chacune avec son propre hôte/base/identifiants. Une connexion générique `mysql` (`sm_db2`) héberge en
parallèle les données transverses : comptes utilisateurs, personnel, administrateurs, liaison des comptes
élèves, etc. Chaque base réplique le même schéma (`school_year`, `basic_school_config`, `classe`, `student`,
`staff`, `subject`, ...).

Chaque contrôleur sélectionne la base cible à l'exécution en lisant un paramètre `connection`, puis en
basculant la connexion par défaut de Laravel :

```php
$connection = $request->input("connection");
config(["database.default" => $connection]);
```

Ce motif se répète environ 190 fois à travers la quasi-totalité des contrôleurs : c'est l'idiome
architectural central de l'application, pas un détail incident. Aucune validation n'est faite sur les valeurs
de `connection` inconnues — un nom invalide se traduit par un échec de connexion remonté depuis le bloc
`catch`.

## Authentification : JWT maison, pas Sanctum

`laravel/sanctum` est installé mais inutilisé. L'authentification est implémentée à la main avec
`firebase/php-jwt` :

- `AccountController::login` valide `login`/`pwd`/`connection`, recherche le compte (mots de passe **en
  clair**, TODO connu et volontairement reporté), résout un rôle via `MyHelper::findRole()`, et délivre un
  jeton d'accès JWT court (1h par défaut) plus un jeton de rafraîchissement en cookie httpOnly (7 jours).
- `AccountController::refresh` renouvelle le jeton d'accès à partir du cookie.
- `JwtMiddleware` (`jwt.auth`) décode le Bearer token et l'expose sous `auth_payload`, sans vérifier de rôle.
- `RoleMiddleware` (`role:ADMIN,...`) contrôle le rôle à partir de `auth_payload`, et doit s'exécuter après
  `jwt.auth`.
- Mapping type de compte → rôle : `1=ADMIN, 2=TOP_MANAGEMENT, 3=SG, 4=BURSAR, 5=TEACHER, 6=PARENT,
  7=STUDENT, 8=CENSEUR`.

Le durcissement sécurité (JWT + RBAC) est un chantier en cours, ne couvrant qu'une poignée de routes ; la
majorité des endpoints restent hérités et non protégés.

## Convention d'erreur : le 500 comme symptôme d'exception avalée

De nombreuses méthodes enveloppent leur logique dans un `try/catch` qui retourne `response()->json([], 500)`
sans journaliser l'exception. La cause réelle (souvent un accès à une propriété nulle) n'est jamais visible
côté HTTP ; le diagnostic passe par l'ajout temporaire d'un `Log::error()` dans le `catch` visé.

## `MyHelper` : le cœur utilitaire

`MyHelper.php` est une classe statique volumineuse utilisée par presque tous les contrôleurs pour : les
suppressions en cascade (pas de contraintes DB, tout est géré manuellement à travers 10+ tables liées), les
recherches par nom/année, et le mapping des rôles. Elle exécute majoritairement des `DB::select()` bruts
plutôt que de l'Eloquent — style à respecter lors de toute modification.

## Paramètre Classifié / Non Classifié (NC)

`classifiedparam` contient au plus une ligne par année scolaire, par établissement, avec deux colonnes
exploitées : `nb_matieres_rate` et `classified`. Point non évident : `classified = 0` signifie que tous les
élèves sont classifiés (jamais de NC) ; `classified = 1` rend la classification conditionnelle, calculée par
élève et par trimestre selon le taux de participation aux évaluations. L'absence de réglage équivaut à
`classified = 0`. Cette logique alimente la génération des bulletins (pas encore développée), dont
l'algorithme complet est documenté côté `dms_acad_react` et sera implémenté côté client, dans la continuité
de calculs déjà faits côté front (`MarkEntryManager`, `EffectifsManager`).

## Modèles et périmètre fonctionnel

`app/Models` contient des modèles Eloquent générés par Reliese, reflétant le schéma partagé. Les contrôleurs
couvrent : comptes, personnel, élèves, parents, classes, matières, sections, filières, groupes, spécialités,
informations d'établissement, paramètres de seuils, classification NC, sauvegardes et verrouillage.
