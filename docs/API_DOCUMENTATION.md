# SATS - API Documentation

Dokumentasi lengkap semua API endpoint SATS, termasuk integrasi IoT device dan Machine Learning.

---

## Daftar Isi

1. [Overview](#overview)
2. [Authentication](#authentication)
3. [Device API](#device-api)
4. [Sensor Data API](#sensor-data-api)
5. [System Status API](#system-status-api)
6. [Instruction API](#instruction-api)
7. [Dashboard API](#dashboard-api)
8. [ML Prediction API](#ml-prediction-api)
9. [Machine Learning Integration](#machine-learning-integration)
10. [IoT Device Integration](#iot-device-integration)
11. [Error Handling](#error-handling)
12. [Test Credentials](#test-credentials)

---

## Overview

```
Base URL: http://localhost:8000/api
```

SATS menggunakan 2 jenis autentikasi:
- **API Key** — untuk perangkat IoT (header `X-API-Key`)
- **Session Auth** — untuk web dashboard (cookie session)

---

## Authentication

### API Key Auth (IoT Device)

Semua endpoint device memerlukan header:

```
X-API-Key: <api_key>
```

Proses validasi:
1. Extract API key dari header
2. Hash key → cari di tabel `api_keys`
3. Verifikasi hash, cek expired, cek active
4. Update `last_used` timestamp

### Session Auth (Web Dashboard)

Endpoint instruksi dan monitoring menggunakan session auth (cookie Laravel).

---

## Device API

### Authenticate Device

```
POST /api/device/{device_id}/authenticate
```

**Headers:** `X-API-Key: <key>`

**Response (200):**
```json
{
  "success": true,
  "message": "Device authenticated successfully",
  "data": {
    "device_id": "DEVICE_01",
    "authenticated_at": "2026-05-04T12:00:00Z"
  }
}
```

### Get Device Configuration

```
GET /api/device/{device_id}/config
```

**Headers:** `X-API-Key: <key>`

**Response (200):**
```json
{
  "success": true,
  "data": {
    "device_id": "DEVICE_01",
    "status": "online",
    "last_seen": "2026-05-04T12:00:00Z"
  }
}
```

### List All Devices (Session Auth)

```
GET /api/devices
```

Mengembalikan daftar semua perangkat beserta data card (latest), data grafik (history 10 menit), dan info active session.

**Query Parameters:**
| Param | Type | Default | Description |
|-------|------|---------|-------------|
| `minutes` | integer | 10 | Rentang waktu grafik (menit) |

**Response (200):**
```json
{
  "success": true,
  "data": [
    {
      "device_id": "DEVICE_01",
      "device_name": "Ambulans 01",
      "status": "online",
      "latest": { "heart_rate": 85, "spo2": 98, "temperature": 36.5, "status": "normal" },
      "history": [ ... ],
      "ml_prediction": "...",
      "ml_condition": "WARNING",
      "ml_probabilities": { "membaik": 11, "stabil": 26, "memburuk": 63 },
      "active_session": {
        "id": 1,
        "medical_record_number": "RM-DEVICE_01-20260524-001",
        "status": "active",
        "started_at": "2026-05-24T08:00:00Z",
        "patient": null
      }
    }
  ]
}
```

> `active_session` berisi data monitoring session yang sedang aktif untuk device tersebut (null jika device offline).

---

### Patient Data (Session Auth)

#### Input Data Pasien

```
POST /nakes/input-data-pasien
```

Menyimpan data pasien dan meng-link-nya ke active monitoring session.

**Request Body:**
```json
{
  "session_id": 1,
  "nama": "Budi Santoso",
  "nik": "3201234567890001",
  "tanggal_lahir": "1990-05-15",
  "umur": 36,
  "jenis_kelamin": "Laki-laki",
  "penyakit_alergi": "Asma",
  "catatan": "Pasien dalam kondisi sadar"
}
```

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `session_id` | integer | Yes | ID monitoring session (active) |
| `nama` | string | No | Nama pasien |
| `nik` | string | No | NIK pasien (16 digit) |
| `tanggal_lahir` | date | No | Format: YYYY-MM-DD |
| `umur` | integer | No | Umur pasien |
| `jenis_kelamin` | string | No | Laki-laki / Perempuan |
| `penyakit_alergi` | string | No | Riwayat penyakit/alergi |
| `catatan` | string | No | Catatan tambahan |

**Response (200):**
```json
{
  "success": true,
  "message": "Data pasien berhasil disimpan",
  "data": {
    "patient_id": 1,
    "session_id": 1,
    "medical_record_number": "RM-DEVICE_01-20260524-001"
  }
}
```

---

### Laporan API (Session Auth)

#### Get Session Data (AJAX)

```
GET /nakes/laporan/session-data?session_id=1&vital_signs[]=heart_rate&vital_signs[]=spo2&vital_signs[]=temperature
```

Mengembalikan data laporan untuk satu session dalam format JSON (untuk AJAX load tanpa refresh halaman).

**Query Parameters:**
| Param | Type | Required | Description |
|-------|------|----------|-------------|
| `session_id` | integer | Yes | ID monitoring session (completed) |
| `vital_signs[]` | array | No | Vital signs yang ditampilkan (default: semua) |

**Response (200):**
```json
{
  "success": true,
  "data": {
    "sessionInfo": { "id": 1, "medical_record_number": "RM-DEVICE_01-20260524-001", "status": "completed" },
    "chartData": { "labels": [...], "datasets": { "heart_rate": [...], "spo2": [...], "temperature": [...] } },
    "latestReading": { "heart_rate": 85, "spo2": 98, "temperature": 36.5 },
    "stats": { "heart_rate": { "avg": 82, "min": 75, "max": 95 }, ... },
    "patientHtml": "<div>...</div>",
    "contentHtml": "<div>...</div>",
    "sidebarHtml": "<div>...</div>"
  }
}
```

> `patientHtml`, `contentHtml`, `sidebarHtml` adalah rendered HTML partials yang langsung di-inject ke DOM via JavaScript.

---

## Sensor Data API

### Store Sensor Data ⭐

Endpoint utama untuk mengirim data sensor real-time dari perangkat.

```
POST /api/device/{device_id}/sensor-data
```

**Headers:**
```
X-API-Key: <key>
Content-Type: application/json
```

**Request Body:**
```json
{
  "heart_rate": 85,
  "spo2": 98,
  "temperature": 36.5,
  "status": "normal",
  "prediction": "healthy"
}
```

| Field | Type | Required | Range | Description |
|-------|------|----------|-------|-------------|
| `heart_rate` | integer | No | 0-250 | BPM |
| `spo2` | integer | No | 0-100 | Saturasi oksigen (%) |
| `temperature` | float | No | 20-45 | Suhu (Celsius) |
| `status` | string | No | normal/warning/critical | Klasifikasi kondisi |
| `prediction` | string | No | max 50 chars | Hasil prediksi |

**Response (201):**
```json
{
  "success": true,
  "message": "Sensor data stored successfully",
  "data": {
    "id": 1,
    "device_id": "DEVICE_01",
    "created_at": "2026-05-04T12:00:00Z"
  }
}
```

**Contoh cURL:**
```bash
curl -X POST http://localhost:8000/api/device/DEVICE_01/sensor-data \
  -H "X-API-Key: test_key_device_01" \
  -H "Content-Type: application/json" \
  -d '{"heart_rate": 85, "spo2": 98, "temperature": 36.5, "status": "normal"}'
```

### Store Sensor Data (Batch)

```
POST /api/device/{device_id}/sensor-data/batch
```

Mengirim beberapa data sensor sekaligus.

### Get Latest Sensor Data

```
GET /api/device/{device_id}/sensor-data/latest
```

**Headers:** `X-API-Key: <key>` atau Session Auth

**Response (200):**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "device_id": "DEVICE_01",
    "heart_rate": 85,
    "spo2": 98,
    "temperature": 36.5,
    "status": "normal",
    "prediction": "healthy",
    "created_at": "2026-05-04T12:00:00Z"
  }
}
```

### Get Sensor Data History

```
GET /api/device/{device_id}/sensor-data/history?minutes=10
```

**Query Parameters:**
| Param | Type | Default | Description |
|-------|------|---------|-------------|
| `minutes` | integer | 10 | Rentang waktu (menit) |

**Response (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "device_id": "DEVICE_01",
      "heart_rate": 85,
      "spo2": 98,
      "temperature": 36.5,
      "status": "normal",
      "created_at": "2026-05-14T12:00:00Z"
    }
  ]
}
```

---

## System Status API

### Store System Status

```
POST /api/device/{device_id}/system-status
```

**Headers:** `X-API-Key: <key>`

**Request Body:**
```json
{
  "monitoring_status": "active",
  "battery_level": 85,
  "signal_strength": -45
}
```

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `monitoring_status` | string | No | `active` atau `inactive` |
| `battery_level` | integer | No | 0-100 (%) |
| `signal_strength` | integer | No | dBm (biasanya -30 sampai -100) |

**Response (200):**
```json
{
  "success": true,
  "message": "System status stored successfully",
  "data": {
    "device_id": "DEVICE_01",
    "monitoring_status": "active",
    "battery_level": 85,
    "signal_strength": -45
  }
}
```

### Get System Status

```
GET /api/device/{device_id}/system-status
```

**Response (200):**
```json
{
  "success": true,
  "data": {
    "device_id": "DEVICE_01",
    "monitoring_status": "active",
    "battery_level": 85,
    "signal_strength": -45,
    "updated_at": "2026-05-04T12:00:00Z"
  }
}
```

### Check Device Status (Public)

```
GET /api/device/{device_id}/status
```

Endpoint publik (tanpa auth) untuk mengecek status device.

---

## Instruction API

Endpoint untuk sistem instruksi dokter-nakes. Menggunakan **Session Auth**.

### Get Instructions

```
GET /api/instruction?device_id=DEVICE_01
```

**Response (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "instruksi_dokter": "Berikan oksigen 2L/menit",
      "is_completed": false,
      "user_name": "dr. Andi",
      "nakes_name": "Suster Rina",
      "waktu": "14:30",
      "completed_at": null,
      "respon_nakes": null,
      "laporan_nakes": null
    }
  ]
}
```

### Store Instruction (Dokter)

```
POST /api/instruction
```

**Request Body:**
```json
{
  "device_id": "DEVICE_01",
  "instruksi_dokter": "Berikan oksigen 2L/menit"
}
```

**Response (201):**
```json
{
  "success": true,
  "data": { "id": 1, "instruksi_dokter": "Berikan oksigen 2L/menit" },
  "message": "Instruksi berhasil dibuat"
}
```

### Store Report (Nakes)

```
POST /api/instruction/report
```

**Request Body:**
```json
{
  "device_id": "DEVICE_01",
  "laporan_nakes": "Pasien mengalami sesak napas ringan"
}
```

### Update Instruction (Dokter)

```
PATCH /api/instruction/{id}
```

**Request Body:**
```json
{
  "instruksi_dokter": "Update: Berikan oksigen 3L/menit"
}
```

### Complete Instruction (Nakes)

```
PATCH /api/instruction/{id}/complete
```

**Request Body:**
```json
{
  "respon_nakes": "Sudah dilakukan"
}
```

**Response (200):**
```json
{
  "success": true,
  "data": { "id": 1, "is_completed": true, "respon_nakes": "Sudah dilakukan" },
  "message": "Instruksi berhasil diselesaikan"
}
```

---

## Dashboard API

### Prediction per Device

```
GET /api/device/{device_id}/prediction
```

Mengembalikan hasil prediksi ML untuk device tertentu.

### Toggle Device Status (Nakes)

```
PATCH /nakes/device-status
```

Nakes bisa mengaktifkan/mematikan perangkat dari dashboard.

---

## ML Prediction API

### Get Prediction

```
GET /api/device/{device_id}/prediction
```

**Response (200):**
```json
{
  "success": true,
  "data": {
    "ml_prediction": "Pasien akan MEMBURUK (63%) dalam 5 menit ke depan",
    "ml_condition": "WARNING",
    "ml_risk_level": "High Risk",
    "ml_probabilities": {
      "membaik": 11,
      "stabil": 26,
      "memburuk": 63
    },
    "ml_predicted_at": "2026-05-18T14:30:00Z"
  }
}
```

---

## Machine Learning Integration

### Overview

SATS mengintegrasikan model ML prediksi kondisi pasien via **Hugging Face Spaces** (Gradio async 2-step API).

```
┌─────────────────┐         ┌─────────────────┐
│   Laravel App   │         │  Hugging Face   │
│                 │         │                 │
│  1. POST data ──────────► │  Terima data    │
│                 │         │  Return event_id│
│     ◄─────────────────── │                 │
│                 │         │                 │
│  2. GET ────────────────► │  Return hasil   │
│     event_id    │         │  prediksi       │
│     ◄─────────────────── │                 │
└─────────────────┘         └─────────────────┘
```

**Kenapa 2 langkah?** Gradio 6.x memproses prediksi secara async. Langkah 1 mengirim data dan dapat `event_id`. Langkah 2 mengambil hasil menggunakan `event_id`.

### Alur di SATS

1. Setiap **5 data sensor baru**, `SensorService` trigger `PatientMonitoringService`
2. Kirim **15 angka** (5 menit × 3 vital signs: HR, Temp, SpO2) ke API eksternal
3. Hasil disimpan di tabel `devices`: `ml_prediction`, `ml_condition`, `ml_risk_level`, `ml_probabilities`, `ml_predicted_at`
4. Broadcast ulang ke dashboard via WebSocket setelah prediksi selesai

### API Hugging Face

```
Base URL: https://dalvero-sats-monitoring.hf.space
```

#### Step 1: Kirim Data → Dapat `event_id`

```
POST /gradio_api/call/predict
Content-Type: application/json
```

**Request Body:**

```json
{
    "data": [80, 36.7, 97, 85, 36.8, 96, 90, 36.9, 95, 95, 37.0, 94, 100, 37.2, 93]
}
```

Urutan data per menit: `HR, Temp, SpO2`
Total: **15 angka** (5 menit × 3 vital signs)

**Response:**
```json
{
    "event_id": "a1b2c3d4e5f6..."
}
```

#### Step 2: Ambil Hasil Pakai `event_id`

```
GET /gradio_api/call/predict/{event_id}
```

**Response (Server-Sent Events format):**

```
event: complete
data: ["Pasien akan MEMBURUK (63%) dalam 5 menit ke depan", "Membaik   :  11% ##\nStabil    :  26% #####\nMemburuk  :  63% #############", "WARNING", "High Risk", 11, 26, 63]
```

**Parse `data` array:**

| Index | Isi | Tipe | Contoh |
|-------|-----|------|--------|
| `[0]` | Prediksi | string | `"Pasien akan MEMBURUK (63%) dalam 5 menit ke depan"` |
| `[1]` | Probabilitas detail (bar chart) | string | `"Membaik: 11% ..."` |
| `[2]` | Kondisi | string | `"NORMAL"` / `"WARNING"` / `"CRITICAL"` |
| `[3]` | Risk Level | string | `"Low Risk"` / `"Medium Risk"` / `"High Risk"` |
| `[4]` | Membaik (%) | number | `11` |
| `[5]` | Stabil (%) | number | `26` |
| `[6]` | Memburuk (%) | number | `63` |

### Data Mapping

Untuk mengirim data dari sensor/database ke API, susun array dengan urutan:

```
Index 0-2:   Menit 1 → [HR, Temp, SpO2]
Index 3-5:   Menit 2 → [HR, Temp, SpO2]
Index 6-8:   Menit 3 → [HR, Temp, SpO2]
Index 9-11:  Menit 4 → [HR, Temp, SpO2]
Index 12-14: Menit 5 → [HR, Temp, SpO2]
```

### Contoh Data Request

**Normal (sehat):**
```json
{"data": [80, 36.7, 97, 78, 36.6, 97, 82, 36.7, 98, 79, 36.8, 97, 81, 36.7, 97]}
```

**Warning (menuju kritis):**
```json
{"data": [85, 36.8, 96, 90, 36.9, 95, 95, 37.0, 94, 100, 37.1, 93, 105, 37.2, 92]}
```

**Critical (kritis):**
```json
{"data": [100, 37.5, 91, 110, 37.8, 89, 120, 38.2, 87, 125, 38.5, 85, 130, 39.0, 82]}
```

### Implementasi Laravel

#### Service Class (`app/Services/PatientMonitoringService.php`)

```php
<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class PatientMonitoringService
{
    protected string $apiUrl = "https://dalvero-sats-monitoring.hf.space";

    public function predict(array $vitalSigns): array
    {
        // Step 1: Kirim data, dapat event_id
        $response1 = Http::post("{$this->apiUrl}/gradio_api/call/predict", [
            'data' => $vitalSigns,
        ]);

        $eventId = $response1->json('event_id');

        // Step 2: Ambil hasil pakai event_id
        $response2 = Http::get("{$this->apiUrl}/gradio_api/call/predict/{$eventId}");

        // Parse SSE response
        $body = $response2->body();
        preg_match('/data: (.+)/', $body, $matches);
        $data = json_decode($matches[1], true);

        return [
            'prediction'    => $data[0],
            'probabilities' => $data[1],
            'condition'     => $data[2],
            'risk_level'    => $data[3],
            'membaik'       => $data[4],
            'stabil'        => $data[5],
            'memburuk'      => $data[6],
        ];
    }

    public function formatVitalSigns(array $readings): array
    {
        // Convert [{hr, temp, spo2}, ...] menjadi flat array
        $data = [];
        foreach ($readings as $r) {
            $data[] = $r['hr'];
            $data[] = $r['temp'];
            $data[] = $r['spo2'];
        }
        return $data;
    }
}
```

### Contoh Integrasi Frontend (JavaScript)

```javascript
const API_URL = "https://dalvero-sats-monitoring.hf.space";

async function predictPatient(vitalSigns) {
    // Step 1: Kirim data, dapat event_id
    const response1 = await fetch(`${API_URL}/gradio_api/call/predict`, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ data: vitalSigns })
    });
    const { event_id } = await response1.json();

    // Step 2: Ambil hasil pakai event_id
    const response2 = await fetch(`${API_URL}/gradio_api/call/predict/${event_id}`);
    const text = await response2.text();

    // Parse SSE response
    const match = text.match(/data: (.+)/);
    const [prediction, probabilities, condition, riskLevel, membaik, stabil, memburuk] = JSON.parse(match[1]);

    return { prediction, probabilities, condition, riskLevel, membaik, stabil, memburuk };
}
```

### Output ML

| Field | Contoh | Keterangan |
|-------|--------|------------|
| Prediksi | "Pasien akan MEMBURUK (63%) dalam 5 menit ke depan" | Teks prediksi |
| Kondisi | `NORMAL` / `WARNING` / `CRITICAL` | Klasifikasi |
| Risk Level | `Low Risk` / `Medium Risk` / `High Risk` | Level risiko |
| Probabilitas | Membaik: 11%, Stabil: 26%, Memburuk: 63% | Persentase |

---

## IoT Device Integration

### Flow Perangkat ke Sistem

```
[DEVICE SIDE]
1. Power ON
   └─ Read stored config (sampling_interval = 5s)

2. Authenticate
   └─ POST /api/device/{device_id}/authenticate
      └─ Send X-API-Key header
      └─ Server validate key (check hash, active, not expired)

3. [LOOP] Every 5 seconds:
   a) Read Sensors → Heart rate, SpO2, Temperature
   b) POST /api/device/{device_id}/sensor-data
   c) POST /api/device/{device_id}/system-status
   d) Check Battery → if < 20%, sleep longer

[SERVER SIDE]
→ Data stored in sensor_datas table
→ Device marked as "online"
→ WebSocket broadcast ke dashboard

[DASHBOARD SIDE]
→ Realtime update card + grafik
→ Zero delay
```

### Rekomendasi Interval

| Data | Interval | Keterangan |
|------|----------|------------|
| Sensor Data | 5-30 detik | Data vital sign |
| System Status | 1-5 menit | Battery, signal |

### Contoh cURL

```bash
# Authenticate
curl -X POST http://localhost:8000/api/device/DEVICE_01/authenticate \
  -H "X-API-Key: test_key_device_01"

# Send Sensor Data
curl -X POST http://localhost:8000/api/device/DEVICE_01/sensor-data \
  -H "X-API-Key: test_key_device_01" \
  -H "Content-Type: application/json" \
  -d '{"heart_rate": 85, "spo2": 98, "temperature": 36.5}'

# Get Latest
curl -X GET http://localhost:8000/api/device/DEVICE_01/sensor-data/latest \
  -H "X-API-Key: test_key_device_01"

# Send System Status
curl -X POST http://localhost:8000/api/device/DEVICE_01/system-status \
  -H "X-API-Key: test_key_device_01" \
  -H "Content-Type: application/json" \
  -d '{"monitoring_status": "active", "battery_level": 85, "signal_strength": -45}'
```

---

## Error Handling

### HTTP Status Codes

| Code | Meaning |
|------|---------|
| 200 | OK - Request berhasil |
| 201 | Created - Data berhasil disimpan |
| 401 | Unauthorized - API Key invalid |
| 404 | Not Found - Resource tidak ditemukan |
| 422 | Unprocessable Entity - Validation error |
| 500 | Server Error |

### Error Response Examples

**401 Unauthorized:**
```json
{
  "success": false,
  "message": "Invalid API key for this device or key expired"
}
```

**422 Validation Error:**
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "heart_rate": ["The heart rate must be between 0 and 250."],
    "spo2": ["The spo2 must be between 0 and 100."]
  }
}
```

**404 Not Found:**
```json
{
  "success": false,
  "message": "Device not found"
}
```

---

## Test Credentials

| Device ID | API Key |
|-----------|---------|
| `DEVICE_01` | `test_key_device_01` |
| `DEVICE_02` | `test_key_device_02` |

| Role | Email | Password |
|------|-------|----------|
| Super Admin | `admin@sats.id` | `password` |
| Dokter | `andi@sats.id` | `password` |
| Nakes | `rina@sats.id` | `password` |

---

## File Terkait

| File | Deskripsi |
|------|-----------|
| [MASTER_BRIEF.md](MASTER_BRIEF.md) | Ringkasan lengkap project |
| [BACKEND.md](BACKEND.md) | Arsitektur backend, service layer |
| [DATABASE.md](DATABASE.md) | Struktur database, ERD |
| [FRONTEND.md](FRONTEND.md) | Struktur frontend, routes |
| [LAPORAN_SYSTEM.md](LAPORAN_SYSTEM.md) | Desain sistem laporan & monitoring session |
| [DEMO.md](DEMO.md) | Panduan instalasi & demo |

---

*Last updated: 24 Mei 2026*
