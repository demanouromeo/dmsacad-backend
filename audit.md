# Audit de sécurité — DMS ACAD Backend

**Cible :** API Laravel 11 hébergée en production sur `https://dmsacad.com/dmsacad_backend_secured/` (copie de
développement analysée : `c:\xampp\htdocs\dmsacad_backend_dev`)
**Date :** 2026-08-18 (revue de code statique initiale) + vérifications en direct le même jour une fois
l'adresse de production confirmée.
**Type d'audit :** Revue de code statique (boîte blanche) complétée par des requêtes en lecture seule,
non destructives (`GET`/`HEAD` sur des ressources statiques), contre le serveur de production, pour confirmer
ou infirmer les hypothèses issues de la revue de code — voir le point 6 pour le détail et les résultats.

## Résumé exécutif

L'application présente plusieurs vulnérabilités **critiques directement exploitables**, dont une exposition de
données déjà active en l'état du dépôt analysé (un dump SQL complet placé dans le dossier web public). Le
cumul des problèmes suivants constitue un risque de compromission totale de la plateforme (toutes les écoles
clientes confondues) :

1. Un dump SQL complet de la base centrale (comptes, mots de passe en clair) est stocké dans le dossier
   public servi par le web.
2. L'endpoint de sauvegarde de base de données est accessible à **tout** utilisateur authentifié, quel que
   soit son rôle (élève, parent...), et non au seul ADMIN.
3. Les mots de passe sont stockés, comparés **et renvoyés au client** en clair.
4. L'injection SQL par concaténation est un motif systémique (~294 requêtes brutes `DB::select`), avec au
   moins un point d'entrée directement exploitable et confirmé (`StaffController::modifyStaff`).
5. `APP_DEBUG=true` alors que `APP_ENV=production`.
6. Le point d'entrée Laravel (`index.php` + `.htaccess`) est dupliqué à la racine du dépôt, hors du dossier
   `public/`, sans règle de blocage des fichiers sensibles — à vérifier d'urgence sur le serveur réel.

Ces six points doivent être traités **avant tout autre chantier**, y compris fonctionnel. Le détail, les
preuves et les correctifs recommandés suivent, classés par gravité.

---

## Suivi des corrections (2026-08-18)

| # | Statut | Détail |
|---|--------|--------|
| 1 | 🟡 Partiellement corrigé | `public/db_backup/sm_db2.sql` a été supprimé de l'arborescence de travail (`git status` : `D public/db_backup/sm_db2.sql`, non encore commité). **Mais** ce fichier a été commité dans le tout premier commit du dépôt (`2a094af`), et ce commit est présent sur `origin/main` (`git@github.com:demanouromeo/dmsacad-backend.git`) — c'est-à-dire que le dump complet (comptes, mots de passe en clair) reste récupérable via `git log`/`git show` par quiconque a accès au dépôt GitHub, que le repo soit public ou privé. Supprimer le fichier du répertoire de travail ne suffit pas. Voir remédiation détaillée ci-dessous. |
| 2 | 🟢 Corrigé (endpoint désactivé) | La route `GET /backup/backupDB` est commentée dans `routes/api.php:323` — plus aucun rôle ne peut l'atteindre. Le contrôleur `BackupController.php` n'a en revanche pas été modifié : il ignore toujours le paramètre `connection` (utilise `env('DB_HOST/USERNAME/PASSWORD/DATABASE')`, donc toujours `sm_db2`) et écrit un fichier temporaire avec un chemin non validé. Si cette route est un jour réactivée sans corriger le contrôleur, la vulnérabilité revient à l'identique. Bonus non demandé : les 12 routes `/test/*` (y compris les opérations destructives `alterStaff`, `deleteManyStudClasse`, `prepareNewYear`) ont aussi été commentées dans le même mouvement — cela referme le point 8 (Élevé) du rapport initial. |
| 5 | 🔴 Non corrigé | `.env` local contient toujours `APP_ENV=production` avec `APP_DEBUG=true`. |
| 6 | 🔴 **Confirmé en direct, non corrigé** | L'adresse réelle du backend est `https://dmsacad.com/dmsacad_backend_secured/` (trouvée dans `dms_acad_react/src/dbmanger/MyConstants.tsx:8`, `gBaseRemoteUrl`) — distincte des deux adresses essayées précédemment par erreur. Vérifié en direct le 2026-08-18, avec un témoin (chemin inventé au hasard → 404 propre, donc pas de fallback SPA trompeur cette fois) : `composer.json` répond 200 avec le **contenu réel du fichier** (2112 octets, identique au fichier local), et `storage/logs/laravel.log` répond 200 avec un **vrai fichier de log de 22 Mo** contenant des traces d'erreurs (chemins serveur, exceptions). Ceci confirme que le serveur sert la racine du projet Laravel, pas seulement `public/`. Point positif : `.env`, `.env.example`, `.git/config` et `.htaccess` renvoient tous 403 — une règle bloque spécifiquement les fichiers commençant par un point, ce qui évite le pire. `database/` renvoie 403 (pas de listing de répertoire). Le dump `db_backup/sm_db2.sql` du point 1 n'a en revanche **pas** été retrouvé à cet endroit (404 à `/db_backup/sm_db2.sql` et `/public/db_backup/sm_db2.sql`) — il n'est donc pas actuellement exposé en direct sur ce serveur, seulement dans l'historique Git (voir point 1). Les fichiers `.php` exécutés directement (`vendor/autoload.php`, `app/Http/Controllers/MyHelper.php`, `routes/api.php`) renvoient une page vide (200/500 sans corps) : pas de fuite du code source PHP, `display_errors` semble désactivé au niveau PHP. |

### Action requise pour purger `sm_db2.sql` de l'historique Git (point 1)

Une simple suppression + nouveau commit laisse le fichier récupérable dans l'historique. Deux étapes
distinctes sont nécessaires :

1. **Rotation des identifiants** : puisque ce dump a été présent sur le dépôt GitHub (public ou privé, peu
   importe pour la suite), traiter tous les mots de passe qu'il contient comme compromis — régénérer les
   mots de passe des comptes de la table `account` de `sm_db2`, et si ce même commit contenait par erreur
   d'autres secrets, les régénérer aussi. C'est la seule action réellement irréversible face à une fuite déjà
   commitée : la suppression de l'historique réduit l'exposition future, mais ne défait pas le passé.
2. **Purge de l'historique** (destructive — nécessite une confirmation explicite avant exécution, car elle
   réécrit l'historique et impose un `push --force` sur `origin/main`, ce qui casse les clones existants des
   autres contributeurs) :
   ```bash
   git filter-repo --path public/db_backup/sm_db2.sql --invert-paths
   # puis, après vérification :
   git push --force origin main
   ```
   À défaut de `git filter-repo`, `bfg --delete-files sm_db2.sql` fait la même chose. Prévenir tout
   collaborateur de re-cloner le dépôt après coup plutôt que de faire un `pull`.

### Résultat de la vérification en direct (2026-08-18, contre `https://dmsacad.com/dmsacad_backend_secured/`)

| Chemin | Résultat | Interprétation |
|---|---|---|
| `/api/configs/allSchools` | 200, JSON | confirme qu'il s'agit bien du backend (route API réelle) |
| `/ce-chemin-nexiste-pas-xyz123` (témoin) | 404 propre | pas de fallback SPA — les codes ci-dessous sont fiables |
| `/.env` | 403 | bloqué |
| `/.env.example` | 403 | bloqué |
| `/.git/config` | 403 | bloqué |
| `/.htaccess` | 403 | bloqué |
| `/composer.json` | **200, contenu réel (2112 octets)** | 🔴 exposé |
| `/storage/logs/laravel.log` | **200, contenu réel (22 Mo)** | 🔴 exposé |
| `/vendor/autoload.php`, `/routes/api.php`, `/app/Http/Controllers/MyHelper.php` | 200/500, corps vide | s'exécutent comme PHP, pas de fuite de code source |
| `/database/` | 403 | pas de listing de répertoire |
| `/db_backup/sm_db2.sql`, `/public/db_backup/sm_db2.sql` | 404 | absent de ce serveur (voir point 1 pour le risque résiduel côté Git) |

**Remédiation immédiate pour ce point :**
1. Sur Hostinger (hPanel), reconfigurer le domaine/sous-dossier `dmsacad_backend_secured` pour que sa racine
   web pointe vers `.../dmsacad_backend_secured/public` et non vers la racine du projet. C'est le seul
   correctif qui traite la cause : tant que la racine du projet est servie telle quelle, la règle qui bloque
   les fichiers en `.` n'est qu'un filet de sécurité partiel (elle ne protège ni `composer.json`, ni
   `storage/`, ni `database/`, ni un futur fichier oublié comme celui du point 1).
2. En attendant ce changement de configuration d'hébergement, supprimer `storage/logs/laravel.log` du
   serveur de production (il est volumineux et contient des chemins/traces d'erreurs internes) et vérifier
   qu'aucune donnée sensible n'y figure avant suppression définitive si une rétention est nécessaire pour le
   débogage.
3. Une fois `public/` correctement isolé, supprimer `index.php` et `.htaccess` dupliqués à la racine du
   projet (ils ne servent plus à rien si Apache pointe déjà vers `public/`).

---

## Tableau de synthèse

| # | Gravité | Constat | Fichier |
|---|---------|---------|---------|
| 1 | 🔴 Critique | Dump SQL complet exposé sans authentification | `public/db_backup/sm_db2.sql` |
| 2 | 🔴 Critique | Endpoint de backup accessible à tout rôle authentifié | `BackupController.php` |
| 3 | 🔴 Critique | Mots de passe en clair, stockés et renvoyés au client | `AccountController.php`, `Account.php` |
| 4 | 🔴 Critique | Injection SQL par concaténation (motif systémique) | ~294 occurrences, ex. `StaffController.php:70` |
| 5 | 🔴 Critique | `APP_DEBUG=true` en production | `.env` |
| 6 | 🔴 Critique (à confirmer) | Racine du dépôt potentiellement servie directement (hors `public/`) | `index.php`, `.htaccess` (racine) |
| 7 | 🟠 Élevé | Contrôle d'accès manquant côté serveur pour VP/CENSEUR et SG (IDOR) | `routes/api.php` (commentaires explicites) |
| 8 | 🟠 Élevé | Routes `/test/*` destructrices accessibles à tout utilisateur authentifié | `TestController.php`, `routes/api.php` |
| 9 | 🟠 Élevé | Cookie `refresh_token` sans attribut `Secure` | `AccountController.php` |
| 10 | 🟡 Moyen | Pas de limitation de débit dédiée sur la connexion | `routes/api.php` |
| 11 | 🟡 Moyen | Traversée de chemin via le paramètre `connection` non validé | `SchoolInfoController.php`, `BackupController.php` |
| 12 | 🟡 Moyen | Messages d'exception bruts renvoyés au client (300 occurrences) | Quasi tous les contrôleurs |
| 13 | 🟡 Moyen | CRUD `Account` mort avec assignation de masse illimitée | `AccountController.php` (`store`/`update`) |
| 14 | 🟢 Faible | CORS large (`*` méthodes/en-têtes) compensé par liste blanche d'origines | `config/cors.php` |
| 15 | 🟢 Faible | Liste des écoles accessible sans authentification | `routes/api.php` |

---

## 🔴 Critique

### 1. Dump SQL complet exposé dans le dossier web public

**Preuve :** `public/db_backup/sm_db2.sql` (710 Ko, daté du 11/10/2024) contient un `CREATE TABLE`/`INSERT
INTO` complet de la base centrale `sm_db2`, y compris la table `account` (identifiants de connexion, mots de
passe en clair — voir point 3), `staff`, `student`, `parent`, etc. Ce fichier n'est référencé nulle part dans
le code applicatif (aucune route ni contrôleur n'écrit dans `public/db_backup/`) : il s'agit d'une sauvegarde
manuelle oubliée dans le mauvais dossier.

**Impact :** `public/` est le dossier servi tel quel par le serveur web dans un déploiement Laravel standard.
Si la copie de production reproduit cette arborescence, ce fichier est téléchargeable par **quiconque, sans
authentification**, à une URL du type `https://dmsschoolmanager.com/db_backup/sm_db2.sql` — compromission
totale et immédiate des comptes, y compris ADMIN, de toutes les écoles.

**Action immédiate :**
1. Vérifier sur le serveur de production : `curl -I https://dmsschoolmanager.com/db_backup/sm_db2.sql`
2. Si le fichier répond en 200, le supprimer immédiatement du serveur de production et vider le cache de tout
   CDN éventuel.
3. Considérer les identifiants qu'il contient comme compromis : régénérer tous les mots de passe de la table
   `account` et invalider les tokens JWT actifs (rotation de `JWT_SECRET`, voir point 5/6).
4. Supprimer le fichier du dépôt local et de l'historique Git s'il y a été committé (vérification faite :
   absent de l'historique de ce dépôt — à revérifier sur le dépôt utilisé pour déployer en production).
5. Ne jamais écrire de sauvegardes sous `public/` ; utiliser `storage/app/` (non servi par le web) avec des
   téléchargements passant par un contrôleur authentifié.

### 2. `backupDB` accessible à tout rôle authentifié, et il exporte toujours la base centrale

**Preuve (`app/Http/Controllers/BackupController.php`) :** la route est enregistrée dans le groupe
`Route::middleware(['jwt.auth'])` (n'importe quel rôle — ÉLÈVE, PARENT compris), pas dans le groupe
`role:ADMIN`. De plus, le code lit `$connection` depuis la requête pour définir la connexion par défaut de
Laravel, mais se connecte ensuite via un **second PDO construit avec `env('DB_HOST')`, `env('DB_USERNAME')`,
`env('DB_PASSWORD')`, `env('DB_DATABASE')`** — c'est-à-dire toujours les identifiants de la connexion `mysql`
par défaut (`sm_db2`), quel que soit le paramètre `connection` envoyé. Autrement dit : n'importe quel compte
ÉLÈVE ou PARENT authentifié peut appeler `GET /api/backup/backupDB` et récupérer un dump complet de la base
centrale contenant tous les comptes de toutes les écoles.

**Impact :** identique au point 1, mais reproductible à volonté par n'importe quel utilisateur légitime de
faible privilège (pas besoin d'un accès filesystem au serveur).

**Recommandation :**
- Restreindre la route au groupe `role:ADMIN` (et idéalement à un rôle super-admin distinct, puisque le
  périmètre couvre toutes les écoles, pas seulement celle de l'ADMIN appelant).
- Faire respecter le paramètre `connection` demandé (ou le supprimer et documenter clairement que
  l'endpoint exporte toujours `sm_db2`).
- Écrire le fichier temporaire dans `storage/app/` plutôt qu'à la racine du projet (`fopen($file_name, ...)`
  utilise un chemin relatif qui dépend du répertoire de travail courant du processus PHP).

### 3. Mots de passe en clair : stockés, comparés, et renvoyés au client

**Preuve :**
- `AccountController::connect` (ligne 201) : `->where('pwd', $pwd)` — comparaison en clair, pas de hachage.
- La réponse de connexion renvoie `'user' => $account` (ligne 299) : le modèle `Account`
  (`app/Models/Account.php`) ne déclare **aucun `$hidden`**, donc le champ `pwd` (le mot de passe en clair de
  l'utilisateur qui vient de se connecter) est sérialisé et renvoyé dans le corps JSON de la réponse de
  connexion — à chaque connexion, sur le réseau, potentiellement journalisé par des proxys/outils de debug
  côté client.
- `AccountController::allAccounts` et `allAdministrateurAccounts` renvoient également `pwd` en clair pour tous
  les comptes d'une école (réservé à ADMIN, mais aggrave l'impact des points 1/2 : ADMIN n'a même pas besoin
  du dump pour voir tous les mots de passe).

**Impact :** un unique dump de base de données, une capture réseau, un outil de débogage navigateur ou un
accès ADMIN compromis suffit à récupérer les mots de passe de tous les comptes en clair, immédiatement
réutilisables (aucun hachage à casser).

**Recommandation :**
1. Hacher les mots de passe avec `Hash::make()` (bcrypt/argon2, déjà disponible via Laravel) et migrer
   `connect()`/`updateAccount()`/`adminUpdateAccount()` vers `Hash::check()`.
2. Ajouter `protected $hidden = ['pwd'];` sur `Account` immédiatement, indépendamment de la migration du
   hachage — geste à coût nul qui arrête la fuite du mot de passe dans les réponses API dès aujourd'hui.
3. Prévoir une migration de données (hachage à la volée au prochain login, ou script de migration en masse)
   puisque les mots de passe actuels ne peuvent pas être re-hachés sans les connaître en clair au moment de la
   bascule.

### 4. Injection SQL par concaténation (motif systémique)

**Preuve :** 294 appels `DB::select`/`DB::statement`/... à travers les contrôleurs interpolent des variables
directement dans la chaîne SQL plutôt que d'utiliser des requêtes préparées avec liaison de paramètres.
Exemple directement exploitable, `app/Http/Controllers/StaffController.php:70-76`
(`modifyStaff`, route `POST /api/staffs/modifyStaff`, groupe `role:ADMIN`) :

```php
$request->validate([
    'grade' => 'nullable|string',   // <- accepte n'importe quelle chaîne, y compris des métacaractères SQL
    ...
]);
...
DB::select("UPDATE staff SET grade = '$grade', region = '$region', ...
WHERE staff_id  = $staff_id");
```

La règle Laravel `'nullable|string'` ne filtre **aucun** caractère spécial SQL (guillemets, `--`, `UNION`,
etc.). Un attaquant disposant d'un compte ADMIN valide (ou d'un jeton ADMIN volé/rejoué grâce à l'un des
points ci-dessus) peut donc injecter du SQL arbitraire via le champ `grade` (ou tout autre champ de la même
méthode). Le même motif se retrouve dans `StudentController.php:43` (`UPDATE student SET st1 = '$father', ...`),
`StudentController.php:1002`, et dans une grande partie de `ClasseController.php`, `SubjectController.php`,
`MyHelper.php`, etc.

**Facteur aggravant :** le bloc `catch` de ces mêmes méthodes renvoie systématiquement
`'message' => 'Staff update failed: ' . $e->getMessage()` (voir point 12) — c'est-à-dire le message d'erreur
SQL brut de PDO. Une injection provoquant une erreur de syntaxe reflète littéralement la requête/l'erreur SQL
au client, ce qui transforme une simple exploration en **injection SQL basée sur l'erreur**, triviale à
automatiser (sqlmap, etc.) sans même avoir besoin d'une exfiltration en aveugle.

**Impact :** lecture/modification/suppression arbitraire de toutes les données de la base de connexion
active (élèves, notes, comptes...), et selon les privilèges du compte MySQL configuré par école, possible
accès à d'autres bases sur le même serveur physique.

**Recommandation :**
1. Remplacer systématiquement les requêtes interpolées par des requêtes préparées :
   `DB::update("UPDATE staff SET grade = ? WHERE staff_id = ?", [$grade, $staff_id])` ou l'équivalent Eloquent
   (`Staff::find($staff_id)->update([...])`), qui existe déjà et couvrirait ce cas sans SQL brut.
2. Ce chantier est trop large pour un correctif ponctuel : prioriser d'abord les endpoints acceptant des
   chaînes libres (texte utilisateur) plutôt que des identifiants numériques, en commençant par
   `StaffController::modifyStaff` et `StudentController` (pères/mères, motifs de renvoi, décisions).
3. À court terme et en complément (pas un substitut), envisager un WAF/reverse-proxy pour réduire la fenêtre
   de risque pendant la remédiation.

### 5. `APP_DEBUG=true` en environnement de production

**Preuve (`.env`) :** `APP_ENV=production` avec `APP_DEBUG=true`.

**Impact :** toute exception non interceptée (ou remontant jusqu'au handler Laravel) affiche une page
Ignition/Whoops avec la trace de pile complète, les valeurs des variables locales à chaque frame, la requête
SQL fautive, et des chemins serveur. Combiné au point 4, cela transforme n'importe quelle erreur SQL en fuite
d'informations extrêmement détaillée (et dans certains cas, en fuite indirecte de secrets si ceux-ci
transitent par une variable locale au moment du crash).

**Recommandation :** passer `APP_DEBUG=false` en production dès que possible ; c'est un changement de
configuration à coût nul et à impact immédiat. Configurer `LOG_LEVEL`/`LOG_CHANNEL` pour continuer à capturer
les erreurs côté serveur (fichier de log, pas réponse HTTP).

### 6. Point d'entrée dupliqué hors de `public/` — à confirmer d'urgence sur le serveur réel

**Preuve :** un fichier `index.php` (racine du dépôt) identique au `public/index.php` standard de Laravel est
présent à la racine du projet, accompagné d'un `.htaccess` (racine) qui est une copie du `.htaccess` standard
de `public/` — il ne contient **aucune** directive de blocage des fichiers sensibles (`.env`, `.git`, etc.),
seulement la réécriture vers `index.php`. Sa règle `RewriteCond %{REQUEST_FILENAME} !-f` signifie que tout
fichier existant physiquement (donc `.env`, `composer.json`, `.git/config`, `storage/logs/laravel.log`,
`database/*`, `vendor/*`) est servi **tel quel** par Apache plutôt que d'être routé vers Laravel, dès lors que
le `DocumentRoot` Apache pointe vers la racine du projet plutôt que vers `public/`.

Cette configuration est cohérente avec un déploiement XAMPP « à plat » (le dossier projet placé directement
sous `htdocs/`, comme c'est le cas de la copie de développement analysée), un schéma de déploiement qui, s'il
est reproduit sur le serveur de production, exposerait :
- `.env` en clair : identifiants MySQL de **toutes** les écoles, `JWT_SECRET`, identifiants SMTP.
- `.git/` (si présent sur le serveur de prod) : reconstruction de tout l'historique du dépôt.
- `storage/logs/laravel.log`, `database/`, `vendor/`.

**Cette hypothèse n'a pas pu être vérifiée depuis cet environnement d'analyse (pas d'accès réseau sortant).**
À exécuter en priorité absolue depuis un poste ayant accès à Internet :

```bash
curl -I https://dmsschoolmanager.com/.env
curl -I https://dmsschoolmanager.com/composer.json
curl -I https://dmsschoolmanager.com/.git/config
curl -I https://dmsschoolmanager.com/storage/logs/laravel.log
```

Un code `200` sur l'une de ces URLs confirme la vulnérabilité et impose une action immédiate : (1) couper
l'accès public le temps de corriger, (2) considérer `.env` comme compromis et régénérer **tous** les secrets
qu'il contient (mots de passe MySQL par école, `JWT_SECRET`, identifiants SMTP), (3) reconfigurer le
`DocumentRoot` Apache pour qu'il pointe exclusivement vers `public/`, (4) supprimer le `index.php` et le
`.htaccess` dupliqués à la racine du projet une fois le `DocumentRoot` corrigé.

---

## 🟠 Élevé

### 7. Contrôle d'accès manquant côté serveur pour les rôles VP (CENSEUR) et SG (IDOR)

**Preuve (`routes/api.php`, commentaires du code lui-même) :**

```php
// CENSEUR is scoped client-side to only the classes assigned to them (classe_year.vp_id) - see
// dms_acad_react's ClasseManager/SubjectClasseManager/StudentManager, which filter the classe list
// they fetch before these write actions are ever reachable. The backend does not re-verify classe_id
// ownership here, matching the same trust model already used for SG's Discipline scoping below.
Route::middleware(['jwt.auth', 'role:ADMIN,CENSEUR'])->group(function () {
    Route::post('/classes/updateManyClasses', ...);
    Route::post('/students/deleteStudents', ...);
    Route::post('/students/saveStudents', ...);
    ...
});
```

Le middleware `role:ADMIN,CENSEUR` vérifie uniquement que l'utilisateur *a* le rôle CENSEUR (VP) ou SG — pas
qu'il est bien le VP/SG *assigné à la classe ciblée* par la requête. Cette vérification n'existe que côté
frontend React (filtrage de la liste des classes affichées), qui n'est pas une frontière de sécurité :
n'importe quel appel direct à l'API (Postman, script, DevTools) contourne ce filtrage.

**Impact :** un compte CENSEUR ou SG légitime (mais malveillant, ou dont le jeton a fuité) peut modifier ou
supprimer des élèves, notes, ou entrées de discipline de **n'importe quelle classe**, y compris celles qui ne
lui sont pas assignées — cassant l'isolation par périmètre qui est pourtant la fonctionnalité même de ces
rôles.

**Recommandation :** dans chaque contrôleur concerné (`ClasseController`, `StudentController`), avant
d'exécuter l'action, vérifier que `classe.vp_id` (ou `classe.sg_id` pour SG) correspond au `user_id` porté par
le payload JWT du compte appelant (sauf si le rôle est ADMIN). C'est une vérification simple à ajouter
(quelques lignes par méthode) qui ferme une classe entière de contournements.

### 8. Routes `/test/*` destructrices accessibles à tout utilisateur authentifié

**Preuve (`routes/api.php`, groupe `jwt.auth` sans restriction de rôle) :** les routes
`/test/deleteManyStudClasse`, `/test/deleteManyStudClassePOST`, `/test/deleteStudClasse`,
`/test/alterStaff`, `/test/updateStudentClasseStructure`, `/test/prepareNewYear`, `/test/add2627` sont
accessibles à **n'importe quel rôle connecté** (élève, parent, etc.). `TestController::alterStaff()` et
`updateStudentClasseStructure()`/`prepareNewYear()` (voir `app/Http/Controllers/TestController.php:245,
421, 536`) itèrent sur la liste de **toutes** les écoles configurées et exécutent des `ALTER TABLE` ou des
mutations en masse sur chacune d'elles, sans confirmation ni restriction de rôle.

**Impact :** un compte élève ou parent authentifié (le rôle le plus faible du système) peut déclencher des
opérations destructrices ou des modifications de schéma sur les bases de **toutes** les écoles clientes de la
plateforme, pas seulement la sienne.

**Recommandation :** retirer ces routes de `routes/api.php` en production (elles portent le nom `test/*`,
signe qu'elles n'ont jamais été prévues pour un usage utilisateur final), ou à défaut les protéger par
`role:ADMIN` et les scoper à la connexion demandée plutôt qu'à la liste complète des écoles.

### 9. Cookie `refresh_token` sans attribut `Secure`

**Preuve (`AccountController.php:300, 349`) :**
```php
->withCookie('refresh_token', $refreshToken, $refresh_token_duration, null, null, false, true, false, 'Strict')
```
Le 6ᵉ paramètre (`secure`) vaut `false`, alors que `APP_URL=https://dmsschoolmanager.com` indique un service
exclusivement en HTTPS.

**Impact :** le navigateur peut transmettre ce cookie sur une connexion HTTP non chiffrée (redirection mal
configurée, sous-domaine servi en HTTP, attaque de rétrogradation), exposant le jeton de rafraîchissement — qui
permet à lui seul de regénérer des jetons d'accès pendant 7 jours — à une interception réseau.

**Recommandation :** passer ce paramètre à `true` (ou utiliser `Cookie::make(...)->secure(true)` /
`config(['session.secure' => true])` selon le style du projet).

---

## 🟡 Moyen

### 10. Pas de limitation de débit dédiée sur la connexion

`routes/api.php` ne définit aucun `throttle` spécifique sur `POST /accounts/connect` ; seul le groupe de
middleware `api` par défaut de Laravel s'applique (`throttle:api`, 60 requêtes/minute par défaut). Combiné aux
mots de passe en clair (point 3) et à l'absence de verrouillage de compte après échecs répétés, cela laisse
une marge confortable pour du bourrage d'identifiants (credential stuffing) ou une attaque par force brute
ciblée. **Recommandation :** ajouter un throttle dédié et plus strict sur `/accounts/connect` (ex.
`throttle:5,1` par IP+login) et envisager un verrouillage temporaire de compte après N échecs.

### 11. Traversée de chemin via le paramètre `connection`

`connection` n'est validé nulle part par rapport à la liste blanche des connexions déclarées dans
`config/database.php` (comportement documenté comme connu dans `CLAUDE.md`). Deux endroits l'utilisent
directement dans un chemin de fichier sans le nettoyer :

- `SchoolInfoController::upload` (ligne 564) : `$request->image->move(public_path("images/$connection/logo"),
  ...)` — un `connection` contenant `../` peut faire écrire le fichier logo (image valide requise) en dehors
  du dossier prévu, n'importe où sur le disque accessible en écriture au process PHP.
- `BackupController::backupDB` : `$file_name = "smd_db_$connection" . date(...) . '.sql'` puis
  `fopen($file_name, 'w+')` — même absence de nettoyage sur un chemin de fichier généré côté serveur.

**Recommandation :** valider `connection` contre la liste des clés définies dans `config/database.php`
(`in_array($connection, array_keys(config('database.connections')))`) dès l'entrée de chaque contrôleur, ou
centraliser cette vérification dans un middleware partagé — cela corrige d'un coup ce point et réduit la
surface de l'erreur générique documentée dans `CLAUDE.md` ("mauvais nom de connexion → échec DB").

### 12. Messages d'exception bruts renvoyés au client (300 occurrences)

La quasi-totalité des contrôleurs renvoient `'message' => $e->getMessage()` (ou équivalent) au client dans
leurs blocs `catch`. Indépendamment du point 4 (où cela devient un vecteur d'injection SQL basée sur l'erreur),
c'est en soi une fuite d'information systémique : requêtes SQL, noms de colonnes/tables, chemins de fichiers,
parfois des détails de configuration. **Recommandation :** logguer l'exception complète côté serveur
(`Log::error($e)`) et ne renvoyer au client qu'un message générique + un identifiant de corrélation, sauf en
environnement de développement.

### 13. CRUD `Account` mort avec assignation de masse illimitée

`AccountController::store/update/index/show/destroy` (lignes 606-689) ne sont référencés dans aucune route
active, mais existent toujours dans le contrôleur et utilisent `Account::create($request->all())` /
`$Account->update($request->all())` — une assignation de masse totale, qui permettrait à n'importe quel
appelant de positionner directement le champ `type` (donc de s'auto-attribuer le rôle ADMIN) si ces méthodes
étaient un jour reconnectées à une route par erreur. **Recommandation :** supprimer ce code mort, ou au
minimum remplacer `$request->all()` par `$request->only($fillable_autorisés)`.

---

## 🟢 Faible / durcissement

### 14. Configuration CORS large

`config/cors.php` autorise `allowed_methods => ['*']` et `allowed_headers => ['*']` avec
`supports_credentials => true`. Le risque est atténué par une liste blanche explicite d'origines (`netlify.app`,
`dmsacad.com`, `capacitor://localhost`, etc.), mais resserrer `allowed_methods`/`allowed_headers` aux valeurs
réellement utilisées par le frontend réduirait la surface en cas d'ajout futur d'une origine trop permissive.

### 15. Liste des écoles accessible sans authentification

`GET /api/configs/allSchools` est volontairement public (commentaire "THIS API doesn't need Authentication")
pour peupler l'écran de connexion. Cela expose la liste des noms de connexion internes, ce qui facilite la
reconnaissance pour les points 4 et 11 (un attaquant n'a pas besoin de deviner les valeurs de `connection`, il
lui suffit d'appeler cette route). Risque faible en soi, mais à garder en tête : ne pas exposer davantage que
le strict nécessaire à l'affichage (nom, pas les clés de connexion techniques si elles diffèrent du nom
affiché).

---

## Plan d'action priorisé

1. **Aujourd'hui :** supprimer `public/db_backup/sm_db2.sql` du serveur de production (point 1), vérifier
   l'exposition de `.env`/`.git` (point 6) et couper l'accès public si confirmé, passer `APP_DEBUG=false`
   (point 5).
2. **Cette semaine :** restreindre `backupDB` à `role:ADMIN` (point 2), ajouter `$hidden = ['pwd']` sur
   `Account` (point 3), retirer ou protéger les routes `/test/*` (point 8), corriger l'attribut `Secure` du
   cookie de rafraîchissement (point 9).
3. **Ce mois-ci :** entamer la migration des mots de passe vers un hachage (point 3), corriger les
   injections SQL les plus exposées en commençant par `StaffController::modifyStaff` et les endpoints
   acceptant du texte libre (point 4), ajouter la vérification de propriété de classe pour CENSEUR/SG
   (point 7), valider `connection` contre la liste blanche des connexions (point 11).
4. **En continu :** remplacer progressivement `'message' => $e->getMessage()` par une journalisation
   serveur + message générique (point 12), nettoyer le code mort à risque (point 13), resserrer CORS
   (point 14).

Après remédiation des points critiques (1 à 6), une régénération complète des secrets (`JWT_SECRET`, mots de
passe MySQL de chaque école, identifiants SMTP) et une invalidation de tous les jetons actifs est recommandée,
ces secrets devant être considérés comme potentiellement déjà compromis au moment de cet audit.
