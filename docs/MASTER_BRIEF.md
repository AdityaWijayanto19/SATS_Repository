# MASTER_BRIEF.md - Smart Ambulance Telemedicine System (SATS)

## Overview

**SATS** (Smart Ambulance Telemedicine System) adalah sistem yang mengintegrasikan perangkat IoT dengan web dashboard untuk memantau kondisi pasien secara real-time selama transportasi ambulans. Sistem ini merupakan project UAS (Ujian Akhir Semester).

---

## Alur Sistem (End-to-End)

```
[Nakes Aktifkan Perangkat] --> [Monitoring Session Dibuat (auto-generate RM)]
        |
        v
[Perangkat IoT] --> [Sensor: HR, SpO2, Suhu] --> [HTTP POST ke API]
        |
        v
[Server: sensor_data (temporary)] --> [WebSocket Broadcast]
        |                              --> [Dashboard Real-Time]
        |                              --> [Prediksi ML (setiap 5 data)]
        v
[Nakes Matikan Perangkat] --> [Session Finalized]
        |                     --> [sensor_data → sensor_readings]
        |                     --> [sensor_data dihapus]
        v
[Nakes Input Data Pasien] --> [Link ke Session]
        |
        v
[Laporan (AJAX)] --> [Pilih Sesi + Vital Sign] --> [Download PDF]
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
| **WebSocket** | Laravel Reverb v1.0 (broadcasting)             |
| **Queue**     | Redis (predis/predis v3.4) + Database driver   |
| **IoT**       | Arduino IDE (kode terpisah, belum ada di repo)  |
| **ML**        | Hugging Face Spaces (Gradio async 2-step API)   |

---

## Sistem Role

| Role         | Akses                   | Status         |
|--------------|-------------------------|----------------|
| `nakes`      | `/nakes/*`              | Sudah ada view |
| `dokter`     | `/dokter/*`             | Sudah ada view |
| `superadmin` | `/superadmin/*`         | Sudah ada view |

**Pembeda nakes & dokter:** Halaman dashboard identik, dipisah route & folder. Halaman instruksi terpisah: dokter kirim instruksi medis, nakes kirim laporan kejadian + konfirmasi instruksi.

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

### 2. Dashboard Nakes [TERHUBUNG KE API]

- Dropdown perangkat (auto-update tanpa refresh)
- 4 kartu statistik: Heart Rate, SpO2, Suhu, Kondisi Pasien
- Banner prediksi ML (dari database, fallback "Data prediksi belum tersedia")
- Probability card: Membaik/Stabil/Memburuk % dengan progress bar
- **Chart toggle: mode "Terspisah" (3 chart terpisah) dan "Gabungan" (1 chart, 3 Y-axis)**
- Grafik real-time Chart.js via WebSocket (zero polling)
- **Terhubung ke API real (sensor-data/latest, sensor-data/history, instruction)**

### 3. Dashboard Dokter [TERHUBUNG KE API]

- Identik dengan dashboard nakes (monitoring, chart, vital sign)
- Dropdown pilih perangkat (subscribe semua device channels)
- **Chart toggle: mode "Terspisah" (3 chart terpisah) dan "Gabungan" (1 chart, 3 Y-axis)**
- **Terhubung ke API real (instruction, sensor-data)**

### 3b. Floating Chat Widget [TERHUBUNG KE API]

- Widget chat floating di pojok kanan bawah (fixed position) pada dashboard nakes & dokter
- **Minimized:** Tombol hijau rounded dengan ikon chat bubbles + notifikasi merah saat ada pesan baru
- **Expanded:** Panel chat dengan header (logo SATS, status online, sapaan role), area pesan, input, footer
- **Nakes:** Kirim pesan/laporan (free text) + 9 quick reply buttons (Sudah dilakukan, Dalam proses, Alat tidak tersedia, Obat sudah diberikan, Pasien stabil, Pasien kritis, Butuh bantuan, Gagal, Monitoring lanjutan)
- **Dokter:** Kirim instruksi medis (free text), pantau laporan & respon nakes
- **Chat alignment:** Pesan sendiri di kanan, pesan lawan di kiri (role-aware)
- **Avatar:** Foto profil user dari `users.photo`, fallback ke inisial role (DR/NK)
- Real-time via Laravel Reverb WebSocket (zero delay)
- Echo channel cleanup saat ganti device
- **Component:** `resources/views/components/chat-widget.blade.php` (Alpine.js `chatWidget()`)
- **Backend:** `InstructionController`, `InstructionService`, 3 Events (`ShouldBroadcastNow`), 4 Form Requests
- **Tabel:** `instructions` (device_id, dokter_id, nakes_id, instruksi_dokter, respon_nakes, laporan_nakes, is_completed)
- **Bug fixes:** Duplikasi pesan (Reverb + API response), `toOthers()` removed, nested `template x-if` → `x-show`, device subscription untuk offline devices

### 4. Input Data Pasien [TERHUBUNG KE BACKEND]

- Form: Nama, NIK, Tgl Lahir, Umur, Jenis Kelamin, Penyakit/Alergi, Catatan
- Nomor rekam medis auto-generate: `RM-{DEVICE_ID}-{YYYYMMDD}-{SEQ}`
- **Backend: `PatientController::store()` + `MonitoringSessionService::linkPatient()`**
- Tersedia di halaman input-data-pasien dan modal popup di halaman laporan
- Data pasien di-link ke active monitoring session

### 5. Laporan Medis Nakes & Dokter [TERHUBUNG KE DATABASE]

- Identitas pasien dari `patients` table (via monitoring session)
- Banner prediksi ML
- Grafik vital signs (Chart.js) — re-init setelah AJAX load
- Nilai vital (heart rate, SpO2, temperature) + statistik (avg, min, max)
- **AJAX session selection** — dropdown sesi, load data tanpa refresh halaman
- **Vital sign checkbox** — pilih vital sign yang ditampilkan
- **Partial views** — `_laporan-patient`, `_laporan-content`, `_laporan-sidebar`
- **Input Data Pasien modal** — popup dengan background blur putih
- Tabel riwayat sensor readings
- Download PDF dengan data real (DomPDF + QuickChart.io, nama file = nomor rekam medis)
- `LaporanController` + `ReportService` — real data dari `monitoring_sessions` + `sensor_readings`
- Dokter: read-only (tidak bisa edit data pasien)

### 6. Dashboard Superadmin [TERHUBUNG KE BACKEND]

- 5 kartu statistik: Total Alat, Alat Aktif, Alat Non-Aktif, Total Pengguna, Pengguna Online
- Tabel Perangkat Aktif (device_id, kondisi pasien, nakes, dokter, waktu update)
- Log Aktivitas Terbaru (16 event types, timeline dengan indikator warna)
- Real-time updates via WebSocket (Alpine.js + Laravel Echo)
- **Backend: DashboardController + ActivityLog model**
- **FIXED:** WebSocket real-time untuk activity log — menggunakan PrivateChannel + Alpine double-init workaround

### 7. Manajemen Alat (Superadmin) [TERHUBUNG KE BACKEND]

- Tabel inventaris perangkat (data dari database)
- Modal "+ Daftar Alat" (input ID & Nama Perangkat)
- Modal Detail per perangkat (ikon, nama, ID, status, urgensi, tgl daftar, terakhir aktif, lokasi)
- CRUD: tambah device, lihat detail, hapus device
- Auto-generate API key saat registrasi device (ditampilkan sekali)
- **Backend: ManajemenAlatController (store, show, destroy)**

### 8. Manajemen User (Superadmin) [TERHUBUNG KE BACKEND]

- Tabel pengguna (data dari database)
- Badge peran berwarna: Super Admin (ungu), Dokter (biru), Perawat (pink)
- Modal "+ Tambah User" (ID, Nama, Peran dropdown, Email)
- Modal Detail user (avatar, role badge, telepon, tgl bergabung)
- **Backend: UserController (store, update, destroy) + routes terdaftar di web.php**
- **Routes:** POST `/superadmin/manajemen-user`, DELETE `/superadmin/manajemen-user/{user}`

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
  - **Dokter:** Dashboard, Input Data Pasien, Laporan, Instruksi
  - **Nakes:** Dashboard, Input Data Pasien, Laporan, Instruksi
- Active state menggunakan `request()->routeIs()`
- **Sidebar logo clickable** → landing page (`/`)
- Navbar: logo + nama user + role + profile dropdown (edit profile, logout)

### 10b. Edit Profil [SELESAI]

- Form edit: nama, email, password (opsional), foto profil
- **Default avatar per role:** 4 variasi per role (superadmin, dokter, nakes) di `public/assets/photo_profile/`
- Radio selection untuk pilih avatar, preview real-time
- **Backend:** `ProfileController` (edit, update)
- **Migration:** `photo` column di tabel `users`
- **Routes:** `GET /profile/edit`, `PUT /profile` (semua role)

### 10c. Landing Page [SELESAI]

- Halaman publik (`/`) dengan 7 section:
  1. **Hero** — judul utama + CTA
  2. **Tentang** — penjelasan SATS
  3. **Fitur** — 3 fitur utama (Real-Time Monitoring, Urgency Classification, Predictive Analytics)
  4. **Alat** — perangkat IoT yang digunakan
  5. **Cara Kerja** — alur sistem
  6. **FAQ** — 6 pertanyaan umum (accordion Alpine.js)
  7. **Closing** — CTA penutup
- Navbar: navigasi section + login/dashboard link (conditional auth)
- Footer dengan informasi project
- **Layout:** `layouts/landing.blade.php`
- **Sections:** `pages/landing/sections/` (7 file)

### 11. Backend & Database [SEBAGIAN BESAR SELESAI]

**Sudah ada:**
- 15 migrasi (users, devices, sensor_datas, sensor_readings, system_statuses, api_keys, patients, medical_records, activity_log, instructions, monitoring_sessions, nakes_device_configs, device_monitorings, cache, jobs, add_photo_to_users)
- 13 model Eloquent: User, Devices, SensorData, SensorReading, SystemStatus, ApiKey, Patient, MedicalRecord, ActivityLog, Instruction, MonitoringSession, NakesDeviceConfig, DeviceMonitoring
- 8 service layer: AuthService, DeviceService, UserService, InstructionService, SensorService, PatientMonitoringService, MonitoringSessionService, ReportService
- `ProfileController` untuk edit profil user
- `PatientController` untuk input data pasien + link ke session
- 11 form request validation
- 4 event class: InstructionSent, InstructionStatusUpdated, InstructionReportSubmitted, ActivityLogCreated
- 16 activity log events: user login/logout, password reset, device online/offline/added/deleted, monitoring started/stopped, patient warning/critical, instruction sent/completed, user added/deleted
- 2 job class: ProcessDeviceData, ProcessSensorData
- API sensor data (POST, GET latest, GET history)
- API instruksi (GET, POST, POST report, PATCH update, PATCH complete)
- API device list (`/api/devices` — termasuk active_session)
- API system status (POST, GET)
- API laporan AJAX (`GET /nakes/laporan/session-data`)
- API input data pasien (`POST /nakes/input-data-pasien`)
- Autentikasi device via API key
- Queue & broadcasting infrastructure (Redis + Reverb)
- Seeder: 3 user + 2 device + 2 API key
- Monitoring session system (auto-create ON, finalize OFF, auto-generate RM)
- Dokumentasi: [DATABASE.md](DATABASE.md), [BACKEND.md](BACKEND.md)

**Belum ada:**
- Laporan superadmin dari database (masih dummy data)
- Device config dari database (masih hardcoded)

### 12. Integrasi IoT [SIMULATOR SUDAH ADA]

Simulator Python tersedia di `simulasi_py/` untuk testing tanpa hardware.

**Alur data (sudah jalan):**
- Simulator → POST `/api/device/{id}/sensor-data` → database → dashboard polling
- Simulator → POST `/api/device/{id}/system-status` → database

**Alur data (rencana hardware):**
- Device mengirim data via HTTP POST ke endpoint yang sama
- Payload: `{ heart_rate, spo2, temperature }`
- API endpoints (sudah ada):
  - `GET /api/device/{device_id}/sensor-data/latest`
  - `GET /api/device/{device_id}/sensor-data/history?minutes=10`
  - `GET /api/device/{device_id}/system-status`

### 13. Machine Learning [SELESAI]

Integrasi ML prediksi kondisi pasien via Hugging Face Spaces (Gradio async 2-step API).

**Alur:**
1. Setiap 5 data sensor baru, `SensorService` trigger `PatientMonitoringService`
2. Kirim 15 angka (5 menit × 3 vital signs: HR, Temp, SpO2) ke API eksternal
3. Hasil disimpan di tabel `devices`: `ml_prediction`, `ml_condition`, `ml_risk_level`, `ml_probabilities`, `ml_predicted_at`
4. Broadcast ulang ke dashboard via WebSocket setelah prediksi selesai

**Output ML:**
- Prediksi teks: "Pasien akan MEMBURUK (63%) dalam 5 menit ke depan"
- Kondisi: `NORMAL` / `WARNING` / `CRITICAL`
- Risk Level: `Low Risk` / `Medium Risk` / `High Risk`
- Probabilitas: Membaik (%) / Stabil (%) / Memburuk (%)

**Frontend:**
- Banner prediksi ML di dashboard nakes & dokter
- Probability card (3 kolom: Membaik, Stabil, Memburuk % dengan progress bar)
- Data di-update real-time via WebSocket

**API:** `GET /api/device/{device_id}/prediction`
**Service:** `PatientMonitoringService.php` (Hugging Face Spaces)
**Docs:** [API_DOCUMENTATION.md](API_DOCUMENTATION.md)

---

## Rencana Perbaikan & Pengembangan

### Prioritas 1 — Realtime & Delay Fix
- [x] **FIX: WebSocket broadcasting auth 403 error** — Fixed: PrivateChannel + Alpine double-init workaround
- [ ] Kurangi delay pengiriman data simulator → dashboard
- [ ] Optimasi polling: pertimbangkan SSE (Server-Sent Events) sebagai alternatif
- [ ] Kurangi atau hapus cache TTL pada DeviceService (saat ini 5 menit)
- [ ] Pastikan data simulator langsung terlihat di dashboard tanpa delay signifikan

### Prioritas 2 — Notifikasi & Feedback UI
- [ ] Notifikasi "Instruksi terkirim" saat dokter mengirim instruksi (toast/snackbar)
- [ ] Warning/highlight saat instruksi diselesaikan nakes (badge atau animasi)
- [ ] Badge counter instruksi aktif di sidebar nakes
- [ ] Notifikasi realtime (broadcasting via Reverb sudah ada, perlu listener di frontend)

### Prioritas 3 — Backend yang Belum Selesai
- [x] Backend input data pasien (POST route + controller → tabel `patients`)
- [x] Daftarkan route untuk UserController (CRUD user ke manajemen-user)
- [x] Aktifkan query database di LaporanController (ganti dummy data) → real data via ReportService
- [ ] Aktifkan query database di SuperadminLaporanController (ganti dummy data)
- [ ] Device config dari database (ganti hardcoded)
- [x] Activity log: 16 event types sudah terinstrumentasi
- [x] Monitoring session system (auto-create ON, finalize OFF, auto-generate RM)
- [ ] **Fitur Hubungi Superadmin + Inbox** — form pelaporan kendala & request akun (guest), inbox superadmin (plan: `docs/plan-hubungi-superadmin.md`)

### Prioritas 4 — Integrasi & Testing
- [ ] Testing dengan hardware IoT real
- [ ] Konfigurasi HTTP POST pada device Arduino
- [ ] Stress testing API endpoint
- [ ] Validasi data sensor (toleransi outlier)

### Prioritas 5 — Machine Learning [SELESAI]
- [x] Training model prediksi kondisi pasien (Hugging Face Spaces)
- [x] Integrasi model ke pipeline data (PatientMonitoringService + SensorService)
- [x] Endpoint prediksi (`GET /api/device/{id}/prediction`)
- [x] Tampilkan prediksi di dashboard (banner + probability card)
- [x] Probabilitas Membaik/Stabil/Memburuk di dashboard nakes & dokter

---

## Struktur File Utama

```
app/Http/Controllers/
  AuthController.php              # Login, logout, forgot/reset password
  DashboardController.php         # Role-based view resolver + API device list
  UserController.php              # CRUD user (superadmin)
  ManajemenAlatController.php     # CRUD perangkat IoT (superadmin)
  LaporanController.php           # Laporan HTML + PDF nakes/dokter (real data + AJAX)
  SuperadminLaporanController.php # Laporan HTML + PDF superadmin
  PatientController.php           # Input data pasien + link ke monitoring session
  ProfileController.php           # Edit profil user (nama, email, password, foto)
  Api/
    DeviceDataController.php      # Autentikasi device, system status, session lifecycle
    SensorDataController.php      # Sensor data endpoint (store, latest, history)
    InstructionController.php     # Instruksi dokter-nakes (CRUD + report + complete)

app/Services/
  AuthService.php                 # Business logic autentikasi + redirect role
  DeviceService.php               # Sensor data CRUD + caching
  UserService.php                 # User CRUD
  InstructionService.php          # Instruksi dokter-nakes business logic
  SensorService.php               # Sensor data operations + caching + trigger ML
  MonitoringSessionService.php    # Session lifecycle: create, finalize, link patient
  ReportService.php               # Report data: getReportData, getChart, getStats

app/Events/
  InstructionSent.php             # Broadcast saat dokter kirim instruksi
  InstructionStatusUpdated.php    # Broadcast saat nakes selesaikan instruksi
  InstructionReportSubmitted.php  # Broadcast saat nakes submit laporan
  ActivityLogCreated.php          # Broadcast activity log real-time ke superadmin dashboard

app/Jobs/
  ProcessDeviceData.php           # Queue: proses system status device
  ProcessSensorData.php           # Queue: proses sensor data

app/Http/Middleware/
  RoleMiddleware.php              # Cek role user
  AuthenticateApiKey.php          # Validasi API key device IoT

app/Models/
  User.php                        # users
  Devices.php                     # devices (PK: device_id string, ML fields)
  SensorData.php                  # sensor_datas (temporary, realtime dashboard)
  SensorReading.php               # sensor_readings (finalized, untuk laporan)
  SystemStatus.php                # system_statuses
  ApiKey.php                      # api_keys
  Patient.php                     # patients
  MedicalRecord.php               # medical_records
  ActivityLog.php                 # activity_log
  Instruction.php                 # instructions
  MonitoringSession.php           # monitoring_sessions
  NakesDeviceConfig.php           # nakes_device_configs
  DeviceMonitoring.php            # device_monitorings

database/seeders/
  UserSeeder.php                  # 3 akun: superadmin, dokter, nakes
  DeviceSeeder.php                # 2 device + 2 API key
  DatabaseSeeder.php              # Memanggil UserSeeder + DeviceSeeder

simulasi_py/
  config.py                       # Konfigurasi simulator
  simulator.py                    # Simulator IoT device (Python)
  requirements.txt                # Dependency: requests

resources/views/
  components/
    navbar.blade.php              # Navigasi atas (nama + role + logout)
    sidebar.blade.php             # Sidebar dinamis (nakes/dokter/superadmin), logo → landing
    chat-widget.blade.php         # Floating chat widget (nakes & dokter dashboard)
    profile-dropdown.blade.php    # Dropdown profil (edit profile, logout)
    landing-navbar.blade.php      # Navbar landing page
    landing-footer.blade.php      # Footer landing page
  layouts/
    app.blade.php                 # Layout utama
    auth.blade.php                # Layout halaman auth
    landing.blade.php             # Layout landing page
  pages/
    landing.blade.php             # Landing page (7 sections)
    login.blade.php               # Login + image slider
    auth/                         # Forgot & reset password
    profile/
      edit.blade.php              # Edit profil (nama, email, password, avatar)
    landing/
      sections/
        hero.blade.php            # Section hero
        tentang.blade.php         # Section tentang
        fitur.blade.php           # Section fitur
        alat.blade.php            # Section alat
        cara-kerja.blade.php      # Section cara kerja
        faq.blade.php             # Section FAQ (accordion)
        closing.blade.php         # Section CTA
    nakes/
      dashboard.blade.php         # Monitoring vital sign + chart toggle + session banner
      inputdata.blade.php         # Form input pasien (terhubung ke backend)
      laporan.blade.php           # Laporan medis + AJAX session + modal input pasien
      laporan-pdf.blade.php       # Template PDF (real data)
      instruksi.blade.php         # Chat instruksi dokter + laporan nakes (API-connected)
      partials/
        _laporan-patient.blade.php   # Identitas pasien + tombol input data
        _laporan-content.blade.php   # ML banner, chart, vital signs, stats, tabel
        _laporan-sidebar.blade.php   # Info session + tombol download PDF
    dokter/
      dashboard.blade.php         # Monitoring vital sign + chart toggle (API-connected)
      inputdata.blade.php         # Form input pasien
      laporan.blade.php           # Laporan medis + AJAX session (read-only)
      laporan-pdf.blade.php       # Template PDF (real data)
      instruksi.blade.php         # Chat instruksi medis + pantau laporan nakes (API-connected)
    superadmin/
      dashboard.blade.php         # Stat cards, tabel kritis, log aktivitas
      manajemen-alat.blade.php    # Inventaris alat + modal (CRUD active)
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
| Python | 3.8+ | Opsional, untuk menjalankan simulator IoT |

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
# Buat semua tabel + isi data awal
php artisan migrate --seed
```

Data yang di-seed:
- Tabel `users` dengan 3 akun (superadmin, dokter, nakes)
- Tabel `devices` dengan 2 device (`DEVICE_01`, `DEVICE_02`)
- Tabel `api_keys` dengan 2 API key (hashed) untuk masing-masing device

> **Catatan:** Jika tabel sudah ada, gunakan `php artisan migrate:fresh --seed` untuk reset ulang.

#### 7. Jalankan Development Server

Buka **5 terminal** secara bersamaan:

**Terminal 1 — Vite (Tailwind CSS & hot reload):**
```bash
npm run dev
```

**Terminal 2 — Laravel server:**
```bash
php artisan serve
```

**Terminal 3 — Redis server (wajib untuk autentikasi API key device):**
```bash
redis_server/redis-server.exe redis_server/redis.windows.conf
```

**Terminal 4 — Queue worker (proses sensor data + ML prediction):**
```bash
php artisan queue:work
```

**Terminal 5 — Reverb WebSocket server (real-time updates):**
```bash
php artisan reverb:start
```

> Atau jika menggunakan Laragon, akses langsung: `http://sats-repository.test`

#### 8. Buka di Browser

```
http://localhost:8000
```

#### 9. Jalankan Simulator (Opsional)

Simulator Python mengirim data sensor ke API untuk testing tanpa hardware IoT.

```bash
# Masuk ke folder simulator
cd simulasi_py

# Install dependency Python
pip install -r requirements.txt

# Jalankan simulator (gunakan device & API key dari seeder)
python simulator.py --device DEVICE_01 --key test_key_device_01
```

> Simulator akan mengirim data sensor setiap 5 detik ke API.
> Data akan muncul real-time di dashboard nakes/dokter.
> Buka simulator di terminal terpisah (total 3 terminal: Vite, Laravel, Simulator).

**Menjalankan banyak device sekaligus:**
```bash
# Terminal 3 — Device 1
python simulator.py --device DEVICE_01 --key test_key_device_01

# Terminal 4 — Device 2
python simulator.py --device DEVICE_02 --key test_key_device_02
```

**Konfigurasi simulator** (`simulasi_py/config.py`):
- `DEVICE_ID` — ID device yang di-simulasikan
- `API_KEY` — API key device
- `SEND_INTERVAL` — Interval pengiriman data (detik, default 5)

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
| Simulator `ModuleNotFoundError` | Jalankan `pip install -r requirements.txt` di folder `simulasi_py/` |
| Simulator `Connection refused` | Pastikan Laravel server berjalan di `http://localhost:8000` |
| Simulator `401 Unauthorized` | Cek API key di `config.py` cocok dengan yang di-seed di database |
| Dashboard tidak update | Pastikan simulator berjalan + Reverb & queue worker running |
| `Pusher error: cURL error 7` | Jalankan `php artisan reverb:start` di terminal terparsah |
| Activity log tidak real-time | Pastikan `php artisan reverb:start` berjalan. Sudah di-fix dengan PrivateChannel + Alpine double-init workaround. |
| Simulator `Authentication error: Connection refused` | Redis server belum jalan. Jalankan `redis_server/redis-server.exe redis_server/redis.windows.conf` |
| ML prediction tidak muncul | Pastikan `php artisan queue:work` berjalan, cek log untuk "ML trigger" |

---

## Role & Pembagian Kerja

| Anggota      | Role      | Cakupan Kerja                        |
|--------------|-----------|--------------------------------------|
| Dalvero      | Frontend  | UI/UX Blade templates, chart, layout |
| Aditya       | Backend   | API, database, integrasi IoT, ML     |

> Catatan: FRONTEND.md adalah dokumentasi progress dari sisi frontend (Dalvero).

---

## Flow Sistem: Dari Pemasangan Hingga Rekam Medis

```
Nakes memasang perangkat pada pasien
        |
        v
Nakes mengaktifkan perangkat (toggle ON di dashboard)
        |--- Monitoring session otomatis dibuat (status: active)
        |--- Nomor rekam medis auto-generate: RM-{DEVICE}-{DATE}-{SEQ}
        |--- Dashboard menampilkan banner session aktif
        |
        v
Perangkat mulai mengirim data sensor setiap 1-2 detik
        |--- data masuk ke tabel sensor_data (temporary)
        |--- data ditampilkan real-time di dashboard (WebSocket)
        |--- prediksi ML trigger setiap 5 data baru
        |
        v
Nakes input data pasien (opsional, bisa kapan saja)
        |--- dari halaman input-data-pasien atau modal di laporan
        |--- data pasien di-link ke active session
        |
        v
Pasien tiba di RS tujuan --> Nakes mematikan perangkat (toggle OFF)
        |--- Session masuk status: completed
        |--- sensor_data di-copy ke sensor_readings (finalized)
        |--- SEMUA sensor_data untuk device dihapus
        |
        v
Nakes buka halaman laporan
        |--- Pilih sesi dari dropdown (AJAX, tanpa refresh)
        |--- Pilih vital sign yang ditampilkan (checkbox)
        |--- Lihat identitas pasien, grafik, statistik, tabel riwayat
        |
        v
Download PDF laporan
        |--- Nama file: Laporan-{nomor_rekam_medis}-{tanggal}.pdf
        |--- Data real dari sensor_readings + patients
        |--- Grafik via QuickChart.io
```

---

## Dokumentasi Terkait

- [README.md](README.md) - Gambaran umum project dan panduan setup
- [DEMO.md](DEMO.md) - Panduan instalasi, menjalankan sistem, dan demo
- [API_DOCUMENTATION.md](API_DOCUMENTATION.md) - Dokumentasi API + integrasi ML Hugging Face
- [FRONTEND.md](FRONTEND.md) - Detail progress frontend, TODO list, dan rencana harian
- [BACKEND.md](BACKEND.md) - Detail progress backend, API endpoints, service layer, dan simulator
- [DATABASE.md](DATABASE.md) - Struktur database, ERD, relasi, dan alur data sistem
- [LAPORAN_SYSTEM.md](LAPORAN_SYSTEM.md) - Desain sistem laporan, monitoring session, dan filterisasi data

---

*Last updated: 24 Mei 2026 (monitoring session, laporan real data, AJAX, input pasien)*
