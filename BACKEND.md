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
        DeviceDataController.php        # Autentikasi device, system status, device config
        SensorDataController.php        # Sensor data endpoint (store, latest, history)
        InstructionController.php       # Instruksi dokter-nakes (CRUD + report + complete)
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
      StoreInstructionRequest.php
      UpdateInstructionRequest.php
      StoreInstructionReportRequest.php
      CompleteInstructionRequest.php
  Models/
    User.php                            # users
    Devices.php                         # devices (PK: device_id string)
    SensorData.php                      # sensor_datas
    SystemStatus.php                    # system_statuses
    ApiKey.php                          # api_keys
    Patient.php                         # patients
    MedicalRecord.php                   # medical_records
    ActivityLog.php                     # activity_log
    Instruction.php                     # instructions
  Services/
    AuthService.php                     # Login, logout, reset password
    DeviceService.php                   # Sensor data CRUD + caching
    UserService.php                     # User CRUD
    InstructionService.php              # Instruksi dokter-nakes business logic
    SensorService.php                   # Sensor data operations + caching
  Events/
    InstructionSent.php                 # Broadcast saat dokter kirim instruksi
    InstructionStatusUpdated.php        # Broadcast saat nakes selesaikan instruksi
    InstructionReportSubmitted.php      # Broadcast saat nakes submit laporan
  Jobs/
    ProcessDeviceData.php               # Queue: proses system status device
    ProcessSensorData.php               # Queue: proses sensor data
database/
  migrations/                           # 11 file migrasi
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

### Tabel Utama (8 tabel domain)

| Tabel | Primary Key | Keterangan |
|-------|-------------|------------|
| `users` | id (auto-increment) | Akun pengguna (role: nakes/dokter/superadmin) |
| `devices` | device_id (string 50) | Perangkat IoT terdaftar |
| `sensor_datas` | id (auto-increment) | Data vital sign dari sensor |
| `system_statuses` | device_id (string) | Status sistem perangkat (battery, signal) |
| `api_keys` | id (auto-increment) | API key untuk autentikasi device |
| `patients` | id (auto-increment) | Data pasien |
| `medical_records` | id (auto-increment) | Rekam medis pasien |
| `activity_log` | id (auto-increment) | Log aktivitas sistem |
| `instructions` | id (auto-increment) | Instruksi dokter-nakes + laporan nakes |

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
| GET | `/api/device/{id}/sensor-data/latest` | Data sensor terbaru |
| GET | `/api/device/{id}/sensor-data/history` | Riwayat sensor |
| POST | `/api/device/{id}/system-status` | Kirim status sistem |
| GET | `/api/device/{id}/system-status` | Ambil status sistem |

### Instruction API (Session Auth)

| Method | Endpoint | Keterangan |
|--------|----------|------------|
| GET | `/api/instruction` | Ambil instruksi per device |
| POST | `/api/instruction` | Kirim instruksi (dokter) |
| POST | `/api/instruction/report` | Submit laporan (nakes) |
| PATCH | `/api/instruction/{id}` | Update instruksi (dokter) |
| PATCH | `/api/instruction/{id}/complete` | Selesaikan instruksi (nakes) |

### Dashboard API (Session Auth)

| Method | Endpoint | Keterangan |
|--------|----------|------------|
| GET | `/api/devices` | Daftar semua perangkat |
| GET | `/api/device/{id}/sensor-data/latest` | Data sensor terbaru (session auth) |
| GET | `/api/device/{id}/sensor-data/history` | Riwayat sensor (session auth) |

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

### InstructionService
- `getInstructions(string)` — Ambil instruksi per device (dengan relasi dokter/nakes)
- `storeInstruction(array)` — Buat instruksi baru (dokter), broadcast `InstructionSent`
- `completeInstruction(Instruction, string)` — Selesaikan instruksi (nakes), broadcast `InstructionStatusUpdated`
- `updateInstruction(Instruction, array)` — Update instruksi (dokter), broadcast `InstructionSent`
- `storeReport(array)` — Submit laporan nakes, broadcast `InstructionReportSubmitted`

### SensorService
- `storeSensorData(array)` — Simpan data sensor + update device status
- `getLatestSensorData(string)` — Ambil data terakhir (cache 5 menit)

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
  - 11 migrasi (users, devices, sensor_datas, system_statuses, api_keys, patients, medical_records, activity_log, instructions, cache, jobs)
  - ERD dan relasi lengkap
  - Seeder: 3 user + 2 device + 2 API key

- [x] **Model Eloquent**
  - 9 model: User, Devices, SensorData, SystemStatus, ApiKey, Patient, MedicalRecord, ActivityLog, Instruction
  - Relasi sudah didefinisikan
  - Scope dan accessor pada SensorData

- [x] **API Sensor Data**
  - POST sensor data dari device
  - GET latest sensor data (dengan caching)
  - GET sensor data history (untuk chart)
  - GET system status

- [x] **API Instruksi**
  - GET instruksi per device
  - POST instruksi baru (dokter)
  - POST laporan nakes
  - PATCH update instruksi (dokter)
  - PATCH selesaikan instruksi (nakes)
  - Broadcasting events (InstructionSent, InstructionStatusUpdated, InstructionReportSubmitted)

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
  - InstructionService (instruksi dokter-nakes + broadcasting)
  - SensorService (sensor data operations + caching)

- [x] **Events & Jobs**
  - InstructionSent, InstructionStatusUpdated, InstructionReportSubmitted (broadcasting)
  - ProcessDeviceData, ProcessSensorData (queue processing)

- [x] **Queue & Broadcasting**
  - Redis (predis/predis v3.4) untuk queue
  - Laravel Reverb v1.0 untuk WebSocket broadcasting
  - Database queue driver

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

### 2. Notifikasi Instruksi
- Notifikasi "Instruksi terkirim" saat dokter mengirim instruksi
- Warning/highlight saat instruksi diselesaikan nakes
- Badge counter instruksi aktif di sidebar
- Listener broadcasting di frontend (Reverb sudah terkonfigurasi)

### 3. Backend Laporan
- Aktifkan query database di LaporanController
- Aktifkan query database di SuperadminLaporanController
- Hapus dummy data

### 4. CRUD User Routing
- Daftarkan route untuk UserController
- Hubungkan ke halaman manajemen-user

### 5. Activity Log
- Buat activity logging di setiap aksi penting
- Model dan migrasi sudah ada

---

## Notes

- Instruction endpoint ada di `api.php` dengan middleware `web` + `auth` (session auth)
- `UserController` punya method tapi belum ada route di `web.php`
- `LaporanController` dan `SuperadminLaporanController` masih pakai dummy data
- `DeviceDataController@getDeviceConfig` mengembalikan nilai hardcoded
- Cache DeviceService: sensor data 5 menit, system status 2 menit — cleared on write
- System status menggunakan queue (`ProcessDeviceData`) untuk async processing
- Broadcasting via Reverb sudah terkonfigurasi, perlu listener di frontend

---

*Last updated: 14 Mei 2026*
