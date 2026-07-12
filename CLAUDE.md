# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project overview

Laravel 11 REST API backend for a multi-school academic management system (DMS ACAD). It serves CRUD
operations for schools, staff, students, classes, subjects, marks, absences, etc. The app is served by
XAMPP/Apache at `http://localhost/dmsacad_backend_dev`, with all routes prefixed under `/api` (see
`routes/api.php`, mounted via `bootstrap/app.php`).

Security hardening (JWT auth + role-based access) is in progress and currently only covers a couple of
endpoints — treat most routes in `routes/api.php` as still unprotected legacy endpoints when asked to add
auth to "an endpoint like the others already secured."

## Commands

This project runs under XAMPP, not `php artisan serve`. Two PHP binaries exist on this machine and they are
**not equivalent**:

- `C:\xampp\php\php.exe` — bundled with XAMPP, has `pdo_mysql` enabled, and is what Apache actually executes.
- The `php` on PATH (`C:\php83\php.exe`) — a separate install **without `pdo_mysql`**, so any DB query run
  through it (`php artisan tinker`, `php artisan migrate`, etc.) fails with `could not find driver` even
  though the same code works fine when served by Apache.

Always use the XAMPP binary for anything that touches the database:

```bash
"/c/xampp/php/php.exe" artisan tinker --execute="..."
"/c/xampp/php/php.exe" artisan migrate
```

Other common commands:

```bash
composer install
npm install && npm run dev      # vite, front-end assets only — this is an API-only backend, not much here
"/c/xampp/php/php.exe" artisan test                 # runs tests/Unit and tests/Feature (currently just Laravel's example stubs, no real coverage)
"/c/xampp/php/php.exe" vendor/bin/pint              # Laravel Pint code style fixer
```

Reproduce/debug a specific API endpoint directly with curl (mirrors what Postman does) rather than guessing:

```bash
curl -s -i "http://localhost/dmsacad_backend_dev/api/<route>?connection=mysql" \
  -H "Authorization: Bearer <token>"
```

## Architecture

### Multi-tenancy: one database per school, chosen at request time

There is no single application database. `config/database.php` defines one named MySQL connection **per
school** (`CES_DE_DABAYE`, `LYCEE_DE_MERI`, `LB_BOGO`, ...), each with its own host/db/user/password, plus a
generic `mysql` connection (`sm_db2` per `.env`) that holds cross-school data — `account`, `staff`,
`administrateur`, `student` login linkage, etc. Every school database replicates the same schema
(`school_year`, `basic_school_config`, `classe`, `student`, `staff`, `subject`, ...).

Controllers select the tenant database per-request by reading a `connection` request param/route param and
switching Laravel's default connection before querying:

```php
$connection = $request->input("connection");
config(["database.default" => $connection]);
```

This pattern appears ~190 times across nearly every controller method (grep `database.default` in
`app/Http/Controllers` to see the full spread) — it is the core architectural idiom of this codebase, not an
incidental detail. When adding a new endpoint, follow the same pattern rather than introducing a different
tenancy mechanism. `$connection` must match a key defined in `config/database.php`; there's no validation of
unknown values, so a bad connection name from the client surfaces as a DB connection failure inside the
controller's catch block.

### Auth: custom JWT, not Sanctum

`laravel/sanctum` is installed but unused for API auth. Auth is hand-rolled with `firebase/php-jwt`:

- `AccountController::login` validates `login`/`pwd`/`connection`, looks up `Account` (passwords are **plain
  text in the `pwd` column right now** — hashing is a known, deliberately deferred TODO, not an oversight),
  resolves a role via `MyHelper::findRole($account->type)`, and issues a short-lived access token (JWT,
  `ACCESS_TOKEN_DURATION` env, default 3600s) plus a refresh token set as an httpOnly cookie
  (`REFRESH_TOKEN_DURATION` env, default 7 days).
- `AccountController::refresh` reads the `refresh_token` cookie, verifies it, and mints a new access token.
- `app/Http/Middleware/JwtMiddleware.php` (alias `jwt.auth`, registered in `bootstrap/app.php`) decodes the
  `Authorization: Bearer` token and stashes the payload on `$request->attributes` under `auth_payload`. It
  does not check roles.
- `app/Http/Middleware/RoleMiddleware.php` (alias `role`) reads `auth_payload` and checks `role` against the
  roles passed as middleware args, e.g. `role:ADMIN,TOP_MANAGEMENT`. It must run **after** `jwt.auth` in the
  same middleware group/stack (it does not decode the token itself).
- Account `type` (int) → role string mapping lives in `MyHelper::findRole()`: `1=ADMIN, 2=TOP_MANAGEMENT,
  3=SG, 4=BURSAR, 5=TEACHER, 6=PARENT, 7=STUDENT, 8=CENSEUR`.

To protect a new route, wrap it the same way the one existing example in `routes/api.php` does:

```php
Route::middleware(['jwt.auth', 'role:ADMIN'])->group(function () {
    Route::get('/modules/whatever', [SomeController::class, 'method']);
});
```

### Error convention: HTTP 500 means "an exception was swallowed"

Many controller methods (see `grep -rn "response()->json([], 500)"` — dozens of hits across
`ClasseController`, `StudentController`, `StaffController`, `SubjectController`, etc.) wrap their body in
`try { ... } catch (Exception $e) { return response()->json([], 500); }`, usually with the exception message
discarded or only echoed (which doesn't work over a JSON API response anyway). This used to be an ad hoc 
correct `500` (Internal Server Error), paired with a `//ERROR OCCURS` comment —  
Either way, the real cause is never in the HTTP layer — it's an exception (often a null-property access, since
PHP promotes those to a catchable `ErrorException` in this app) inside the corresponding controller method.
Since these catches don't log, temporarily add `Log::error($e->getMessage())` in the relevant catch block to
find the real cause, then remove it once done.

### `MyHelper` (app/Http/Controllers/MyHelper.php)

A large static utility class (despite living under `Controllers`, not `Helpers`) used across controllers for:
- Cascading deletes across the school-year/section relational graph (deleting a student, staff, or class
  requires manually clearing references across ~10+ related tables — `deleteAStudent`, `deleteAStaff`,
  `deleteAClasse`, `deleteClasses`, etc. — because there are no DB-level cascade constraints).
- Lookups by name/year (`getSchoolYearID`, `getSectionID`, `getSectionYearID2`).
- Role name mapping (`findRole`).

Most of its methods run raw `DB::select(...)` with interpolated variables rather than Eloquent — when
touching this file, follow the existing style rather than converting to query builder/Eloquent unless asked.

### Models

`app/Models` contains Reliese-generated Eloquent models (see `reliese/laravel` dev dependency) mirroring the
shared per-school schema. `app/Models/SmModels bk` and `app/Models/app_default_models_BK` are backup/legacy
copies — do not treat code found only there as authoritative or currently in use.

### API docs

`darkaonline/l5-swagger` + `zircote/swagger-php` are installed and configured (`config/l5-swagger.php`) but
no `@OA\*` annotations exist in the codebase yet — Swagger generation is not currently wired up to anything.
