# Product Requirements Document (PRD): PHP MVC To-Do List untuk AI Prompting

**Konteks untuk AI (System Prompt):**
Anda adalah Senior Fullstack PHP Developer. Tugas Anda adalah membangun aplikasi web To-Do List berdasarkan spesifikasi di bawah ini. Jangan menggunakan framework seperti Laravel/Symfony. 
**PENTING - ARSITEKTUR:** Anda HARUS menggunakan arsitektur *Clean MVC* terstruktur yang mengacu pada pola *Layered Architecture* (seperti repository `ProgrammerZamanNow/php-login-management` dan `SiYusup/Aplikasi-Pencatat-Barang`). Pisahkan logika menjadi `Controller`, `Service` (Business Logic), `Repository` (Query Database), dan `Domain` (Entity). Gunakan standard *Dependency Injection* sederhana.

---

## 1. Tech Stack
**Backend (PHP 8.2+):**
*   **Architecture:** Layered Native PHP MVC (Controller, Service, Repository)
*   **Database:** PostgreSQL
*   **Routing:** `bramus/router`
*   **Database Abstraction:** `catfan/medoo` (Gunakan di dalam layer `Repository`)
*   **Environment Config:** `vlucas/phpdotenv`
*   **Validation:** `vlucas/valitron` (Gunakan di dalam layer `Service`)
*   **UUID Generator:** `ramsey/uuid`
*   **Testing:** `phpunit/phpunit` (Fokus pengujian pada fungsi Service & Repository)

**Frontend (Vanilla + CDN):**
*   **CSS Framework:** Tailwind CSS + FrankenUI (HTML-first)
*   **Interactivity:** Alpine.js (State management ringan)
*   **Icons:** Lucide Icons (Vanilla JS / CDN)
*   **HTTP Client:** Axios (Untuk pemanggilan API/AJAX)
*   **Notification:** ToastifyJS
*   **Charts:** ApexChartsJS
*   **Confirm Dialog:** Native HTML `<dialog>` tag dikontrol via Alpine.js.

---

## 2. Struktur Direktori yang Diharapkan
```text
/
├── app/
│   ├── Config/        (Inisialisasi Dotenv dan koneksi Medoo PostgreSQL)
│   ├── Controller/    (Hanya menangani HTTP Request/Response, memanggil Service)
│   ├── Domain/        (Class representasi Entity/Tabel dari Database)
│   ├── Middleware/    (AuthMiddleware untuk proteksi route Bramus)
│   ├── Repository/    (HANYA berisi query ke PostgreSQL menggunakan Medoo)
│   ├── Service/       (Core Business Logic, Validasi Valitron, memanggil Repository)
│   └── View/          (File .php untuk HTML/AlpineJS/FrankenUI)
├── public/
│   ├── index.php      (Entry point, inisialisasi bramus/router & dependency injection)
│   └── assets/        (JS, CSS kustom)
├── tests/             (Folder untuk skrip testing PHPUnit)
│   ├── Repository/
│   └── Service/
├── vendor/            (Composer dependencies)
├── .env               (Konfigurasi DB PostgreSQL)
├── phpunit.xml        (Konfigurasi PHPUnit)
└── composer.json
```

---

## 3. Database Schema (PostgreSQL)
*Instruksi untuk AI: Buatkan skema tabel dengan DDL PostgreSQL berikut.*

```sql
CREATE EXTENSION IF NOT EXISTS "uuid-ossp";

-- Role tunggal (Pengguna) - Sederhana dan Aman
CREATE TABLE users (
    id BIGSERIAL PRIMARY KEY,
    uuid UUID NOT NULL DEFAULT uuid_generate_v4() UNIQUE,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    status VARCHAR(20) DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Isolasi Keamanan (Brute-Force Protection)
CREATE TABLE user_security (
    user_id BIGINT PRIMARY KEY REFERENCES users(id) ON DELETE CASCADE,
    failed_attempts SMALLINT DEFAULT 0,
    lockout_until TIMESTAMP NULL,
    last_login_at TIMESTAMP NULL,
    last_login_ip VARCHAR(45) NULL
);

-- Sesi Split-Token (Remember Me & API Protection)
CREATE TABLE user_sessions (
    id BIGSERIAL PRIMARY KEY,
    user_id BIGINT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    selector VARCHAR(12) NOT NULL UNIQUE,
    hashed_validator VARCHAR(64) NOT NULL,
    user_agent TEXT NULL,
    expires_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Audit Log (Forensik)
CREATE TABLE audit_logs (
    id BIGSERIAL PRIMARY KEY,
    user_id BIGINT NULL REFERENCES users(id) ON DELETE SET NULL,
    event VARCHAR(100) NOT NULL,
    ip_address VARCHAR(45) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE categories (
    id BIGSERIAL PRIMARY KEY,
    uuid UUID NOT NULL DEFAULT uuid_generate_v4() UNIQUE,
    user_id BIGINT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    name VARCHAR(50) NOT NULL,
    color VARCHAR(20) DEFAULT 'zinc',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE tasks (
    id BIGSERIAL PRIMARY KEY,
    uuid UUID NOT NULL DEFAULT uuid_generate_v4() UNIQUE,
    user_id BIGINT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    category_id BIGINT NULL REFERENCES categories(id) ON DELETE SET NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT NULL,
    status VARCHAR(20) DEFAULT 'pending', -- pending, completed
    due_date TIMESTAMP NULL,
    reminder_minutes INT DEFAULT 30, 
    is_reminded BOOLEAN DEFAULT FALSE,
    completed_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

---

## 4. Kebutuhan Fungsional & Instruksi AI

**Instruksi Umum untuk AI:** 
Setiap kali membuat fitur baru, buat `Domain`-nya dulu, lalu `Repository` (dengan query Medoo), lalu `Service` (validasi dengan Valitron), dan terakhir `Controller`. Pastikan untuk **menulis skrip PHPUnit** untuk class `Service` dan `Repository` sebelum mengimplementasikan View.

### Fitur 1: Autentikasi (Login & Registrasi)
*   **Spesifikasi:** Hanya menggunakan `username` dan `password`.
*   **AI Note:** Di `UserService`, validasi input menggunakan `Valitron`. Gunakan pola *Split-Token Session* di tabel `user_sessions`. Catat riwayat login/gagal ke `audit_logs` dan `user_security` via `UserRepository`.
*   **Testing:** Buat test PHPUnit untuk mensimulasikan login sukses, password salah, dan akun terkunci sementara (lockout_until).

### Fitur 2: Manajemen Tugas (CRUD Tasks)
*   **Spesifikasi:** Buat, Edit, Hapus, Tandai Selesai.
*   **Frontend Note:** Form Create/Edit menggunakan Modal Alpine.js + FrankenUI. Submit via Axios.
*   **Delete UI:** Gunakan HTML `<dialog>` dikontrol Alpine.js untuk konfirmasi "Apakah Anda Yakin?". Jika "Ya", Axios memanggil route `DELETE /api/tasks/{uuid}`.
*   **Backend Note:** `TaskController` menangkap request dan meneruskannya ke `TaskService`. Parameter yang dikirim dari/ke user harus selalu UUID, BUKAN integer ID.

### Fitur 3: Manajemen Kategori (CRUD Category)
*   **Spesifikasi:** Buat, Edit, Hapus Kategori (Nama dan Warna).
*   **Backend Note:** `CategoryRepository` menangani operasi Medoo. Jika dihapus, pastikan tidak ada *error constraint* karena tugas yang memakai kategori tersebut akan otomatis di-set `NULL` via Postgres `ON DELETE SET NULL`.

### Fitur 4: Dashboard Analitik (ApexChartsJS)
*   **Spesifikasi:** Tampilkan grafik statistik tugas di Halaman Utama.
*   **AI Note:** Buat `DashboardController` yang me-return data JSON dari `TaskService` (hitung pending vs completed). Di View, tangkap JSON via Axios dan render *Donut Chart* dengan ApexCharts.

### Fitur 5: Notifikasi Pengingat Tugas (Axios Polling)
*   **Spesifikasi:** Pengguna mendapat *ToastifyJS notification* jika tugas mendekati due_date (<= 30 menit atau sesuai setting task).
*   **AI Note:** Buat endpoint `GET /api/tasks/reminders`. Di file layout Header, jalankan Alpine.js `setInterval` setiap 1 menit untuk memanggil Axios ke endpoint ini. Jika ada tugas masuk daftar peringatan, tampilkan *Toast*, dan backend (via `TaskService`) otomatis menandai `is_reminded=TRUE` agar toast tidak muncul berulang.

### Fitur 6 & 7: Ubah Profil dan Password
*   **Spesifikasi:** Pengguna dapat mengganti `username` dan `password` di halaman pengaturan.
*   **AI Note:** `UserService` harus memverifikasi `password` lama (menggunakan `password_verify()`) sebelum memproses fungsi `password_hash()` untuk password baru.