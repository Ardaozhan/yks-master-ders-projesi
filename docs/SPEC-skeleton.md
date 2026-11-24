# SPEC: YKS MASTER skeleton (slice 1)

Source documents: `YKS_Gereksinim_Analizi.docx` (FR-01..45, NFR-01..10) and
`YKS_MASTER_Kullanici_Arayuzu_ve_Kullanilabilirlik.docx` (OneDrive, `internet programlama`).
Goal: a running app skeleton by 2026-11-25 so the midterm deck (27.11) can show real
routing, backend communication and login validation. Final delivery: 2026-12-04.

## Stack (fixed by the project proposal)

- Plain PHP 8.2 (XAMPP), no framework. MySQL/MariaDB via PDO. HTML, CSS, vanilla JS.
- Dev server: `php -S localhost:8000 -t public public/index.php` (Apache/XAMPP also works via `public/.htaccess`).
- No Composer dependency required. Tests run with `php tests/run.php` (tiny in-repo runner).

## Directory layout

```
public/            index.php (front controller), .htaccess, assets/css, assets/js
src/
  Core/            Router.php, Database.php, View.php, Session.php, Csrf.php, Validator.php, Auth.php
  Controllers/     HomeController, AuthController, CatalogController, PlanController (stub), AdminController (stub)
  Repositories/    UserRepository, SubjectRepository, TopicRepository
views/             layout.php, partials/, home, auth/login, auth/register, catalog/index, plan/today (stub), errors/403, errors/404
config/            config.example.php (committed), config.php (gitignored, read by Database)
database/          schema.sql, seed.sql (TYT/AYT subjects + topics, no users), create_admin.php (CLI, password from STDIN)
tests/             run.php + *Test.php
```

## Routing

Front controller `public/index.php` -> `Router` maps `METHOD + path` to `Controller@method`.
Route table in one place (`src/routes.php`). Middleware per route: `guest`, `auth`, `role:admin`, `csrf` (all POST).

| Method | Path | Handler | Guard | FR |
|---|---|---|---|---|
| GET | / | Home@index | - (logged-in -> /bugun) | - |
| GET/POST | /kayit | Auth@register | guest | FR-01 |
| GET/POST | /giris | Auth@login | guest | FR-02 |
| POST | /cikis | Auth@logout | auth | FR-05 |
| GET | /katalog | Catalog@index (?tur=TYT|AYT&ders=id&q=) | auth | FR-12,13,15 |
| GET | /api/konular?ders=id | Catalog@topicsJson | auth | FR-13 (fetch for dependent select) |
| GET | /bugun | Plan@today (stub page) | auth, role:student | FR-06 (later) |
| GET | /yonetim | Admin@index (stub page) | auth, role:admin | FR-39 |

Unknown path -> 404 view. Role mismatch -> 403 view (FR-04, NFR-03).

## Database (all six entities now, so later slices only add code)

- `users`: id, full_name, email UNIQUE, password_hash, role ENUM('student','admin'), student_type ENUM('grade12','graduate') NULL, created_at, updated_at
- `subjects`: id, exam_type ENUM('TYT','AYT'), name, is_active, sort_order
- `topics`: id, subject_id FK, name, sort_order, is_active
- `study_plans`: id, user_id FK, topic_id FK, plan_date DATE, target_minutes, status ENUM('planned','in_progress','completed'), created_at, updated_at
- `study_records`: id, plan_id FK UNIQUE, actual_minutes, studied_on DATE, note VARCHAR(255) NULL, created_at, updated_at
- `student_topic_progress`: user_id, topic_id (composite PK), status ENUM('not_started','in_progress','done'), updated_at

InnoDB, utf8mb4_turkish_ci, FKs with RESTRICT (FR-43: catalog rows are deactivated, never deleted).

## Login validation

- Register: full_name 2-100 chars, valid email, password min 8 chars, student_type required. Server-side validation is final (NFR-05); errors shown next to fields, other inputs kept.
- Passwords: `password_hash(PASSWORD_DEFAULT)` / `password_verify` (NFR-04).
- Login failure: one generic message ("E-posta veya parola hatalı"), no user enumeration (NFR-07).
- On login: `session_regenerate_id(true)`; session cookie httponly + samesite=Lax.
- CSRF token on every POST form; invalid token -> 403.
- Simple brute-force brake: after 5 failed attempts per session, 30 s wait.

## Security rules

- All SQL via PDO prepared statements, `ATTR_EMULATE_PREPARES=false` (NFR-06).
- All output escaped through one `e()` helper (htmlspecialchars, ENT_QUOTES, UTF-8).
- DB credentials only in `config/config.php` (gitignored) (NFR-08).
- Errors: generic message to user, details to `storage/logs/app.log` (gitignored).

## UI

- Turkish UI text. Mobile-first responsive layout, nav: Bugün, Katalog, (later Haftalık Özet, Konu İlerlemesi, Geçmiş), Profil; Yönetim only for admin.
- Labels visible on every field, status never by color alone, visible focus ring.
- Catalog page: TYT/AYT tabs, subject list, topic list for selected subject, search box, empty-result message with "Filtreyi temizle".

## Tests (tests/run.php)

Router (match, 404, method mismatch), Validator (register rules), Csrf (token verify), Auth (hash/verify, role check).
DB-backed repository tests run only when `config/config.php` exists.

## Out of scope for slice 1

Plan CRUD, status/time records, weekly summary, topic progress, history, admin CRUD, profile edit.
