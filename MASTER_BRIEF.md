# MASTER_BRIEF.md - Smart Ambulance Telemedicine System (SATS)

## Overview

**SATS** (Smart Ambulance Telemedicine System) adalah sistem yang mengintegrasikan perangkat IoT dengan web dashboard untuk memantau kondisi pasien secara real-time selama transportasi ambulans. Sistem ini merupakan project UAS (Ujian Akhir Semester).

---

## Alur Sistem (End-to-End)

```
[Perangkat IoT] --> [Sensor: Detak Jantung, SpO2, Suhu] --> [Pengiriman Data (MQTT/HTTP)]
        |
        v
[Rule-Based Klasifikasi Kondisi] (di kode Arduino/IoT)
        |
        v
[Web Dashboard Laravel] --> [Monitoring Real-Time]
        |                    --> [Grafik Vital Sign]
        |                    --> [Prediksi ML (rencana)]
        v
[Laporan Medis + PDF]
```

---

## Tech Stack

| Layer         | Teknologi                                      |
|---------------|-------------------------------------------------|
| **Backend**   | Laravel 12 (PHP 8.2+)                          |
| **Frontend**  | Blade + Tailwind CSS v4.2 + Alpine.js v3       |
| **Charting**  | Chart.js v4.4.1                                |
| **PDF**       | barryvdh/laravel-dompdf v3.1                   |
| **Build**     | Vite v6                                        |
| **Database**  | MySQL (`sats_db`)                              |
| **IoT**       | Arduino IDE (kode terpisah, belum ada di repo)  |
| **ML**        | Belum diimplementasikan (rencana di akhir)      |

---

## Sistem Role

| Role         | Akses                   | Status         |
|--------------|-------------------------|----------------|
| `nakes`      | `/nakes/*`              | Sudah ada view |
| `dokter`     | `/dokter/*`             | Sudah ada view |
| `superadmin` | `/superadmin/*`         | Sudah ada view |

**Pembeda nakes & dokter:** Halaman identik, dipisah route & folder. Fitur tambahan dokter: container komentar untuk nakes. Fitur tambahan nakes: dropdown respon instruksi dokter.

### Akun Pengguna (Seeder)

| Role | Nama | Email | Password |
|------|------|-------|----------|
| `superadmin` | Super Admin | `admin@sats.id` | `password` |
| `dokter` | Dr. Andi | `andi@sats.id` | `password` |
| `nakes` | Suster Rina | `rina@sats.id` | `password` |

> Seeder: `database/seeders/UserSeeder.php`

---

## Fitur & Progress per Komponen

### 1. Autentikasi & Otorisasi [SELESAI]

- Login, logout, forgot password, reset password (via email)
- Role middleware (`nakes`, `dokter`, `superadmin`)
- Redirect berdasarkan role setelah login (AuthService + AuthController)

### 2. Dashboard Nakes [UI SELESAI, DATA DUMMY]

- Dropdown perangkat (3 device hardcoded: Sats Wearable-1/2/3)
- 4 kartu statistik: Heart Rate, SpO2, Suhu, Kondisi Pasien
- Banner prediksi ML (hardcoded: 20% risk, 15 menit)
- 3 grafik real-time Chart.js (Heart Rate, SpO2, Suhu)
- Container respon instruksi dokter (dropdown 5 opsi + checklist)
- **Belum terhubung ke database/API real**

### 3. Dashboard Dokter [UI SELESAI, DATA DUMMY]

- Identik dengan dashboard nakes (monitoring, chart, vital sign)
- Container komentar untuk nakes (textarea + kirim)
- Daftar komentar terkirim dengan timestamp
- Tampilan respon nakes (green border, label "Respon Nakes")
- **Belum terhubung ke backend**

### 4. Input Data Pasien [UI SELESAI, BELUM ADA BACKEND]

- Form: No. Rekam Medis, Nama, NIK, Tgl Lahir, Jenis Kelamin, Penyakit/Alergi, Catatan
- Tersedia di nakes, dokter, dan superadmin
- **Belum ada POST handler di backend**

### 5. Laporan Medis Nakes & Dokter [UI SELESAI, DATA DUMMY]

- Identitas pasien, banner prediksi ML
- Grafik tekanan darah (sistolik/diastolik)
- Nilai vital (heart rate, SpO2)
- Hasil klasifikasi ML (Normal/Warning/Critical)
- Tabel riwayat kondisi pasien
- Filter rentang tanggal
- Download PDF (via DomPDF + QuickChart.io)
- `LaporanController` sudah role-aware (cek `auth()->user()->role`)

### 6. Dashboard Superadmin [UI SELESAI, DATA DUMMY]

- 4 kartu statistik: Total Alat, Alat Aktif, Alat Non-Aktif, Total Pengguna
- Tabel Alat Kritis (5 perangkat dengan status Warning/Kritis)
- Log Aktivitas Terbaru (timeline dengan indikator warna)
- **Belum terhubung ke database real**

### 7. Manajemen Alat (Superadmin) [UI SELESAI, DATA DUMMY]

- Tabel inventaris 9 perangkat SATS Wearable (DEV-001 s/d DEV-009)
- Modal "+ Daftar Alat" (input ID & Nama Perangkat)
- Modal Detail per perangkat (ikon, nama, ID, status, urgensi, tgl daftar, terakhir aktif, lokasi)
- **Belum ada backend CRUD**

### 8. Manajemen User (Superadmin) [UI SELESAI, DATA DUMMY]

- Tabel 5 pengguna (No, Nama, Peran, Status, Email)
- Badge peran berwarna: Super Admin (ungu), Dokter (biru), Perawat (pink)
- Modal "+ Tambah User" (ID, Nama, Peran dropdown, Email)
- Modal Detail user (avatar, role badge, telepon, tgl bergabung)
- **Belum ada backend CRUD**

### 9. Laporan Superadmin [UI SELESAI, DATA DUMMY]

- Filter: rentang tanggal + dropdown ambulans
- 3 kartu statistik: Total Penggunaan Alat, Total Aktivitas User, Total Laporan
- Chart.js line chart 3 sumbu Y (Heart Rate, SpO2, Temperature)
- Tabel data sensor (12 baris): Waktu, Perangkat, Ambulans, vital signs, Klasifikasi
- Badge klasifikasi: Normal (hijau), Warning (kuning), Critical (merah)
- Download PDF (landscape A4, via QuickChart.io)
- Controller: `SuperadminLaporanController`

### 10. Sidebar & Navbar [SELESAI]

- Sidebar dinamis berdasarkan role:
  - **Superadmin:** Dashboard, Manajemen Alat, Manajemen User, Laporan
  - **Dokter:** Dashboard, Input Data Pasien, Laporan
  - **Nakes:** Dashboard, Input Data Pasien, Laporan
- Active state menggunakan `request()->routeIs()`
- Navbar: logo + nama user + role + tombol logout

### 11. Backend & Database [SEBAGIAN BESAR BELUM ADA]

**Sudah ada:**
- Tabel `users`, `password_reset_tokens`, `sessions`, `cache`, `jobs`
- Model `User.php`
- Seeder `UserSeeder.php` (3 akun: superadmin, dokter, nakes)
- Dokumentasi database lengkap di [DATABASE.md](DATABASE.md)

**Belum ada:**
- Model & migration: `Pasien`, `VitalSign`, `RiwayatKondisi`, `Device`
- API endpoints untuk IoT data ingestion
- Backend handler untuk input data pasien
- Backend CRUD untuk manajemen alat & user
- Backend untuk komentar dokter↔nakes

### 12. Integrasi IoT [BELUM ADA DI REPO]

Kode Arduino/IoT berada di luar repo ini. Rencana alur data:
- Device mengirim data via MQTT/HTTP POST ke `/api/ingest`
- Payload: `{ device_id, heart_rate, spo2, temperature, timestamp }`
- API endpoints:
  - `GET /api/device/{device_id}/latest`
  - `GET /api/device/{device_id}/history?minutes=10`
  - `GET /api/device/{device_id}/prediction`
- Real-time: HTTP polling (10 detik) atau WebSocket (Laravel Echo + Pusher)

### 13. Machine Learning [BELUM ADA]

Direncanakan ditambahkan di akhir project. Fitur:
- Prediksi: "Pasien akan memburuk X% dalam Y menit ke depan"
- Klasifikasi: Normal / Warning / Critical
- Endpoint: `GET /api/device/{device_id}/prediction`
- Response: `{ risk_level, risk_percent, timeframe_minutes, message }`

---

## Struktur File Utama

```
app/Http/Controllers/
  AuthController.php              # Login, logout, forgot/reset password
  DashboardController.php         # Role-based view resolver (3 role)
  LaporanController.php           # Laporan HTML + PDF nakes/dokter (role-aware)
  SuperadminLaporanController.php # Laporan HTML + PDF superadmin

app/Services/
  AuthService.php                 # Business logic autentikasi + redirect role

app/Http/Middleware/
  RoleMiddleware.php              # Cek role user

database/seeders/
  UserSeeder.php                  # 3 akun: superadmin, dokter, nakes
  DatabaseSeeder.php              # Memanggil UserSeeder

resources/views/
  components/
    navbar.blade.php              # Navigasi atas (nama + role + logout)
    sidebar.blade.php             # Sidebar dinamis (nakes/dokter/superadmin)
  layouts/
    app.blade.php                 # Layout utama
    auth.blade.php                # Layout halaman auth
  pages/
    login.blade.php               # Login + image slider
    auth/                         # Forgot & reset password
    nakes/
      dashboard.blade.php         # Monitoring + respon instruksi dokter
      inputdata.blade.php         # Form input pasien
      laporan.blade.php           # Laporan medis + chart
      laporan-pdf.blade.php       # Template PDF
    dokter/
      dashboard.blade.php         # Monitoring + komentar untuk nakes
      inputdata.blade.php         # Form input pasien
      laporan.blade.php           # Laporan medis + chart
      laporan-pdf.blade.php       # Template PDF
    superadmin/
      dashboard.blade.php         # Stat cards, tabel kritis, log aktivitas
      manajemen-alat.blade.php    # Inventaris alat + modal
      manajemen-user.blade.php    # Manajemen user + modal
      laporan.blade.php           # Laporan + chart + tabel sensor
      laporan-pdf.blade.php       # Template PDF landscape
```

---

## Panduan Setup (Clone & Jalankan di Laptop Lain)

### Prasyarat

| Software | Versi Minimum | Keterangan |
|----------|---------------|------------|
| PHP | 8.2+ | Sudah include di Laragon/XAMPP |
| Composer | 2.x | Dependency manager PHP |
| Node.js | 18+ | Untuk Vite & Tailwind |
| MySQL | 8.x | Database utama |
| Git |任意 | Clone repo |

> **Rekomendasi:** Gunakan [Laragon](https://laragon.org/) (Windows) karena sudah include PHP, MySQL, dan auto virtual host.

### Step-by-Step

#### 1. Clone Repository

```bash
git clone https://github.com/AdityaWijayanto19/SATS_Repository.git
cd SATS_Repository
```

#### 2. Install Dependencies

```bash
# Install PHP dependencies
composer install

# Install JS dependencies
npm install
```

#### 3. Setup Environment

```bash
# Copy file env
cp .env.example .env

# Generate key aplikasi
php artisan key:generate
```

#### 4. Konfigurasi Database di `.env`

Buka file `.env`, ubah bagian database dari SQLite ke MySQL:

```env
# Comment/hapus baris SQLite
# DB_CONNECTION=sqlite

# Tambahkan konfigurasi MySQL
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=sats_db
DB_USERNAME=root
DB_PASSWORD=
```

> **Catatan Laragon:** Username default `root`, password kosong.
> **Catatan XAMPP:** Username default `root`, password kosong.
> Pastikan database `sats_db` sudah dibuat di MySQL (bisa via phpMyAdmin atau Laragon auto-create).

#### 5. Buat Database (jika belum ada)

Buka phpMyAdmin atau MySQL CLI:

```sql
CREATE DATABASE sats_db;
```

> Di Laragon, database akan otomatis terbuat saat pertama kali diakses jika nama database ditulis di `.env`.

#### 6. Jalankan Migration & Seeder

```bash
# Buat semua tabel
php artisan migrate

# Isi data awal (3 akun user)
php artisan db:seed
```

Data yang di-seed:
- Tabel `users` dengan 3 akun (superadmin, dokter, nakes)

#### 7. Jalankan Development Server

Buka **2 terminal** secara bersamaan:

**Terminal 1 — Vite (Tailwind CSS & hot reload):**
```bash
npm run dev
```

**Terminal 2 — Laravel server:**
```bash
php artisan serve
```

> Atau jika menggunakan Laragon, akses langsung: `http://sats-repository.test`

#### 8. Buka di Browser

```
http://localhost:8000
```

### Akun Login (Setelah Seed)

| Role | Email | Password | Dashboard |
|------|-------|----------|-----------|
| Super Admin | `admin@sats.id` | `password` | `/superadmin/dashboard` |
| Dokter | `andi@sats.id` | `password` | `/dokter/dashboard` |
| Nakes (Perawat) | `rina@sats.id` | `password` | `/nakes/dashboard` |

### Troubleshooting Umum

| Masalah | Solusi |
|---------|--------|
| `composer install` error | Jalankan `composer update` atau pastikan PHP 8.2+ (`php -v`) |
| `npm install` error | Pastikan Node.js 18+ terinstall (`node -v`), coba `npm install --force` |
| `No application encryption key` | Jalankan `php artisan key:generate` |
| `SQLSTATE` connection error | Cek MySQL berjalan, cek konfigurasi DB di `.env` |
| Halaman kosong/blank | Cek `storage/logs/laravel.log`, pastikan `npm run dev` berjalan |
| CSS/JS tidak ter-load | Pastikan `npm run dev` berjalan di terminal terpisah |
| `Vite manifest not found` | Jalankan `npm run build` atau `npm run dev` |
| Migration error `table already exists` | Jalankan `php artisan migrate:fresh --seed` (hapus semua tabel & seed ulang) |

---

## Role & Pembagian Kerja

| Anggota      | Role      | Cakupan Kerja                        |
|--------------|-----------|--------------------------------------|
| Dalvero      | Frontend  | UI/UX Blade templates, chart, layout |
| (Anggota lain)| Backend  | API, database, integrasi IoT, ML     |

> Catatan: FRONTEND.md adalah dokumentasi progress dari sisi frontend (Dalvero).

---

## Flow Sistem: Dari Pemasangan Hingga Rekam Medis

```
Nakes memasang perangkat pada pasien
        |
        v
Perangkat dinyalakan (via perangkat / dashboard monitoring)
        |
        v
Perangkat mulai mengambil data sensor & mengirim ke database
        |--- data masuk ke tabel sensor_data
        |--- data ditampilkan real-time di dashboard
        |
        v
Nakes di RS tujuan memantau kondisi pasien via dashboard
        |
        v
Pasien tiba di RS tujuan --> Nakes mematikan perangkat
        |--- perintah "stop" masuk ke tabel commands
        |
        v
Nakes di ambulans menginput data pasien
        |--- data masuk ke tabel patients
        |
        v
Nakes melakukan cross-check di menu laporan
        |--- pilih rentang tanggal/jam atau data vital terbaru
        |
        v
Rekam medis ter-generate otomatis
        |--- no rekam medis muncul di laporan
        |--- data tersimpan di tabel medical_records
        |--- laporan siap diunduh sebagai PDF
```

---

## Dokumentasi Terkait

- [FRONTEND.md](FRONTEND.md) - Detail progress frontend, TODO list, dan rencana harian
- [DATABASE.md](DATABASE.md) - Struktur database, ERD, relasi, dan alur data sistem

---

*Last updated: 10 Mei 2026*
