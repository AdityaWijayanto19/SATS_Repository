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

- Dropdown perangkat (polling `/api/devices` setiap 10 detik, auto-update tanpa refresh)
- 4 kartu statistik: Heart Rate, SpO2, Suhu, Kondisi Pasien
- Banner prediksi ML (dari database, fallback "Data prediksi belum tersedia")
- Probability card: Membaik/Stabil/Memburuk % dengan progress bar
- 3 grafik real-time Chart.js (polling setiap 5 detik, skip update jika data sama)
- **Terhubung ke API real (sensor-data/latest, sensor-data/history, instruction)**

### 3. Dashboard Dokter [TERHUBUNG KE API]

- Identik dengan dashboard nakes (monitoring, chart, vital sign)
- **Terhubung ke API real (instruction, sensor-data)**

### 3b. Floating Chat Widget [TERHUBUNG KE API]

- Widget chat floating di pojok kanan bawah (fixed position) pada dashboard nakes & dokter
- **Minimized:** Tombol hijau rounded dengan ikon chat bubbles + notifikasi merah saat ada pesan baru
- **Expanded:** Panel chat dengan header (logo SATS, status online, sapaan role), area pesan, input, footer
- **Nakes:** Kirim pesan/laporan (free text) + 9 quick reply buttons (Sudah dilakukan, Dalam proses, Alat tidak tersedia, Obat sudah diberikan, Pasien stabil, Pasien kritis, Butuh bantuan, Gagal, Monitoring lanjutan)
- **Dokter:** Kirim instruksi medis (free text), pantau laporan & respon nakes
- Real-time via Laravel Reverb WebSocket (zero delay)
- Echo channel cleanup saat ganti device
- **Component:** `resources/views/components/chat-widget.blade.php` (Alpine.js `chatWidget()`)
- **Backend:** `InstructionController`, `InstructionService`, 3 Events (`ShouldBroadcastNow`), 4 Form Requests
- **Tabel:** `instructions` (device_id, dokter_id, nakes_id, instruksi_dokter, respon_nakes, laporan_nakes, is_completed)
- **Bug fixes:** Duplikasi pesan (Reverb + API response), `toOthers()` removed, nested `template x-if` → `x-show`, device subscription untuk offline devices

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

### 7. Manajemen Alat (Superadmin) [TERHUBUNG KE BACKEND]

- Tabel inventaris perangkat (data dari database)
- Modal "+ Daftar Alat" (input ID & Nama Perangkat)
- Modal Detail per perangkat (ikon, nama, ID, status, urgensi, tgl daftar, terakhir aktif, lokasi)
- CRUD: tambah device, lihat detail, hapus device
- Auto-generate API key saat registrasi device (ditampilkan sekali)
- **Backend: ManajemenAlatController (store, show, destroy)**

### 8. Manajemen User (Superadmin) [UI SELESAI, BELUM ADA ROUTE]

- Tabel pengguna (data dari database)
- Badge peran berwarna: Super Admin (ungu), Dokter (biru), Perawat (pink)
- Modal "+ Tambah User" (ID, Nama, Peran dropdown, Email)
- Modal Detail user (avatar, role badge, telepon, tgl bergabung)
- **Backend: UserController sudah ada (store, update, destroy), belum ada route di web.php**

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
- Navbar: logo + nama user + role + tombol logout

### 11. Backend & Database [SEBAGIAN BESAR SELESAI]

**Sudah ada:**
- 11 migrasi (users, devices, sensor_datas, system_statuses, api_keys, patients, medical_records, activity_log, instructions, cache, jobs)
- 9 model Eloquent dengan relasi: User, Devices, SensorData, SystemStatus, ApiKey, Patient, MedicalRecord, ActivityLog, Instruction
- 5 service layer: AuthService, DeviceService, UserService, InstructionService, SensorService
- 11 form request validation
- 3 event class: InstructionSent, InstructionStatusUpdated, InstructionReportSubmitted
- 2 job class: ProcessDeviceData, ProcessSensorData
- API sensor data (POST, GET latest, GET history)
- API instruksi (GET, POST, POST report, PATCH update, PATCH complete)
- API device list (`/api/devices`)
- API system status (POST, GET)
- Autentikasi device via API key
- Queue & broadcasting infrastructure (Redis + Reverb)
- Seeder: 3 user + 2 device + 2 API key
- Dokumentasi: [DATABASE.md](DATABASE.md), [BACKEND.md](BACKEND.md)

**Belum ada:**
- Backend input data pasien (POST handler)
- Route untuk UserController (CRUD user belum terhubung ke UI)
- Laporan dari database (masih dummy data)
- Device config dari database (masih hardcoded)
- Activity log controller

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
**Docs:** [API_INTEGRATION.md](API_INTEGRATION.md)

---

## Rencana Perbaikan & Pengembangan

### Prioritas 1 — Realtime & Delay Fix
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
- [ ] Backend input data pasien (POST route + controller → tabel `patients`)
- [ ] Daftarkan route untuk UserController (CRUD user ke manajemen-user)
- [ ] Aktifkan query database di LaporanController (ganti dummy data)
- [ ] Aktifkan query database di SuperadminLaporanController (ganti dummy data)
- [ ] Device config dari database (ganti hardcoded)
- [ ] Activity log: tulis log di setiap aksi penting

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
  LaporanController.php           # Laporan HTML + PDF nakes/dokter (role-aware)
  SuperadminLaporanController.php # Laporan HTML + PDF superadmin
  Api/
    DeviceDataController.php      # Autentikasi device, system status, device config
    SensorDataController.php      # Sensor data endpoint (store, latest, history)
    InstructionController.php     # Instruksi dokter-nakes (CRUD + report + complete)

app/Services/
  AuthService.php                 # Business logic autentikasi + redirect role
  DeviceService.php               # Sensor data CRUD + caching
  UserService.php                 # User CRUD
  InstructionService.php          # Instruksi dokter-nakes business logic
  SensorService.php               # Sensor data operations + caching

app/Events/
  InstructionSent.php             # Broadcast saat dokter kirim instruksi
  InstructionStatusUpdated.php    # Broadcast saat nakes selesaikan instruksi
  InstructionReportSubmitted.php  # Broadcast saat nakes submit laporan

app/Jobs/
  ProcessDeviceData.php           # Queue: proses system status device
  ProcessSensorData.php           # Queue: proses sensor data

app/Http/Middleware/
  RoleMiddleware.php              # Cek role user
  AuthenticateApiKey.php          # Validasi API key device IoT

app/Models/
  User.php                        # users
  Devices.php                     # devices (PK: device_id string)
  SensorData.php                  # sensor_datas
  SystemStatus.php                # system_statuses
  ApiKey.php                      # api_keys
  Patient.php                     # patients
  MedicalRecord.php               # medical_records
  ActivityLog.php                 # activity_log
  Instruction.php                 # instructions

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
    sidebar.blade.php             # Sidebar dinamis (nakes/dokter/superadmin)
  layouts/
    app.blade.php                 # Layout utama
    auth.blade.php                # Layout halaman auth
  pages/
    login.blade.php               # Login + image slider
    auth/                         # Forgot & reset password
    nakes/
      dashboard.blade.php         # Monitoring vital sign (API-connected)
      inputdata.blade.php         # Form input pasien
      laporan.blade.php           # Laporan medis + chart
      laporan-pdf.blade.php       # Template PDF
      instruksi.blade.php         # Chat instruksi dokter + laporan nakes (API-connected)
    dokter/
      dashboard.blade.php         # Monitoring vital sign (API-connected)
      inputdata.blade.php         # Form input pasien
      laporan.blade.php           # Laporan medis + chart
      laporan-pdf.blade.php       # Template PDF
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

Buka **4 terminal** secara bersamaan:

**Terminal 1 — Vite (Tailwind CSS & hot reload):**
```bash
npm run dev
```

**Terminal 2 — Laravel server:**
```bash
php artisan serve
```

**Terminal 3 — Queue worker (proses sensor data + ML prediction):**
```bash
php artisan queue:work
```

**Terminal 4 — Reverb WebSocket server (real-time updates):**
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
| `Pusher error: cURL error 7` | Jalankan `php artisan reverb:start` di terminal terpisah |
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
- [BACKEND.md](BACKEND.md) - Detail progress backend, API endpoints, service layer, dan simulator
- [DATABASE.md](DATABASE.md) - Struktur database, ERD, relasi, dan alur data sistem

---

*Last updated: 18 Mei 2026*
