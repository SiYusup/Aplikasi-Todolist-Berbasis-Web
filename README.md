# Aplikasi To-Do List — PHP Native Clean MVC

Aplikasi web To-Do List dengan autentikasi, manajemen tugas & kategori, dashboard analitik,
pengingat otomatis, dan halaman pengaturan (profil, tema gelap/terang, bahasa ID/EN).

Dibangun dengan **PHP native + arsitektur Clean MVC berlapis** (tanpa framework PHP),
PostgreSQL, dan frontend Vanilla + CDN. Cocok untuk belajar alur web app end-to-end:
routing → controller → service → repository → database → view.

## ✨ Fitur Utama

| Fitur | Keterangan |
|---|---|
| Autentikasi | Register & login (username + password), sesi split-token (`selector:validator`), proteksi brute-force (lockout 5 menit), audit log |
| Tugas (CRUD) | Buat, lihat detail, edit, hapus, tandai selesai — semua berbasis **UUID** |
| Kategori (CRUD) | Nama + warna via color picker (hex); badge kategori berwarna di kartu tugas |
| History | Tugas yang selesai otomatis pindah dari Tugas ke halaman History (bisa dibuka lagi / hapus permanen) |
| Dashboard | Kartu Total/Pending/Selesai, donut chart (ApexCharts), tabel per kategori |
| Pengingat | Polling tiap 1 menit → toast saat tugas mendekati `due_date`; otomatis ditandai agar tidak berulang |
| Pengaturan | Profil + statistik, ubah username/password, tema terang/gelap, bahasa Indonesia/English, default pengingat, ekspor JSON, hapus tugas selesai, logout |
| API + Docs | REST API JSON + spesifikasi OpenAPI/Swagger siap import |

## 🧰 Tech Stack

**Backend** — PHP 8.2+ · `bramus/router` (routing) · `catfan/medoo` (query builder, dipakai di Repository)
· `vlucas/valitron` (validasi, dipakai di Service) · `vlucas/phpdotenv` (config)
· `ramsey/uuid` (UUID) · `phpunit/phpunit` (testing) · PostgreSQL (database)

**Frontend (CDN)** — Tailwind CSS · FrankenUI 1 (komponen `uk-*`, tema zinc) · Alpine.js (state)
· Axios (HTTP) · ToastifyJS (notifikasi) · ApexChartsJS (grafik) · Lucide Icons

## 🏗️ Arsitektur

Setiap request mengalir satu arah, satu lapis satu tanggung jawab:

```
Browser (Alpine + Axios)
   │  HTTP (halaman web / /api/* JSON)
   ▼
public/index.php  (bramus/router: definisi route web + API)
   │
   ├─► PageController ──► View (layout.php + pages/*.php)
   │
   └─► *Controller ──► *Service ──► *Repository ──► PostgreSQL (via Medoo)
        (HTTP I/O)     (bisnis +     (query SQL
                        Valitron)     saja)
```

| Lapis | Folder | Boleh melakukan |
|---|---|---|
| Controller | `app/Controller/` | Baca request, panggil Service, tulis response. **Tanpa** SQL & validasi bisnis |
| Service | `app/Service/` | Business logic + validasi Valitron, panggil Repository |
| Repository | `app/Repository/` | Query PostgreSQL via Medoo. **Tanpa** validasi |
| Domain | `app/Domain/` | Entity PHP (`User`, `Task`, `Category`) |
| Middleware | `app/Middleware/` | `AuthMiddleware`: baca cookie split-token, guard halaman & API |
| View | `app/View/` | `layout.php` + `pages/*.php` (Alpine.js + FrankenUI) |

## ✅ Prasyarat

- PHP **8.2+** dengan ekstensi `pdo_pgsql`, `mbstring`, `openssl`
- Composer 2.x
- PostgreSQL 13+ (username & password sesuai `.env`)

## 🚀 Instalasi

```bash
# 1. Clone repo
git clone <url-repo-anda> aplikasi-todolist
cd aplikasi-todolist

# 2. Install dependensi PHP
composer install

# 3. Siapkan database (ganti user/pass sesuai milikmu)
createdb -U postgres todolist
psql -U postgres -d todolist -f database/schema.sql

# 4. Konfigurasi environment
cp .env.example .env
# lalu sesuaikan isi .env bila perlu:
# DB_HOST=localhost  DB_PORT=5432  DB_NAME=todolist
# DB_USER=postgres   DB_PASS=ucup

# 5. Jalankan server development
php -S localhost:8000 -t public public/index.php

# 6. Buka di browser
# http://localhost:8000  → register akun dulu, lalu login
```

> **Catatan:** bentuk perintah `php -S ... -t public public/index.php` penting —
> file `public/index.php` bertindak sebagai router (meneruskan request API ke
> Bramus Router sekaligus melayani file statis di `public/assets`).

## ⚙️ Konfigurasi (`.env`)

| Key | Default | Keterangan |
|---|---|---|
| `DB_HOST` / `DB_PORT` / `DB_NAME` / `DB_USER` / `DB_PASS` | `localhost` / `5432` / `todolist` / `postgres` / `ucup` | Koneksi PostgreSQL |
| `SESSION_COOKIE` | `todolist_session` | Nama cookie sesi split-token |
| `SESSION_DAYS` / `REMEMBER_DAYS` | `7` / `30` | Masa berlaku sesi biasa / "ingat saya" |
| `APP_URL` | `http://localhost:8000` | Base URL aplikasi |

## 🔄 Alur Aplikasi

### Autentikasi
1. **Register** (`POST /api/auth/register`) → user + baris `user_security` dibuat, event `register` dicatat.
2. **Login** (`POST /api/auth/login`) → `password_verify()`; gagal → `failed_attempts` +1 dan event
   `login_failed`; ≥ 5x gagal → akun dikunci (`lockout_until`, HTTP 423). Sukses → token
   `selector:validator` dibuat (`user_sessions`), cookie HttpOnly `todolist_session` di-set.
3. Setiap request terproteksi dicek `AuthMiddleware`: cookie valid + belum kedaluwarsa + validator cocok.
4. **Logout** menghapus sesi aktif + cookie (dengan dialog konfirmasi di UI).

### Siklus Tugas
```
Tugas Baru (modal) → status=pending → tampil di halaman Tugas
   → Tandai selesai → completed_at diisi → PINDAH ke halaman History
   → Buka lagi → kembali pending → kembali ke halaman Tugas
   → Hapus → konfirmasi → terhapus permanen
```

### Pengingat
Header layout menjalankan `setInterval` 60 detik → `GET /api/tasks/reminders`
(tugas `pending` dengan `due_date <= now + reminder_minutes` dan `is_reminded = false`)
→ ToastifyJS + backend menandai `is_reminded = TRUE` agar tidak berulang.

### Skema Database
`users` · `user_security` (brute-force) · `user_sessions` (split-token) ·
`audit_logs` (forensik) · `categories` (warna hex badge) · `tasks`
DDL lengkap: [`database/schema.sql`](database/schema.sql) ·
Spesifikasi awal: [`documents/PRD.md`](documents/PRD.md)

## 📚 Dokumentasi API

Spesifikasi OpenAPI 3.0: [`documents/openapi.yaml`](documents/openapi.yaml) —
buka [`documents/swagger.html`](documents/swagger.html) di browser untuk Swagger UI,
atau import YAML-nya ke <https://editor.swagger.io>.

Ringkasan endpoint (semua memakai UUID, butuh cookie sesi kecuali Auth dasar):

| Method | Endpoint | Keterangan |
|---|---|---|
| POST | `/api/auth/register` | Registrasi |
| POST | `/api/auth/login` | Login (set cookie) |
| POST | `/api/auth/logout` | Logout |
| GET | `/api/auth/me` | Profil user login |
| GET/POST | `/api/tasks` | List (filter `status`, `category_uuid`, `page`, `limit`) / buat |
| GET | `/api/tasks/reminders` | Tugas mendekati due date |
| GET/PUT/DELETE | `/api/tasks/{uuid}` | Detail / edit / hapus |
| PATCH | `/api/tasks/{uuid}/complete`, `/reopen` | Selesai / buka lagi |
| GET/POST | `/api/categories` | List / buat |
| GET/PUT/DELETE | `/api/categories/{uuid}` | Detail / edit / hapus (`SET NULL` ke tugas) |
| GET | `/api/dashboard/stats` | Total/pending/completed + per kategori |
| PUT | `/api/user/username`, `/api/user/password` | Ubah username/password (wajib password lama) |

## 🧪 Testing

```bash
vendor/bin/phpunit
```

Tes (PHPUnit 11, 23 test / 60 assertions) mencakup Service + Repository dengan database
nyata: register/login/lockout, split-token, ubah username/password, CRUD kategori
(termasuk warna hex), CRUD tugas, complete/reopen, reminders, dan statistik dashboard.
Kredensial DB tes diambil dari environment (`DB_*`, default sama seperti `.env`).

## 🗂️ Struktur Proyek

```
├── app/
│   ├── Config/       Database.php (Medoo + Dotenv)
│   ├── Controller/   Auth, Task, Category, User, Page
│   ├── Domain/       User, Task, Category (entity)
│   ├── Helper/       Response.php (JSON helper)
│   ├── Middleware/   AuthMiddleware.php (split-token guard)
│   ├── Repository/   query Medoo per tabel
│   ├── Service/      business logic + validasi Valitron
│   └── View/         layout.php + pages/ (login, register, home,
│                     tasks, categories, history, settings)
├── public/
│   ├── index.php     entry point + definisi route
│   └── assets/app.js toast, i18n ID/EN, palet warna, helper
├── database/schema.sql
├── documents/        PRD.md, openapi.yaml, swagger.html
├── tests/Service/    UserServiceTest, TaskServiceTest, CategoryServiceTest
├── composer.json
└── phpunit.xml
```

## 📄 Lisensi

ISC — lihat `package.json`. Dibuat untuk pembelajaran oleh UCrazy
(finder.ucup@gmail.com).
