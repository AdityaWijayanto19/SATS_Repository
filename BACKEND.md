# SATS - Backend Documentation

## Tech Stack

- **Framework:** Laravel 12 (PHP 8.2+)
- **Database:** MySQL (`sats_db`)
- **Auth:** Session-based (web), API Key (IoT devices)
- **Cache:** File-based (DeviceService)
- **PDF:** barryvdh/laravel-dompdf v3.1
- **Simulator:** Python 3 + requests

---

## Arsitektur Backend

```
app/
  Http/
    Controllers/
      AuthController.php                # Login, logout, forgot/reset password
      DashboardController.php           # Role-based view resolver + API device list
      UserController.php                # CRUD user (superadmin)
      ManajemenAlatController.php       # CRUD perangkat IoT (superadmin)
      LaporanController.php             # Laporan HTML + PDF nakes/dokter
      SuperadminLaporanController.php   # Laporan HTML + PDF superadmin
      Api/
        DeviceAuthController.php        # Autentikasi device, sensor data, system status
        SensorDataController.php        # Legacy sensor data endpoint
        CommentController.php           # Komentar dokter-nakes
    Middleware/
      AuthenticateApiKey.php            # Validasi X-API-Key untuk device IoT
      RoleMiddleware.php                # Cek role user (nakes/dokter/superadmin)
    Requests/
      LoginRequest.php
      ForgotPasswordRequest.php
      ResetPasswordRequest.php
      CreateUserRequest.php
      UpdateUserRequest.php
      StoreSensorDataRequest.php
      StoreSystemStatusRequest.php
  Models/
    User.php                            # users
    Devices.php                         # devices (PK: device_id string)
    SensorData.php                      # sensor_datas
    SystemStatus.php                    # system_statuses
    ApiKey.php                          # api_keys
    Patient.php                         # patients
    MedicalRecord.php                   # medical_records
    Command.php                         # commands
    ActivityLog.php                     # activity_log
    Comment.php                         # comments
  Services/
    AuthService.php                     # Login, logout, reset password
    DeviceService.php                   # Sensor data CRUD + caching
    UserService.php                     # User CRUD
database/
  migrations/                           # 12 file migrasi
  seeders/
    DatabaseSeeder.php                  # Memanggil UserSeeder + DeviceSeeder
    UserSeeder.php                      # 3 akun (superadmin, dokter, nakes)
    DeviceSeeder.php                    # 2 device + 2 API key
simulasi_py/
  config.py                             # Konfigurasi simulator
  simulator.py                          # Simulator IoT device (Python)
  requirements.txt                      # Dependency: requests
```

---

## Database Schema

### Tabel Utama (9 tabel domain)

| Tabel | Primary Key | Keterangan |
|-------|-------------|------------|
| `users` | id (auto-increment) | Akun pengguna (role: nakes/dokter/superadmin) |
| `devices` | device_id (string 50) | Perangkat IoT terdaftar |
| `sensor_datas` | id (auto-increment) | Data vital sign dari sensor |
| `system_statuses` | device_id (string) | Status sistem perangkat (battery, signal) |
| `api_keys` | id (auto-increment) | API key untuk autentikasi device |
| `patients` | id (auto-increment) | Data pasien |
| `medical_records` | id (auto-increment) | Rekam medis pasien |
| `commands` | id (auto-increment) | Perintah start/stop ke device |
| `activity_log` | id (auto-increment) | Log aktivitas sistem |
| `comments` | id (auto-increment) | Komentar instruksi dokter ke nakes |

> Detail lengkap: [DATABASE.md](DATABASE.md)

---

## Sistem Autentikasi

### 1. Web Auth (Session)
- Login via email + password
- Middleware: `auth` (Laravel built-in)
- Role middleware: `RoleMiddleware` → cek `auth()->user()->role`
- Redirect setelah login berdasarkan role

### 2. Device Auth (API Key)
- Header: `X-API-Key: <plain_key>`
- Route param: `{device_id}`
- Middleware: `AuthenticateApiKey` → validasi via `ApiKey::findValidKey()`
- Proses: hash key → cari di DB → verifikasi hash → cek expired → update last_used

---

## API Endpoints

### Device API (API Key Auth)

| Method | Endpoint | Keterangan |
|--------|----------|------------|
| POST | `/api/device/{id}/authenticate` | Autentikasi device |
| GET | `/api/device/{id}/config` | Konfigurasi device (hardcoded) |
| POST | `/api/device/{id}/sensor-data` | Kirim data sensor |
| POST | `/api/device/{id}/system-status` | Kirim status sistem |
| GET | `/api/device/{id}/system-status` | Ambil status sistem |

### Dashboard API (Session Auth)

| Method | Endpoint | Keterangan |
|--------|----------|------------|
| GET | `/api/device/{id}/sensor-data/latest` | Data sensor terbaru |
| GET | `/api/device/{id}/sensor-data/history` | Riwayat sensor (10 menit) |
| GET | `/api/devices` | Daftar semua perangkat |
| GET | `/api/comments` | Komentar per device |
| POST | `/api/comments` | Kirim komentar |
| PATCH | `/api/comments/{id}/respond` | Respon komentar |

### Legacy

| Method | Endpoint | Keterangan |
|--------|----------|------------|
| GET | `/api/sensor-data/{id}/latest` | Data sensor terbaru (legacy) |

---

## Service Layer

### DeviceService
- `storeSensorData(array)` — Simpan data sensor, update status device ke online
- `getLatestSensorData(string)` — Ambil data terakhir (cache 5 menit)
- `storeSystemStatus(array)` — Upsert status sistem (battery, signal)
- `getSystemStatus(string)` — Ambil status sistem (cache 2 menit)
- `getDeviceDetail(string)` — Detail device dengan eager load

### AuthService
- `login(array, Request)` — Autentikasi session, redirect by role
- `generateResetToken(string)` — Buat token reset password
- `sendPasswordResetEmail(string, string)` — Kirim email reset
- `validateResetToken(string, string)` — Validasi token (60 menit expiry)
- `resetPassword(string, string, string)` — Update password
- `logout(Request)` — Logout, invalidate session

### UserService
- `createUser(array)` — Buat user baru
- `updateUser(User, array)` — Update user
- `deleteUser(User)` — Hapus user

---

## Simulator (`simulasi_py/`)

Simulator Python untuk menguji API tanpa hardware IoT.

### Cara Pakai
```bash
cd simulasi_py
pip install -r requirements.txt
python simulator.py
```

### Konfigurasi (`config.py`)
- `BASE_URL` — URL API Laravel
- `DEVICE_ID` — ID device yang di-simulasikan
- `API_KEY` — API key device
- `SEND_INTERVAL` — Interval pengiriman data (detik)
- `THRESHOLDS` — Threshold warning/critical untuk setiap vital sign

### Fungsi Simulator
1. Autentikasi ke API
2. Kirim system status (battery, signal)
3. Loop kirim sensor data setiap N detik
4. Distribusi data: 90% normal, 7% warning, 3% critical

---

## Kondisi Saat Ini

### Sudah Dikerjakan

- [x] **Autentikasi & Otorisasi**
  - Login, logout, forgot/reset password
  - Role middleware (nakes, dokter, superadmin)
  - API key authentication untuk device IoT
  - Redirect berdasarkan role

- [x] **Database & Migrasi**
  - 12 migrasi (users, devices, sensor_datas, system_statuses, api_keys, patients, medical_records, commands, activity_log, comments, cache, jobs)
  - ERD dan relasi lengkap
  - Seeder: 3 user + 2 device + 2 API key

- [x] **Model Eloquent**
  - 10 model: User, Devices, SensorData, SystemStatus, ApiKey, Patient, MedicalRecord, Command, ActivityLog, Comment
  - Relasi sudah didefinisikan
  - Scope dan accessor pada SensorData

- [x] **API Sensor Data**
  - POST sensor data dari device
  - GET latest sensor data (dengan caching)
  - GET sensor data history (untuk chart)
  - GET system status

- [x] **API Komentar**
  - GET komentar per device
  - POST komentar baru (dokter)
  - PATCH respon komentar (nakes)

- [x] **Manajemen Alat (Superadmin)**
  - CRUD perangkat: tambah, detail, hapus
  - Auto-generate API key saat tambah device
  - API key ditampilkan sekali saat registrasi

- [x] **Dashboard API**
  - GET `/api/devices` — daftar semua perangkat (polling dropdown)
  - Realtime update device list tanpa refresh

- [x] **Service Layer**
  - DeviceService (sensor data + caching)
  - AuthService (login, logout, reset password)
  - UserService (CRUD user)

- [x] **Form Request Validation**
  - 7 form request class untuk validasi input

- [x] **Simulator Python**
  - Simulator IoT device untuk testing
  - Konfigurasi per device
  - Distribusi data realistis (normal/warning/critical)

- [x] **Bug Fixes (10 Mei 2026)**
  - Fix chart flickering: skip update jika data tidak berubah
  - Fix komentar checklist reset: preserve checked state saat poll
  - Fix polling interval: seragam 5 detik (sebelumnya 2s/10s tidak konsisten)
  - Fix device list: polling `/api/devices` setiap 10 detik

### Belum Dikerjakan

- [ ] **Backend Input Data Pasien**
  - POST handler untuk form input data pasien
  - Simpan ke tabel `patients`

- [ ] **Backend CRUD User (Routing)**
  - UserController sudah ada method-nya
  - Belum ada route di web.php
  - Belum terhubung ke halaman manajemen-user

- [ ] **Laporan dari Database**
  - LaporanController masih pakai dummy data
  - SuperadminLaporanController masih pakai dummy data
  - Query database sudah ada (commented out) tapi belum diaktifkan

- [ ] **Device Config dari Database**
  - `getDeviceConfig()` masih hardcoded
  - Perlu baca dari database

- [ ] **Commands (Start/Stop Device)**
  - Model dan migrasi sudah ada
  - Belum ada controller dan route
  - Belum ada UI untuk kirim perintah

- [ ] **Activity Log**
  - Model dan migrasi sudah ada
  - Belum ada yang menulis ke tabel ini
  - Belum ada controller dan route

- [ ] **Integrasi IoT Real**
  - Simulator sudah jalan
  - Perlu testing dengan hardware asli
  - Konfigurasi MQTT/HTTP pada device

- [ ] **Machine Learning**
  - Prediksi kondisi pasien
  - Endpoint `/api/device/{id}/prediction`
  - Integrasi model ML ke pipeline data

---

## Rencana Perbaikan (Prioritas)

### 1. Realtime & Delay Fix
- Kurangi delay pengiriman data simulator ke dashboard
- Optimasi polling: pertimbangkan SSE (Server-Sent Events) sebagai alternatif polling
- Hapus atau kurangi cache TTL pada DeviceService (saat ini 5 menit)

### 2. Notifikasi Komentar
- Notifikasi "Komentar terkirim" saat dokter mengirim komentar
- Warning/highlight saat komentar dichecklist nakes
- Badge counter komentar aktif di sidebar

### 3. Backend Laporan
- Aktifkan query database di LaporanController
- Aktifkan query database di SuperadminLaporanController
- Hapus dummy data

### 4. CRUD User Routing
- Daftarkan route untuk UserController
- Hubungkan ke halaman manajemen-user

### 5. Commands & Activity Log
- Buat controller untuk commands (start/stop device)
- Buat activity logging di setiap aksi penting

---

## Notes

- Komentar endpoint ada di `web.php` (session auth), bukan `api.php` — disengaja karena diakses dari dashboard frontend
- `UserController` punya method tapi belum ada route di `web.php`
- `LaporanController` dan `SuperadminLaporanController` masih pakai dummy data
- `DeviceAuthController@getDeviceConfig` mengembalikan nilai hardcoded
- Cache DeviceService: sensor data 5 menit, system status 2 menit — cleared on write

---

*Last updated: 10 Mei 2026*
