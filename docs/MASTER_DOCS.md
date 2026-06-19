# SATS — Smart Ambulance Telemedicine System

Dokumentasi lengkap sistem monitoring vital sign pasien berbasis IoT untuk ambulans.

---

## Daftar Isi

1. [Overview](#1-overview)
2. [Tech Stack](#2-tech-stack)
3. [Sistem Role](#3-sistem-role)
4. [Arsitektur Sistem](#4-arsitektur-sistem)
5. [Database](#5-database)
6. [API Reference](#6-api-reference)
7. [Machine Learning](#7-machine-learning)
8. [Service Layer](#8-service-layer)
9. [Real-Time WebSocket](#9-real-time-websocket)
10. [Hardware Integration](#10-hardware-integration)
11. [File Structure](#11-file-structure)
12. [Fitur per Role](#12-fitur-per-role)

---

## 1. Overview

SATS adalah sistem telemedicine berbasis IoT yang menghubungkan data vital sign pasien di ambulans dengan dokter di rumah sakit secara real-time. Sistem ini memungkinkan monitoring kondisi pasien selama perjalanan ke rumah hospital.

**Alur Utama:**
```
Sensor IoT (ESP32) → HTTP POST → Laravel API → WebSocket → Dashboard Browser
                                    ↓
                              Database + ML Prediction
```

**Fitur Utama:**
- Monitoring vital sign real-time (Heart Rate, SpO2, Suhu)
- Prediksi kondisi pasien berbasis Machine Learning
- Komunikasi instruksi dokter ↔ nakes secara real-time
- Manajemen sesi monitoring otomatis
- Laporan dan rekam medis dengan export PDF
- Manajemen perangkat dan pengguna

---

## 2. Tech Stack

| Komponen | Teknologi |
|----------|-----------|
| Backend | Laravel 12, PHP 8.3 |
| Frontend | Tailwind CSS v4, Alpine.js v3, Chart.js v4 |
| Database | MySQL 8 |
| Cache | Redis |
| WebSocket | Laravel Reverb |
| Queue | Laravel Queue (Redis driver) |
| PDF | DomPDF + QuickChart.io |
| ML | Hugging Face Spaces (Gradio) |
| Build | Vite v6 |
| Hardware | ESP32, MAX30102, DS18B20 |

---

## 3. Sistem Role

### Nakes (Perawat)
- Dashboard monitoring perangkat
- Setup dan konfigurasi perangkat
- Input data pasien
- Melihat laporan
- Menerima instruksi dari dokter

### Dokter
- Dashboard monitoring multi-perangkat
- Memilih perangkat untuk dipantau
- Mengirim instruksi ke nakes
- Melihat rekam medis
- Download laporan PDF

### Superadmin
- Dashboard ringkasan sistem
- Manajemen perangkat (CRUD)
- Manajemen pengguna (CRUD)
- Manajemen rekam medis (search, filter, delete)
- Laporan operasional
- Inbox support

### Demo Accounts

| Role | Email | Password |
|------|-------|----------|
| Superadmin | admin@sats.id | password |
| Dokter | dokter@sats.id | password |
| Nakes | nakes@sats.id | password |

---

## 4. Arsitektur Sistem

### Monitoring Session Lifecycle

```
Device ON → Auto-create Session → Kirim Data → Device OFF → Finalize Session
    ↓              ↓                    ↓            ↓              ↓
 Status:online  Session:active    sensor_datas   Status:offline  Copy to
                                  terisi data                    sensor_readings
                                                                 Delete sensor_datas
```

**Session Status:**
- `active` — sedang berjalan
- `completed` — sudah selesai (data di-copy ke permanen)
- `cancelled` — dibatalkan

**Medical Record Number Format:** `RM-{DEVICE_ID}-{YYYYMMDD}-{SEQ}`

### Auto-Offline Mechanism

Scheduler `devices:mark-stale-offline` berjalan setiap 5 detik:
- Timeout: 5 detik tanpa data → device dianggap offline
- Otomatis finalisasi session
- Broadcast status change ke dashboard

### Auto-Reactivate

Saat device mengirim data dan status=offline:
- Controller otomatis set status=online
- Buat session baru jika belum ada
- Broadcast status change

---

## 5. Database

### Tabel Utama (16 tabel)

| Tabel | Keterangan |
|-------|------------|
| `users` | Pengguna (superadmin, dokter, nakes) |
| `devices` | Perangkat IoT terdaftar |
| `sensor_datas` | Data vital sign mentah (temporary) |
| `sensor_readings` | Data vital sign permanen (per session) |
| `monitoring_sessions` | Sesi monitoring |
| `patients` | Data pasien |
| `medical_records` | Catatan medis (legacy) |
| `instructions` | Instruksi dokter → nakes |
| `api_keys` | API key perangkat |
| `system_statuses` | Status sistem perangkat |
| `activity_log` | Log aktivitas sistem |
| `nakes_device_configs` | Konfigurasi perangkat nakes |
| `device_monitorings` | Relasi dokter-perangkat |
| `failed_sensor_datas` | Dead letter queue |
| `reports` | Laporan support |

### Relasi Utama

```
Devices (1) ──→ (N) SensorData
Devices (1) ──→ (N) MonitoringSession
Devices (1) ──→ (N) Patient
MonitoringSession (1) ──→ (N) SensorReading
MonitoringSession (N) ──→ (1) Patient
MonitoringSession (N) ──→ (1) User (creator/nakes)
MonitoringSession (N) ──→ (1) User (dokter)
User (1) ──→ (N) Patient (via nakes_id)
User (N) ──→ (N) Device (via device_monitorings)
```

---

## 6. API Reference

### Authentication

**API Key (IoT Device):**
```
Header: X-API-Key: dev_xxxxxxxx
```

**Session Auth (Web):**
```
Cookie: laravel_session
```

### Device Endpoints

| Method | Endpoint | Auth | Keterangan |
|--------|----------|------|------------|
| POST | `/api/device/register` | - | Registrasi perangkat baru |
| POST | `/api/device/{id}/authenticate` | API Key | Autentikasi perangkat |
| GET | `/api/device/{id}/config` | API Key | Ambil konfigurasi |
| PATCH | `/api/device/{id}/status` | API Key | Update status (online/offline) |

### Sensor Data Endpoints

| Method | Endpoint | Auth | Keterangan |
|--------|----------|------|------------|
| POST | `/api/device/{id}/sensor-data` | API Key | Kirim data sensor |
| POST | `/api/device/{id}/sensor-data/batch` | API Key | Kirim data batch |
| POST | `/api/device/{id}/system-status` | API Key | Kirim status sistem |
| GET | `/api/device/{id}/sensor-data/latest` | Session | Data terbaru |
| GET | `/api/device/{id}/sensor-data/history` | Session | Data historis |

### Instruction Endpoints

| Method | Endpoint | Auth | Keterangan |
|--------|----------|------|------------|
| GET | `/api/instruction` | Session | Ambil instruksi |
| POST | `/api/instruction` | Session | Kirim instruksi (dokter) |
| POST | `/api/instruction/report` | Session | Kirim laporan (nakes) |
| PATCH | `/api/instruction/{id}/complete` | Session | Konfirmasi instruksi |

### Sensor Data Payload

```json
{
    "heart_rate": 82,
    "spo2": 97,
    "temperature": 36.8,
    "status": "normal",
    "kategori_usia": "Dewasa"
}
```

**Validasi:**
| Field | Tipe | Range |
|-------|------|-------|
| `heart_rate` | integer | 0-250 |
| `spo2` | integer | 0-100 |
| `temperature` | numeric | 0-45 |
| `status` | string | `normal`, `warning`, `critical`, `no_finger` |
| `kategori_usia` | string | `Balita`, `Anak-anak`, `Dewasa`, `Lansia` |

### Headers yang Dibutuhkan

```
Content-Type: application/json
X-API-Key: dev_xxxxxxxx
Idempotency-Key: {32 hex chars}
```

### Response Codes

| Code | Arti |
|------|------|
| 202 | Data diterima, di-queue |
| 200 | Berhasil |
| 401 | API key tidak valid |
| 404 | Device tidak ditemukan |
| 422 | Validasi gagal |
| 429 | Rate limit exceeded |
| 409 | Duplikat (idempotent) |

---

## 7. Machine Learning

### Model

- **Algoritma:** Random Forest Classifier
- **Akurasi:** 63.4%
- **Fitur:** 19 (15 vital signs + 4 one-hot age category)
- **Output:** Prediksi 5 menit ke depan (Membaik/Stabil/Memburuk)

### Input Format (16 elemen)

```
[kategori_usia, HR1, Temp1, SpO1, HR2, Temp2, SpO2, ..., HR5, Temp5, SpO5]
```

### Hugging Face API

**Endpoint:** `https://dalvero-sats-monitoring.hf.space`

**Step 1: Kirim data**
```
POST /gradio_api/call/predict_manual
Body: { "data": ["Dewasa", 80, 36.7, 97, ...] }
Response: { "event_id": "xxx" }
```

**Step 2: Ambil hasil**
```
GET /gradio_api/call/predict_manual/{event_id}
Response (SSE): data: ["Prediksi...", "Detail...", "NORMAL", "Low Risk", 19, 30, 51]
```

### Threshold per Kategori Usia

| Kategori | HR Normal | SpO2 Normal | Temp Normal |
|----------|-----------|-------------|-------------|
| Balita | 80-130 | 95-100 | 36.5-37.5 |
| Anak-anak | 70-110 | 95-100 | 36.5-37.2 |
| Dewasa | 60-100 | 95-100 | 36.1-38.0 |
| Lansia | 60-100 | 93-100 | 36.1-37.0 |

### Trigger

ML dipanggil setiap 5 data valid baru (HR > 0 dan SpO2 > 0). Cache 2 menit.

---

## 8. Service Layer

| Service | Fungsi |
|---------|--------|
| `AuthService` | Login, logout, reset password |
| `DashboardService` | Data dashboard per role |
| `DeviceService` | Status sistem, cache Redis |
| `DeviceManagementService` | CRUD perangkat + API key |
| `MonitoringSessionService` | Lifecycle session |
| `SensorService` | Simpan data, trigger ML |
| `PatientMonitoringService` | Integrasi Hugging Face ML |
| `ReportService` | Data laporan, chart, stats |
| `InstructionService` | CRUD instruksi |
| `SupportService` | Laporan support |
| `UserService` | CRUD user |
| `ProfileService` | Edit profil |

---

## 9. Real-Time WebSocket

### Channel

```javascript
Echo.private(`device.${deviceId}`)
    .listen('.sensor.data.received', (e) => { ... })
    .listen('.device.status.changed', (e) => { ... });
```

### Events

| Event | Channel | Trigger |
|-------|---------|---------|
| `SensorDataReceived` | `device.{id}` | Data sensor masuk |
| `DeviceStatusChanged` | `device.{id}` | Status berubah |
| `DeviceStatusChangedGlobal` | `superadmin.dashboard` | Status global |
| `ActivityLogCreated` | `superadmin.dashboard` | Log baru |
| `InstructionSent` | `device.{id}` | Instruksi baru |
| `InstructionStatusUpdated` | `device.{id}` | Instruksi dikonfirmasi |
| `SupportReportCreated` | `superadmin.dashboard` | Laporan support baru |

### Real-Time Flow

```
ESP32 POST → Controller → broadcast(SensorDataReceived) → Browser
                           ↓
                    dispatch(ProcessSensorData) → Queue → DB
```

Data sampai ke browser **sebelum** ditulis ke database (zero-latency broadcast).

---

## 10. Hardware Integration

### ESP32 Data Mapping

| Hardware | API | Transformasi |
|----------|-----|-------------|
| `smoothHR` (float) | `heart_rate` (int) | `(int)(smoothHR + 0.5)` |
| `smoothSpO2` (float) | `spo2` (int) | `(int)(smoothSpO2 + 0.5)` |
| `smoothTempVal` (float) | `temperature` (float) | Langsung |
| `NORMAL` | `normal` | Lowercase |
| `WARNING` | `warning` | Lowercase |
| `CRITICAL` | `critical` | Lowercase |
| `WAITING` | `no_finger` | Mapping khusus |
| `ANAK_ANAK` | `Anak-anak` | **Dengan dash!** |

### Handling Nilai 0

Sensor mengirim `heart_rate: 0` dan `spo2: 0` saat jari belum terdeteksi. Website menangani:
- **Chart:** 0 → null (tidak ditampilkan)
- **ML:** Skip data dengan HR/SpO2 = 0
- **Database:** Tetap menyimpan 0

### Urutan Inisialisasi Sensor

1. **Temperature** → ~2-3 detik
2. **Heart Rate** → ~5-10 detik (3 peak)
3. **SpO2** → ~10-15 detik (buffer 100 sample)

---

## 11. File Structure

### Controllers (15)

```
app/Http/Controllers/
├── AuthController.php
├── DashboardController.php
├── ManajemenAlatController.php
├── PatientController.php
├── ProfileController.php
├── RekamMedisController.php
├── SuperadminInboxController.php
├── SuperadminLaporanController.php
├── SuperadminRekamMedisController.php
├── UserController.php
└── Api/
    ├── DeviceDataController.php
    ├── InstructionController.php
    └── SensorDataController.php
```

### Models (15)

```
app/Models/
├── ActivityLog.php
├── ApiKey.php
├── DeviceMonitoring.php
├── Devices.php
├── FailedSensorData.php
├── Instruction.php
├── MedicalRecord.php
├── MonitoringSession.php
├── NakesDeviceConfig.php
├── Patient.php
├── SensorData.php
├── SensorReading.php
├── SupportReport.php
├── SystemStatus.php
└── User.php
```

### Views

```
resources/views/
├── layouts/ (app, auth, landing)
├── components/ (sidebar, navbar, chat-widget, profile-dropdown)
└── pages/
    ├── landing/ (7 sections)
    ├── nakes/ (dashboard, inputdata, laporan, instruksi, monitoring)
    ├── dokter/ (dashboard, inputdata, laporan, rekam-medis, instruksi, monitoring, monitor-3d)
    ├── superadmin/ (dashboard, manajemen-alat, manajemen-user, rekam-medis, laporan, inbox)
    ├── auth/ (login, forgot-password, reset-password)
    └── profile/ (edit)
```

---

## 12. Fitur per Role

### Nakes
- [x] Dashboard monitoring perangkat
- [x] Setup perangkat (API key)
- [x] Toggle perangkat (online/offline)
- [x] Input data pasien
- [x] Laporan dengan filter session
- [x] Download PDF laporan
- [x] Chat widget (instruksi dokter)
- [x] Monitoring real-time

### Dokter
- [x] Dashboard multi-perangkat (card view)
- [x] Pilih perangkat untuk dipantau
- [x] Monitoring real-time (chart + stats)
- [x] Prediksi ML
- [x] Kirim instruksi ke nakes
- [x] Rekam medis (list, detail, PDF)
- [x] Laporan dengan filter
- [x] Monitor 3D view

### Superadmin
- [x] Dashboard ringkasan sistem
- [x] Manajemen alat (CRUD)
- [x] Manajemen user (CRUD)
- [x] Manajemen rekam medis (search, filter, delete, PDF)
- [x] Laporan operasional
- [x] Inbox support
- [x] Log aktivitas real-time

### IoT Device
- [x] Registrasi perangkat
- [x] Kirim data sensor (single + batch)
- [x] Kirim status sistem
- [x] Update status (online/offline)
- [x] API key authentication
- [x] Rate limiting
- [x] Idempotent requests
