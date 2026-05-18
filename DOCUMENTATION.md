# SATS - Smart Ambulance Telemedicine System

> Sistem telemedicine ambulans cerdas yang mengintegrasikan perangkat IoT dengan web dashboard untuk memantau tanda vital pasien secara real-time selama transportasi ambulans.

---

## Daftar Isi

1. [Gambaran Umum](#1-gambaran-umum)
2. [Teknologi yang Digunakan](#2-teknologi-yang-digunakan)
3. [Arsitektur Sistem](#3-arsitektur-sistem)
4. [Sistem Role & Akses](#4-sistem-role--akses)
5. [Struktur Direktori](#5-struktur-direktori)
6. [Database Schema](#6-database-schema)
7. [Eloquent Models](#7-eloquent-models)
8. [Controllers](#8-controllers)
9. [Service Layer](#9-service-layer)
10. [Middleware](#10-middleware)
11. [Events & Jobs](#11-events--jobs)
12. [Routes](#12-routes)
13. [Frontend Views](#13-frontend-views)
14. [IoT Simulator](#14-iot-simulator)
15. [Panduan Setup](#15-panduan-setup)
16. [Panduan Penggunaan](#16-panduan-penggunaan)
17. [Status Fitur](#17-status-fitur)
18. [Troubleshooting](#18-troubleshooting)

---

## 1. Gambaran Umum

SATS (Smart Ambulance Telemedicine System) adalah sistem yang dirancang untuk:

- **Memantau tanda vital pasien** (heart rate, SpO2, suhu tubuh) secara real-time selama perjalanan ambulans
- **Menghubungkan perawat (nakes) di ambulans dengan dokter** di rumah sakit tujuan melalui workflow instruksi
- **Menyediakan dashboard monitoring** dengan grafik interaktif dan notifikasi kondisi kritis
- **Menghasilkan laporan medis** dalam format HTML dan PDF
- **Mengelola perangkat IoT** yang terpasang pada ambulans

### Alur Sistem End-to-End

```
Perangkat IoT (Sensor HR, SpO2, Suhu)
        |
        v
HTTP POST ke API Laravel
        |
        v
WebSocket Broadcast (real-time)  +  Queue Job (simpan ke DB)
        |
        v
Dashboard Nakes/Dokter (polling + grafik real-time)
        |
        v
Dokter mengirim instruksi --> Nakes merespon
        |
        v
Laporan Medis + PDF
```

---

## 2. Teknologi yang Digunakan

| Layer | Teknologi | Versi |
|---|---|---|
| **Backend** | Laravel | 12.x |
| **PHP** | PHP | 8.2+ |
| **Frontend** | Blade Templates | - |
| **CSS Framework** | Tailwind CSS | 4.2 |
| **JavaScript** | Alpine.js | 3.x (CDN) |
| **Charting** | Chart.js | 4.4.1 (CDN) |
| **PDF Generator** | barryvdh/laravel-dompdf | 3.1 |
| **Build Tool** | Vite | 6.x |
| **Database** | MySQL | 8.x |
| **Cache** | Redis (predis/predis) | 3.4 |
| **WebSocket** | Laravel Reverb | 1.0 |
| **Realtime Client** | Laravel Echo + Pusher.js | 2.3.4 / 8.5.0 |
| **IoT Simulator** | Python 3 + requests | - |

### Dependency Utama (Composer)

```json
{
    "laravel/framework": "^12.0",
    "laravel/reverb": "^1.0",
    "barryvdh/laravel-dompdf": "^3.1",
    "predis/predis": "^3.4"
}
```

### Dependency Utama (NPM)

```json
{
    "@tailwindcss/vite": "^4.0.0",
    "laravel-echo": "^2.3.4",
    "pusher-js": "^8.5.0",
    "vite": "^6.0.11"
}
```

---

## 3. Arsitektur Sistem

### Pola Arsitektur yang Digunakan

| Pola | Deskripsi |
|---|---|
| **Service Layer** | Logika bisnis dipisah ke service class (AuthService, DeviceService, SensorService, UserService, InstructionService) |
| **Queue-based Write** | Penulisan sensor data & system status melalui queued job untuk response API yang cepat |
| **Redis Caching** | API key (30 menit), sensor data (5 menit), system status (2 menit) |
| **Dead Letter Queue** | Job yang gagal setelah 3 retry disimpan di tabel `failed_sensor_datas` |
| **Idempotency** | Semua POST endpoint memerlukan `Idempotency-Key` header dengan atomic lock |
| **Dual Authentication** | Endpoint monitoring menerima session auth (dokter) atau API key (nakes) |
| **Role-based Views** | DashboardController me-resolve view berdasarkan role user |

### Alur Penulisan Sensor Data

```
IoT Device
    |
    v
POST /api/device/{id}/sensor-data
    |
    v
Middleware: apikey -> throttle.api -> idempotent
    |
    v
SensorDataController::storeSensorData()
    |
    +---> Broadcast SensorDataReceived (WebSocket, langsung)
    |
    +---> Dispatch ProcessSensorData (Queue, async)
              |
              v
         Simpan ke database
         Update device status
         Clear Redis cache
```

---

## 4. Sistem Role & Akses

| Role | Akses | Fitur Utama |
|---|---|---|
| `nakes` (Perawat) | `/nakes/*` | Monitoring dashboard, respon instruksi dokter, input data pasien, laporan |
| `dokter` | `/dokter/*` | Monitoring dashboard, kirim instruksi ke nakes, input data pasien, laporan |
| `superadmin` | `/superadmin/*` | Dashboard admin, manajemen alat, manajemen user, laporan |

### Akun Default (Seeder)

| Role | Nama | Email | Password |
|---|---|---|---|
| `superadmin` | Super Admin | `admin@sats.id` | `password` |
| `dokter` | Dr. Andi | `andi@sats.id` | `password` |
| `nakes` | Suster Rina | `rina@sats.id` | `password` |

### Perbedaan Nakes vs Dokter

- **Dashboard identik** — sama-sama monitoring real-time dengan grafik
- **Dokter** memiliki container komentar untuk mengirim instruksi ke nakes
- **Nakes** memiliki dropdown respon instruksi dokter dan checklist

---

## 5. Struktur Direktori

```
SATS/
├── app/
│   ├── Events/                    # WebSocket broadcast events
│   │   ├── SensorDataReceived.php
│   │   ├── InstructionSent.php
│   │   ├── InstructionStatusUpdated.php
│   │   └── InstructionReportSubmitted.php
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Api/
│   │   │   │   ├── DeviceDataController.php
│   │   │   │   ├── SensorDataController.php
│   │   │   │   └── InstructionController.php
│   │   │   ├── AuthController.php
│   │   │   ├── DashboardController.php
│   │   │   ├── LaporanController.php
│   │   │   ├── ManajemenAlatController.php
│   │   │   ├── SuperadminLaporanController.php
│   │   │   └── UserController.php
│   │   ├── Middleware/
│   │   │   ├── AuthenticateApiKey.php
│   │   │   ├── AuthenticateMonitoringAccess.php
│   │   │   ├── IdempotentRequest.php
│   │   │   ├── RoleMiddleware.php
│   │   │   ├── ThrottleApiRequests.php
│   │   │   └── ValidateRequestSignature.php
│   │   └── Requests/              # 13 form request validation
│   ├── Jobs/
│   │   ├── ProcessSensorData.php
│   │   └── ProcessDeviceData.php
│   ├── Mail/
│   │   └── ResetPasswordMail.php
│   ├── Models/                    # 10 Eloquent models
│   ├── Policies/
│   │   └── UserPolicy.php
│   └── Services/
│       ├── AuthService.php
│       ├── DeviceService.php
│       ├── InstructionService.php
│       ├── SensorService.php
│       └── UserService.php
├── config/
│   ├── roles.php                  # Mapping role ke redirect URL
│   └── reverb.php                 # Konfigurasi WebSocket
├── database/
│   ├── migrations/                # 12 migrasi
│   └── seeders/
│       ├── UserSeeder.php
│       ├── DeviceSeeder.php
│       └── DatabaseSeeder.php
├── public/
│   └── assets/
│       ├── logo.png
│       ├── ambulance_1.jpg
│       ├── ambulance_2.jpg
│       ├── dokter.jpg
│       ├── vital_sign.jpg
│       └── sounds/notification.mp3
├── resources/
│   ├── css/app.css                # Tailwind CSS v4
│   ├── js/
│   │   ├── app.js
│   │   └── bootstrap.js           # Laravel Echo + Reverb config
│   └── views/
│       ├── components/
│       │   ├── navbar.blade.php
│       │   └── sidebar.blade.php
│       ├── layouts/
│       │   ├── app.blade.php
│       │   └── auth.blade.php
│       └── pages/
│           ├── login.blade.php
│           ├── auth/
│           ├── nakes/             # 6 view files
│           ├── dokter/            # 6 view files
│           └── superadmin/        # 5 view files
├── routes/
│   ├── api.php
│   ├── web.php
│   ├── channels.php
│   └── console.php
├── simulasi_py/                   # IoT Simulator (Python)
│   ├── config.py
│   ├── simulator.py
│   └── requirements.txt
├── BACKEND.md
├── DATABASE.md
├── DEVICE_HARDWARE_TO_SYSTEM.md
├── FRONTEND.md
├── MASTER_BRIEF.md
├── API_ENDPOINTS.md
└── DOCUMENTATION.md               # File ini
```

---

## 6. Database Schema

### Entity Relationship

```
users (1) ──────< patients (Many) ──────< medical_records (Many)
  |                    |
  |                    +── device_id ──> devices
  |
  +──< instructions (Many, via dokter_id / nakes_id)

devices (1) ──< sensor_datas (Many)
  |
  +──< system_statuses (1)
  +──< api_keys (Many)
  +──< instructions (Many)
```

### Tabel-Tabel Utama

#### `devices`
| Kolom | Tipe | Keterangan |
|---|---|---|
| `device_id` | string(50), PK | ID perangkat (contoh: `DEVICE_01`) |
| `status` | enum: online/offline | Status koneksi perangkat |
| `last_seen` | timestamp | Terakhir perangkat mengirim data |

#### `sensor_datas`
| Kolom | Tipe | Keterangan |
|---|---|---|
| `id` | bigint, PK, auto-increment | |
| `device_id` | string(50), FK -> devices | |
| `heart_rate` | int | Detak jantung (BPM) |
| `spo2` | int | saturasi oksigen (%) |
| `temperature` | float | Suhu tubuh (Celsius) |
| `status` | enum: normal/warning/critical | Klasifikasi kondisi |
| `prediction` | string(50) | Hasil prediksi ML |
| `created_at` | timestamp | Waktu pencatatan |

#### `system_statuses`
| Kolom | Tipe | Keterangan |
|---|---|---|
| `device_id` | string(50), PK, FK -> devices | |
| `monitoring_status` | enum: active/inactive | |
| `battery_level` | int (0-100) | Level baterai perangkat |
| `signal_strength` | int | Kekuatan sinyal (RSSI) |

#### `api_keys`
| Kolom | Tipe | Keterangan |
|---|---|---|
| `id` | bigint, PK | |
| `device_id` | string(50), FK -> devices | |
| `key_hash` | string, unique | Hash dari API key |
| `name` | string | Nama/API key |
| `is_active` | boolean | Status aktif |
| `rate_limit_per_minute` | int, default 60 | Rate limit per menit |
| `last_used` | timestamp | Terakhir digunakan |
| `last_used_ip` | string | IP terakhir |
| `expires_at` | timestamp | Waktu kadaluarsa |

#### `patients`
| Kolom | Tipe | Keterangan |
|---|---|---|
| `id` | bigint, PK | |
| `device_id` | string(50), FK -> devices | |
| `nama` | string | Nama pasien |
| `jenis_kelamin` | string | Jenis kelamin |
| `umur` | int | Umur pasien |
| `catatan_tambahan` | text, nullable | Catatan tambahan |
| `nakes_id` | bigint, FK -> users | Perawat yang menginput |

#### `medical_records`
| Kolom | Tipe | Keterangan |
|---|---|---|
| `id` | bigint, PK | |
| `patient_id` | bigint, FK -> patients | |
| `device_id` | string(50), FK -> devices | |
| `heart_rate` | int | |
| `spo2` | int | |
| `temperature` | float | |
| `status` | enum | normal/warning/critical |
| `prediction` | string | |

#### `instructions`
| Kolom | Tipe | Keterangan |
|---|---|---|
| `id` | bigint, PK | |
| `device_id` | string(50), FK -> devices | |
| `dokter_id` | bigint, FK -> users, nullable | Dokter pembuat instruksi |
| `nakes_id` | bigint, FK -> users, nullable | Nakes yang dituju |
| `instruksi_dokter` | text | Isi instruksi dokter |
| `respon_nakes` | text, nullable | Respon dari nakes |
| `laporan_nakes` | text, nullable | Laporan dari nakes |
| `is_completed` | boolean | Status selesai |
| `completed_at` | timestamp | Waktu diselesaikan |
| `completed_by` | bigint, FK -> users | Siapa yang menyelesaikan |

#### `activity_log`
| Kolom | Tipe | Keterangan |
|---|---|---|
| `id` | bigint, PK | |
| `message` | text | Pesan log |
| `created_at` | timestamp | |

#### `failed_sensor_datas` (Dead Letter Queue)
| Kolom | Tipe | Keterangan |
|---|---|---|
| `id` | bigint, PK | |
| `device_id` | string(50), FK -> devices | |
| `payload` | json | Data asli yang gagal diproses |
| `error_message` | text | Pesan error |
| `retry_count` | int | Jumlah percobaan ulang |
| `last_retry_at` | timestamp | Terakhir dicoba ulang |
| `failed_at` | timestamp | Waktu kegagalan |

---

## 7. Eloquent Models

| Model | Tabel | Fitur Utama |
|---|---|---|
| `User` | `users` | Fillable: name, email, password, role. Relasi: patients |
| `Devices` | `devices` | PK string (`device_id`). Relasi: sensorData, systemStatus, apiKeys, patients, medicalRecords, instructions |
| `SensorData` | `sensor_datas` | Scope: `latest($deviceId)`, `withinRange($deviceId, $from, $to)`, `onlyVitals()`. Accessor: `statusBadge` |
| `SystemStatus` | `system_statuses` | PK string. Method: `isBatteryLow()`, `isSignalWeak()` |
| `ApiKey` | `api_keys` | Static: `hashKey()`, `findValidKey()`. Instance: `verifyKey()`, `isValid()` |
| `Patient` | `patients` | Relasi: device, nakes (User), medicalRecords |
| `MedicalRecord` | `medical_records` | Relasi: patient, device |
| `Instruction` | `instructions` | Relasi: dokter, nakes, creator (User), device |
| `ActivityLog` | `activity_log` | Tanpa updated_at |
| `FailedSensorData` | `failed_sensor_datas` | Method: `incrementRetry()`. Cast payload as array |

---

## 8. Controllers

### Web Controllers

| Controller | File | Fungsi |
|---|---|---|
| `AuthController` | `app/Http/Controllers/AuthController.php` | Login, logout, forgot password, reset password. Menggunakan AuthService |
| `DashboardController` | `app/Http/Controllers/DashboardController.php` | Resolver view berdasarkan role (`getViewByRole()`). Method: `viewDashboardPage()`, `viewManajemenUserPage()`, `viewInputDataPasienPage()`, `getDevicesApi()` |
| `ManajemenAlatController` | `app/Http/Controllers/ManajemenAlatController.php` | CRUD perangkat untuk superadmin. `store()` auto-generate API key |
| `LaporanController` | `app/Http/Controllers/LaporanController.php` | Laporan HTML + PDF untuk nakes/dokter. Role-aware. **Saat ini menggunakan data dummy** |
| `SuperadminLaporanController` | `app/Http/Controllers/SuperadminLaporanController.php` | Laporan HTML + PDF untuk superadmin. **Saat ini menggunakan data dummy** |
| `UserController` | `app/Http/Controllers/UserController.php` | CRUD user. **Belum ada route di web.php** |

### API Controllers

| Controller | File | Fungsi |
|---|---|---|
| `DeviceDataController` | `app/Http/Controllers/Api/DeviceDataController.php` | `listDevices()`, `registerDevice()`, `authenticate()`, `storeSystemStatus()`, `getDeviceConfig()` |
| `SensorDataController` | `app/Http/Controllers/Api/SensorDataController.php` | `storeSensorData()` (broadcast + queue), `storeSensorDataBatch()`, `getLatestSensorData()` (Redis cache), `getSensorDataHistory()` |
| `InstructionController` | `app/Http/Controllers/Api/InstructionController.php` | `index()`, `store()`, `storeReport()`, `update()`, `complete()`. Menggunakan InstructionService |

---

## 9. Service Layer

| Service | File | Fungsi |
|---|---|---|
| `AuthService` | `app/Services/AuthService.php` | Login (session + role redirect), generate reset token, kirim email reset, validasi token (60 menit), reset password, logout |
| `DeviceService` | `app/Services/DeviceService.php` | `storeSystemStatus()` (updateOrCreate + cache clear), `getSystemStatus()` (cache 2 menit), `getDeviceDetail()` (eager load) |
| `SensorService` | `app/Services/SensorService.php` | `storeSensorData()` (update status + insert + clear cache), `storeSensorDataBatch()`, `getLatestSensorData()` (cache 5 menit) |
| `UserService` | `app/Services/UserService.php` | `createUser()`, `updateUser()`, `deleteUser()` |
| `InstructionService` | `app/Services/InstructionService.php` | `getInstructions()`, `storeInstruction()` + broadcast, `completeInstruction()` + broadcast, `storeReport()` + broadcast, `updateInstruction()` + broadcast |

---

## 10. Middleware

| Alias | Class | Fungsi |
|---|---|---|
| `role` | `RoleMiddleware` | Mengecek `auth()->user()->role` terhadap role yang diizinkan |
| `apikey` | `AuthenticateApiKey` | Validasi header `X-API-Key`. Cache Redis 30 menit (keyed by deviceId + sha256(apiKey)) |
| `throttle.api` | `ThrottleApiRequests` | Rate limiting per API key. Default 60 req/menit. Header: `X-RateLimit-*` |
| `idempotent` | `IdempotentRequest` | Wajibkan `Idempotency-Key` header pada POST/PUT/PATCH. Atomic lock, cache response 24 jam |
| `sign.verify` | `ValidateRequestSignature` | Validasi HMAC-SHA256 signature (opsional, saat ini dikomentari) |
| `monitoring.auth` | `AuthenticateMonitoringAccess` | Dual auth: session (dokter) atau API key (nakes) |

---

## 11. Events & Jobs

### Events (WebSocket Broadcasting)

| Event | Channel | Trigger |
|---|---|---|
| `SensorDataReceived` | `private-device.{device_id}` | Saat sensor data diterima. Broadcast langsung (`ShouldBroadcastNow`) |
| `InstructionSent` | `private-device.{device_id}` | Saat dokter mengirim instruksi |
| `InstructionStatusUpdated` | `private-device.{device_id}` | Saat nakes menyelesaikan instruksi |
| `InstructionReportSubmitted` | `private-device.{device_id}` | Saat nakes mengirim laporan |

### Jobs (Queue Processing)

| Job | Fungsi | Retry | Timeout |
|---|---|---|---|
| `ProcessSensorData` | Simpan sensor data ke DB, update device status, clear cache | 3x (10s, 60s, 5min) | 30s |
| `ProcessDeviceData` | Simpan system status ke DB | 3x (10s, 60s, 5min) | 30s |

Kedua job menyimpan data gagal ke tabel `failed_sensor_datas` (dead letter queue) setelah semua retry habis.

### Broadcast Channel

```php
// routes/channels.php
Broadcast::channel('device.{deviceId}', function ($user, $deviceId) {
    return in_array($user->role, ['dokter', 'nakes']) && Devices::where('device_id', $deviceId)->exists();
});
```

---

## 12. Routes

### Web Routes (`routes/web.php`)

#### Publik
| Method | URI | Controller | Nama Route |
|---|---|---|---|
| GET | `/` | Welcome page | - |
| GET | `/login` | `AuthController@viewLoginPage` | `login` |
| POST | `/login` | `AuthController@login` | `login.process` |
| GET | `/forgot-password` | `AuthController@showForgotPassword` | `password.forgot` |
| POST | `/forgot-password` | `AuthController@forgotPassword` | `password.email` |
| GET | `/reset-password` | `AuthController@showResetPassword` | `password.reset` |
| POST | `/reset-password` | `AuthController@resetPassword` | `password.update` |

#### Autentikasi (middleware: `auth`)
| Method | URI | Controller | Nama Route |
|---|---|---|---|
| POST | `/logout` | `AuthController@logout` | `logout` |
| GET | `/api/devices` | `DashboardController@getDevicesApi` | - |

#### Nakes (prefix: `/nakes`, middleware: `role:nakes`)
| Method | URI | Controller | Nama Route |
|---|---|---|---|
| GET | `/nakes/dashboard` | `DashboardController@viewDashboardPage` | `dashboard` |
| GET | `/nakes/input-data-pasien` | `DashboardController@viewInputDataPasienPage` | `input-data-pasien` |
| GET | `/nakes/laporan` | `LaporanController@index` | `laporan.index` |
| GET | `/nakes/laporan/pdf` | `LaporanController@pdf` | `laporan.pdf` |
| GET | `/nakes/instruksi` | View closure | `nakes.instruksi` |
| GET | `/nakes/monitoring` | View closure | `nakes.monitoring` |

#### Dokter (prefix: `/dokter`, middleware: `role:dokter`)
| Method | URI | Controller | Nama Route |
|---|---|---|---|
| GET | `/dokter/dashboard` | `DashboardController@viewDashboardPage` | `dokter.dashboard` |
| GET | `/dokter/input-data-pasien` | `DashboardController@viewInputDataPasienPage` | `dokter.input-data-pasien` |
| GET | `/dokter/laporan` | `LaporanController@index` | `dokter.laporan` |
| GET | `/dokter/laporan/pdf` | `LaporanController@pdf` | `dokter.laporan.pdf` |
| GET | `/dokter/instruksi` | View closure | `dokter.instruksi` |
| GET | `/dokter/monitoring` | View closure | `dokter.monitoring` |

#### Superadmin (prefix: `/superadmin`, middleware: `role:superadmin`)
| Method | URI | Controller | Nama Route |
|---|---|---|---|
| GET | `/superadmin/dashboard` | `DashboardController@viewDashboardPage` | `superadmin.dashboard` |
| GET | `/superadmin/manajemen-alat` | `ManajemenAlatController@index` | `superadmin.manajemen-alat` |
| POST | `/superadmin/manajemen-alat` | `ManajemenAlatController@store` | `superadmin.manajemen-alat.store` |
| DELETE | `/superadmin/manajemen-alat/{device_id}` | `ManajemenAlatController@destroy` | `superadmin.manajemen-alat.destroy` |
| GET | `/superadmin/manajemen-alat/{device_id}` | `ManajemenAlatController@show` | `superadmin.manajemen-alat.show` |
| GET | `/superadmin/manajemen-user` | `DashboardController@viewManajemenUserPage` | `superadmin.manajemen-user` |
| GET | `/superadmin/input-data-pasien` | `DashboardController@viewInputDataPasienPage` | `superadmin.input-data-pasien` |
| GET | `/superadmin/laporan` | `SuperadminLaporanController@index` | `superadmin.laporan` |
| GET | `/superadmin/laporan/pdf` | `SuperadminLaporanController@pdf` | `superadmin.laporan.pdf` |

### API Routes (`routes/api.php`)

#### Device Auth (middleware: `apikey`)
| Method | URI | Controller |
|---|---|---|
| POST | `/api/device/{device_id}/authenticate` | `DeviceDataController@authenticate` |
| POST | `/api/device/register` | `DeviceDataController@registerDevice` (tanpa middleware) |

#### Device Data (middleware: `apikey`, `throttle.api`)
| Method | URI | Controller | Middleware Tambahan |
|---|---|---|---|
| GET | `/api/device/{device_id}/config` | `DeviceDataController@getDeviceConfig` | - |
| POST | `/api/device/{device_id}/sensor-data` | `SensorDataController@storeSensorData` | `idempotent` |
| POST | `/api/device/{device_id}/sensor-data/batch` | `SensorDataController@storeSensorDataBatch` | `idempotent` |
| POST | `/api/device/{device_id}/system-status` | `DeviceDataController@storeSystemStatus` | `idempotent` |

#### Monitoring (middleware: `web`, `monitoring.auth`)
| Method | URI | Controller |
|---|---|---|
| GET | `/api/device` | `DeviceDataController@listDevices` |
| GET | `/api/device/{device_id}/sensor-data/latest` | `SensorDataController@getLatestSensorData` |
| GET | `/api/device/{device_id}/sensor-data/history` | `SensorDataController@getSensorDataHistory` |

#### Instructions (middleware: `web`, `auth`)
| Method | URI | Controller |
|---|---|---|
| GET | `/api/instruction` | `InstructionController@index` |
| POST | `/api/instruction` | `InstructionController@store` |
| POST | `/api/instruction/report` | `InstructionController@storeReport` |
| PATCH | `/api/instruction/{instruction}` | `InstructionController@update` |
| PATCH | `/api/instruction/{instruction}/complete` | `InstructionController@complete` |

---

## 13. Frontend Views

### Layouts & Components

| File | Fungsi |
|---|---|
| `layouts/app.blade.php` | Layout utama dengan navbar, sidebar, content area. Load Tailwind CSS, Alpine.js, Vite |
| `layouts/auth.blade.php` | Layout halaman autentikasi |
| `components/navbar.blade.php` | Navigasi atas: logo, nama user, role badge, tombol logout. Warna: `rgb(0,75,58)` |
| `components/sidebar.blade.php` | Sidebar dinamis berdasarkan role. Warna: `rgb(0,83,63)`. Active state via `request()->routeIs()` |

### Halaman per Role

#### Nakes (`pages/nakes/`)
| File | Fungsi | Status |
|---|---|---|
| `dashboard.blade.php` | Monitoring real-time: dropdown device (polling 10s), 4 stat cards, Chart.js (polling 5s), container respon instruksi | Terhubung ke API |
| `inputdata.blade.php` | Form input data pasien | UI selesai, belum ada backend |
| `instruksi.blade.php` | Halaman manajemen instruksi | Selesai |
| `monitoring.blade.php` | Halaman monitoring khusus | Selesai |
| `laporan.blade.php` | Laporan medis dengan chart dan filter tanggal | Data dummy |
| `laporan-pdf.blade.php` | Template PDF | Selesai |

#### Dokter (`pages/dokter/`)
| File | Fungsi | Status |
|---|---|---|
| `dashboard.blade.php` | Monitoring + container kirim instruksi ke nakes | Terhubung ke API |
| `inputdata.blade.php` | Form input data pasien | UI selesai, belum ada backend |
| `instruksi.blade.php` | Halaman instruksi | Selesai |
| `monitoring.blade.php` | Halaman monitoring | Selesai |
| `laporan.blade.php` | Laporan medis | Data dummy |
| `laporan-pdf.blade.php` | Template PDF | Selesai |

#### Superadmin (`pages/superadmin/`)
| File | Fungsi | Status |
|---|---|---|
| `dashboard.blade.php` | Stat cards, tabel alat kritis, log aktivitas | UI selesai |
| `manajemen-alat.blade.php` | Inventaris perangkat + CRUD modal | Terhubung ke backend |
| `manajemen-user.blade.php` | Manajemen user + modal | UI selesai, belum ada route |
| `laporan.blade.php` | Laporan + chart + tabel sensor | Data dummy |
| `laporan-pdf.blade.php` | Template PDF landscape | Selesai |

---

## 14. IoT Simulator

Simulator Python tersedia di folder `simulasi_py/` untuk testing tanpa hardware.

### File

| File | Fungsi |
|---|---|
| `config.py` | Konfigurasi: BASE_URL, DEVICE_ID, API_KEY, INTERVAL, SENSOR_CONFIG, THRESHOLDS |
| `simulator.py` | Class `SATSSimulator` — autentikasi, kirim system status, loop kirim sensor data |
| `requirements.txt` | Dependency: `requests` |

### Karakteristik Simulator

- **Distribusi data**: 90% normal, 7% warning, 3% critical
- **Interval default**: 5 detik
- **Idempotency**: Generate unique `Idempotency-Key` per request
- **CLI args**: `--device`, `--key`, `--interval`, `--url`

### Cara Menjalankan

```bash
cd simulasi_py
pip install -r requirements.txt
python simulator.py --device DEVICE_01 --key test_key_device_01
```

---

## 15. Panduan Setup

### Prasyarat

| Software | Versi Minimum |
|---|---|
| PHP | 8.2+ |
| Composer | 2.x |
| Node.js | 18+ |
| MySQL | 8.x |
| Python | 3.8+ (opsional, untuk simulator) |

### Langkah Setup

#### 1. Clone Repository

```bash
git clone https://github.com/AdityaWijayanto19/SATS_Repository.git
cd SATS_Repository
```

#### 2. Install Dependencies

```bash
composer install
npm install
```

#### 3. Setup Environment

```bash
cp .env.example .env
php artisan key:generate
```

#### 4. Konfigurasi Database

Edit file `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=sats
DB_USERNAME=root
DB_PASSWORD=
```

#### 5. Buat Database

```sql
CREATE DATABASE sats;
```

#### 6. Jalankan Migration & Seeder

```bash
php artisan migrate --seed
```

#### 7. Jalankan Development Server

```bash
# Terminal 1 — Vite
npm run dev

# Terminal 2 — Laravel
php artisan serve
```

#### 8. Akses Aplikasi

```
http://localhost:8000
```

#### 9. Jalankan Simulator (Opsional)

```bash
# Terminal 3
cd simulasi_py
python simulator.py --device DEVICE_01 --key test_key_device_01
```

### Perintah Development

```bash
# Jalankan semua sekaligus (server + queue + logs + vite)
composer dev

# Reset database
php artisan migrate:fresh --seed

# Jalankan queue worker
php artisan queue:listen --tries=1

# Jalankan WebSocket server (Reverb)
php artisan reverb:start
```

---

## 16. Panduan Penggunaan

### Login

1. Buka `http://localhost:8000/login`
2. Masukkan email dan password sesuai role yang diinginkan
3. Sistem akan redirect ke dashboard sesuai role

### Monitoring Real-time (Nakes/Dokter)

1. Login sebagai nakes atau dokter
2. Pilih perangkat dari dropdown (data di-polling setiap 10 detik)
3. Pantau 4 kartu statistik: Heart Rate, SpO2, Suhu, Status Kondisi
4. Lihat grafik real-time yang di-polling setiap 5 detik

### Workflow Instruksi (Dokter -> Nakes)

1. **Dokter** mengirim instruksi melalui container komentar di dashboard
2. **Nakes** melihat instruksi masuk di dashboard
3. **Nakes** merespon instruksi melalui dropdown + checklist
4. Status instruksi di-broadcast secara real-time via WebSocket

### Manajemen Perangkat (Superadmin)

1. Login sebagai superadmin
2. Buka menu "Manajemen Alat"
3. Tambah perangkat baru — API key akan di-generate dan ditampilkan sekali
4. Lihat detail perangkat, hapus perangkat yang tidak digunakan

### Laporan & PDF

1. Buka menu "Laporan" sesuai role
2. Filter berdasarkan rentang tanggal
3. Klik "Download PDF" untuk mengunduh laporan dalam format PDF

---

## 17. Status Fitur

### Sudah Terimplementasi Penuh

- Autentikasi (login, logout, forgot/reset password via email)
- Role-based access control (nakes, dokter, superadmin)
- API perangkat IoT (sensor data POST, system status POST/GET, device config, registrasi)
- Autentikasi API key dengan Redis caching, rate limiting, idempotency
- WebSocket broadcasting via Laravel Reverb
- Background job processing dengan dead letter queue
- Workflow instruksi dokter-nakes
- CRUD manajemen perangkat (superadmin)
- Dashboard monitoring dengan real-time polling
- Generate laporan PDF via DomPDF + QuickChart.io
- IoT simulator Python

### Sebagian Implementasi / Data Dummy

- `LaporanController` dan `SuperadminLaporanController` — query database dikomentari, menggunakan data hardcoded
- Endpoint device config mengembalikan nilai hardcoded
- UI manajemen user ada, tetapi route belum didaftarkan

### Belum Diimplementasikan

- Backend input data pasien (form ada, belum ada POST handler)
- Commands controller (start/stop perangkat)
- Penulisan activity log (model/migrasi ada, belum ada yang menulis)
- Integrasi machine learning prediksi
- Integrasi hardware IoT asli (baru simulator)

---

## 18. Troubleshooting

| Masalah | Solusi |
|---|---|
| `composer install` error | Jalankan `composer update` atau pastikan PHP 8.2+ (`php -v`) |
| `npm install` error | Pastikan Node.js 18+ (`node -v`), coba `npm install --force` |
| `No application encryption key` | Jalankan `php artisan key:generate` |
| `SQLSTATE` connection error | Cek MySQL berjalan, cek konfigurasi DB di `.env` |
| Halaman kosong/blank | Cek `storage/logs/laravel.log`, pastikan `npm run dev` berjalan |
| CSS/JS tidak ter-load | Pastikan `npm run dev` berjalan di terminal terpisah |
| `Vite manifest not found` | Jalankan `npm run build` atau `npm run dev` |
| Migration error `table already exists` | Jalankan `php artisan migrate:fresh --seed` |
| Simulator `ModuleNotFoundError` | Jalankan `pip install -r requirements.txt` di folder `simulasi_py/` |
| Simulator `Connection refused` | Pastikan Laravel server berjalan di `http://localhost:8000` |
| Simulator `401 Unauthorized` | Cek API key di `config.py` cocok dengan yang di-seed |
| Dashboard tidak update | Pastikan simulator berjalan, cek polling interval (5 detik) |

---

## Dokumentasi Terkait

| File | Deskripsi |
|---|---|
| [MASTER_BRIEF.md](MASTER_BRIEF.md) | Ringkasan lengkap project, alur sistem, fitur, dan progress |
| [BACKEND.md](BACKEND.md) | Arsitektur backend, API endpoints, service layer, simulator |
| [DATABASE.md](DATABASE.md) | Struktur database, ERD, relasi, alur data |
| [FRONTEND.md](FRONTEND.md) | Struktur frontend, routes, status fitur, workflow komentar |
| [API_ENDPOINTS.md](API_ENDPOINTS.md) | Dokumentasi lengkap API dengan contoh cURL |
| [DEVICE_HARDWARE_TO_SYSTEM.md](DEVICE_HARDWARE_TO_SYSTEM.md) | Dokumentasi integrasi hardware IoT |

---

*Dokumentasi ini dibuat pada 18 Mei 2026.*
