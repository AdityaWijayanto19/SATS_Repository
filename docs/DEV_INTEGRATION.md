# DEV_INTEGRATION.md — Integrasi Perangkat Hardware dengan Website SATS

Panduan lengkap untuk menghubungkan perangkat ESP32 (hardware asli) dengan website SATS.

---

## Daftar Isi

1. [Overview Arsitektur](#1-overview-arsitektur)
2. [Registrasi Perangkat](#2-registrasi-perangkat)
3. [API Endpoints yang Dibutuhkan](#3-api-endpoints-yang-dibutuhkan)
4. [Mapping Data Hardware → API](#4-mapping-data-hardware--api)
5. [Alur Data Real-Time](#5-alur-data-real-time)
6. [Handling Kategori Usia & Reset](#6-handling-kategori-usia--reset)
7. [Monitoring Session Lifecycle](#7-monitoring-session-lifecycle)
8. [Implementation Guide untuk ESP32](#8-implementation-guide-untuk-esp32)
9. [Testing & Debugging](#9-testing--debugging)
10. [Troubleshooting](#10-troubleshooting)

---

## 1. Overview Arsitektur

```
┌─────────────────────┐         HTTP POST           ┌─────────────────────┐
│   ESP32 Hardware    │ ──────────────────────────► │   SATS Website API  │
│   (third_test.cpp)  │                              │   (Laravel)         │
│                     │   POST /sensor-data          │                     │
│   - MAX30102 (HR,   │   PATCH /status              │   - Validate API Key│
│     SpO2)           │   POST /system-status        │   - Store to DB     │
│   - DS18B20 (Temp)  │                              │   - WebSocket Push  │
│   - NEWS2 Classify  │ ◄────────────────────────── │   - ML Prediction   │
│   - WiFi (HTTP)     │   202 Accepted               │                     │
└─────────────────────┘                              └─────────────────────┘
                                                              │
                                                              │ WebSocket (Laravel Reverb)
                                                              ▼
                                                     ┌─────────────────────┐
                                                     │   Dashboard Web     │
                                                     │   (Browser)         │
                                                     │                     │
                                                     │   - Real-time data  │
                                                     │   - Chart update    │
                                                     │   - Status change   │
                                                     └─────────────────────┘
```

**Perbedaan Simulator vs Hardware Asli:**

| Aspek | Simulator (Python) | Hardware (ESP32) |
|-------|-------------------|-----------------|
| Data source | Random generator | Sensor fisik (MAX30102, DS18B20) |
| Status klasifikasi | Rule-based threshold | NEWS2 scoring (age-based) |
| Kategori usia | Input interaktif | Tombol fisik (2x click) |
| Koneksi | HTTP via `requests` | HTTP via `HTTPClient` (Arduino) |
| Device ID | Config file (`devices.json`) | Hardcode di firmware |
| API Key | Config file | Hardcode di firmware |

---

## 2. Registrasi Perangkat

Sebelum perangkat bisa mengirim data, device harus terdaftar di sistem.

### 2.1 Registrasi via API

```bash
POST http://localhost:8000/api/device/register
Content-Type: application/json

{
    "device_id": "SATS-DEV04",
    "name": "SATS Ambulance Unit 04",
    "rate_limit_per_minute": 60
}
```

**Response (201):**
```json
{
    "success": true,
    "message": "Device registered successfully",
    "data": {
        "device_id": "SATS-DEV04",
        "name": "SATS Ambulance Unit 04",
        "api_key": "dev_xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx",
        "rate_limit_per_minute": 60,
        "expires_at": "2027-06-13T00:00:00.000000Z",
        "note": "Store the API key securely. It will not be shown again."
    }
}
```

> **PENTING:** Simpan `api_key` dari response! Key ini hanya ditampilkan sekali saat registrasi. Simpan untuk di-hardcode di firmware ESP32.

### 2.2 Registrasi via Dashboard Superadmin

Alternatif: Buka dashboard superadmin → Manajemen Alat → Tambah Alat. Isi device_id dan nama, API key akan otomatis di-generate.

### 2.3 Device ID yang Digunakan

Di kode hardware (`third_test.cpp`), device ID ditampilkan di LCD Page 3:
```cpp
// Line 287 di third_test.cpp
line2 = "ID: SATS-DEV04";
```

Gunakan `SATS-DEV04` sebagai device_id saat registrasi. Pastikan konsisten antara yang di-hardcode di firmware dan yang terdaftar di database.

---

## 3. API Endpoints yang Dibutuhkan

### 3.1 Kirim Data Sensor (WAJIB — setiap detik)

```
POST /api/device/{device_id}/sensor-data
Headers:
    X-API-Key: dev_xxxxxxxx
    Content-Type: application/json
    Idempotency-Key: {random_32_hex_chars}

Body:
{
    "heart_rate": 82,
    "spo2": 97,
    "temperature": 36.8,
    "status": "normal",
    "kategori_usia": "Dewasa"
}

Response: 202 Accepted
{
    "success": true,
    "message": "Sensor data queued successfully"
}
```

**Validasi yang berlaku di server:**

| Field | Tipe | Range | Keterangan |
|-------|------|-------|------------|
| `heart_rate` | integer | 0-250 | bpm |
| `spo2` | integer | 0-100 | persen |
| `temperature` | numeric | 0-45 | Celcius |
| `status` | string | `normal`, `warning`, `critical`, `no_finger` | **HARUS lowercase** |
| `kategori_usia` | string | `Balita`, `Anak-anak`, `Dewasa`, `Lansia` | **Case-sensitive** |

### 3.2 Update Status Device (WAJIB — saat hidup/mati)

```
PATCH /api/device/{device_id}/status
Headers:
    X-API-Key: dev_xxxxxxxx
    Content-Type: application/json

Body:
{
    "status": "online"    // atau "offline"
}

Response: 200 OK
{
    "success": true,
    "message": "Device status updated to online",
    "data": {
        "device_id": "SATS-DEV04",
        "status": "online"
    }
}
```

> **PENTING:** Endpoint ini memicu **auto-create monitoring session** saat `online` dan **auto-finalize session** saat `offline`. Ini adalah kunci dari lifecycle monitoring.

### 3.3 Kirim System Status (OPSIONAL — untuk info battery/signal)

```
POST /api/device/{device_id}/system-status
Headers:
    X-API-Key: dev_xxxxxxxx
    Content-Type: application/json
    Idempotency-Key: {random_32_hex_chars}

Body:
{
    "monitoring_status": "active",
    "battery_level": 88,
    "signal_strength": 75
}

Response: 202 Accepted
```

### 3.4 Autentikasi Device (OPSIONAL — untuk verifikasi koneksi)

```
POST /api/device/{device_id}/authenticate
Headers:
    X-API-Key: dev_xxxxxxxx

Body:
{
    "device_id": "SATS-DEV04"
}

Response: 200 OK
```

---

## 4. Mapping Data Hardware → API

### 4.1 Heart Rate

| Hardware (ESP32) | API Field | Notes |
|------------------|-----------|-------|
| `smoothHR` (float) | `heart_rate` (integer) | Cast ke integer: `(int)(smoothHR + 0.5f)` |
| Range valid: 40-200 bpm | Range valid: 0-250 | Sudah cocok |

```cpp
// Di third_test.cpp, line 778:
int dHR = hasHR ? (int)(smoothHR + 0.5f) : 0;
// → Kirim dHR sebagai heart_rate
```

### 4.2 SpO2

| Hardware (ESP32) | API Field | Notes |
|------------------|-----------|-------|
| `smoothSpO2` (float) | `spo2` (integer) | Cast ke integer: `(int)(smoothSpO2 + 0.5f)` |
| Range valid: 70-100% | Range valid: 0-100% | Sudah cocok |

```cpp
// Di third_test.cpp, line 779:
int dSpO2 = hasSpO2 ? (int)(smoothSpO2 + 0.5f) : 0;
// → Kirim dSpO2 sebagai spo2
```

### 4.3 Temperature

| Hardware (ESP32) | API Field | Notes |
|------------------|-----------|-------|
| `smoothTempVal` (float) | `temperature` (numeric) | Langsung kirim float |
| Range valid: >32°C (body temp) | Range valid: 0-45 | Sudah cocok |

```cpp
// Di third_test.cpp, line 780:
float dTemp = smoothTempVal > 0 ? smoothTempVal : 0;
// → Kirim dTemp sebagai temperature
```

### 4.4 Status (Klasifikasi)

| Hardware (ESP32) | API Field | Mapping |
|------------------|-----------|---------|
| `"NORMAL"` | `"normal"` | **HARUS di-lowercase** |
| `"WARNING"` | `"warning"` | **HARUS di-lowercase** |
| `"CRITICAL"` | `"critical"` | **HARUS di-lowercase** |
| `"WAITING"` | `"no_finger"` | Mapping khusus: WAITING → no_finger |

> **CRITICAL:** API hanya menerima lowercase (`normal`, `warning`, `critical`, `no_finger`). Hardware menghasilkan UPPERCASE (`NORMAL`, `WARNING`, `CRITICAL`, `WAITING`). **Harus di-convert ke lowercase sebelum dikirim.**

```cpp
// Contoh mapping di ESP32:
const char* apiStatus;
if (strcmp(currentStatus, "NORMAL") == 0) apiStatus = "normal";
else if (strcmp(currentStatus, "WARNING") == 0) apiStatus = "warning";
else if (strcmp(currentStatus, "CRITICAL") == 0) apiStatus = "critical";
else apiStatus = "no_finger";  // WAITING atau status lain
```

### 4.5 Kategori Usia

| Hardware (ESP32) | API Field | Mapping |
|------------------|-----------|---------|
| `BALITA` (enum 0) | `"Balita"` | String sesuai enum name |
| `ANAK_ANAK` (enum 1) | `"Anak-anak"` | **PERHATIAN:** Ada dash (`-`) |
| `DEWASA` (enum 2) | `"Dewasa"` | String sesuai enum name |
| `LANSIA` (enum 3) | `"Lansia"` | String sesuai enum name |

> **CRITICAL:** Untuk kategori `ANAK_ANAK`, API menerima `"Anak-anak"` (dengan dash). Di hardware, fungsi `getAgeName()` mengembalikan `"Anak"` (tanpa dash). **Harus di-map manual ke `"Anak-anak"`.**

```cpp
// Mapping kategori usia untuk API:
const char* getApiKategoriUsia(AgeCategory age) {
    switch (age) {
        case BALITA:     return "Balita";
        case ANAK_ANAK:  return "Anak-anak";  // DENGAN DASH!
        case DEWASA:     return "Dewasa";
        case LANSIA:     return "Lansia";
        default:         return "Dewasa";
    }
}
```

### 4.6 Ringkasan Mapping Lengkap

```
┌─────────────────────────────────────────────────────────────────┐
│                    HARDWARE → API MAPPING                       │
├─────────────────────┬───────────────────┬───────────────────────┤
│ Hardware (ESP32)    │ API Field         │ Transformasi          │
├─────────────────────┼───────────────────┼───────────────────────┤
│ smoothHR (float)    │ heart_rate (int)  │ (int)(smoothHR + 0.5) │
│ smoothSpO2 (float)  │ spo2 (int)        │ (int)(smoothSpO2+0.5) │
│ smoothTempVal(float)│ temperature(num)  │ langsung kirim        │
│ currentStatus       │ status (string)   │ UPPERCASE → lowercase │
│   "NORMAL"          │   "normal"        │                       │
│   "WARNING"         │   "warning"       │                       │
│   "CRITICAL"        │   "critical"      │                       │
│   "WAITING"         │   "no_finger"     │ mapping khusus        │
│ currentAge          │ kategori_usia     │ enum → string         │
│   BALITA            │   "Balita"        │                       │
│   ANAK_ANAK         │   "Anak-anak"     │ DENGAN DASH           │
│   DEWASA            │   "Dewasa"        │                       │
│   LANSIA            │   "Lansia"        │                       │
└─────────────────────┴───────────────────┴───────────────────────┘
```

### 4.7 Handling Nilai 0 (No Finger / Sensor Belum Siap)

**Masalah:** ESP32 mengirim `heart_rate: 0` dan `spo2: 0` saat jari belum terdeteksi atau sensor belum stabil. Temperature (DS18B20) biasanya sudah terbaca lebih dulu.

**Urutan inisialisasi sensor di ESP32:**
1. **Temperature** → ~2-3 detik (DS18B20 langsung baca)
2. **Heart Rate** → ~5-10 detik (butuh 3 peak terdeteksi)
3. **SpO2** → ~10-15 detik (butuh buffer 100 sample + algoritma)

**Yang sudah di-handle di website:**

| Komponen | Penanganan |
|----------|------------|
| **Chart** | Nilai 0 di-convert ke `null` → tidak ditampilkan di chart (tidak mengganggu Y-axis) |
| **Stat Card** | Nilai 0 ditampilkan sebagai "—" |
| **ML Prediction** | Hanya menggunakan data dengan HR > 0 DAN SpO2 > 0 |
| **Database** | Tetap menyimpan 0 (data asli dari perangkat) |

**Rekomendasi untuk ESP32 (opsional, tidak wajib):**

Jika ingin data yang masuk ke database selalu valid, tambahkan pengecekan sebelum mengirim:

```cpp
// Hanya kirim data jika HR dan SpO2 sudah terbaca
if (hasHR && hasSpO2) {
    sendSensorData(dHR, dSpO2, dTemp, getApiStatus(currentStatus), getApiKategoriUsia(currentAge));
}
```

Ini akan mengurangi data "noise" di database, tapi website sudah bisa menangani data 0 dengan benar.

---

## 5. Alur Data Real-Time

### 5.1 Flow Diagram

```
ESP32 Hardware                    SATS Server                      Browser
     │                                │                                │
     │  1. POST /sensor-data          │                                │
     │  {heart_rate, spo2,            │                                │
     │   temperature, status,         │                                │
     │   kategori_usia}               │                                │
     │ ─────────────────────────────► │                                │
     │                                │  2. Broadcast WebSocket        │
     │                                │  (SensorDataReceived event)    │
     │                                │ ──────────────────────────────►│
     │                                │                                │
     │                                │  3. Dispatch background job    │
     │                                │  (ProcessSensorData)           │
     │                                │                                │
     │  4. 202 Accepted               │                                │
     │ ◄───────────────────────────── │                                │
     │                                │  5. Job: Write to DB           │
     │                                │  (sensor_datas table)          │
     │                                │                                │
     │                                │  6. Trigger ML prediction      │
     │                                │  (setiap 5 data baru)          │
     │                                │                                │
     │  7. PATCH /status: online      │                                │
     │ ─────────────────────────────► │                                │
     │                                │  8. Auto-create session        │
     │                                │  + Broadcast status change     │
     │                                │ ──────────────────────────────►│
```

### 5.2 Real-Time Tanpa Polling

Data dari hardware sampai ke dashboard **tanpa polling**. Alurnya:

1. ESP32 mengirim HTTP POST ke `/api/device/{id}/sensor-data`
2. Server **langsung** broadcast data via WebSocket (`SensorDataReceived` event) — **sebelum** data ditulis ke DB
3. Dashboard yang sudah subscribe ke channel `device.{deviceId}` langsung menerima data
4. Background job menulis data ke database secara async

**Kenapa tanpa polling?**
- Controller `SensorDataController::storeSensorData()` memanggil `SensorDataReceived::dispatch()` **sebelum** dispatch job
- Event ini menggunakan `ShouldBroadcastNow` — langsung di-broadcast, tidak masuk queue
- Dashboard menggunakan Laravel Echo untuk listen ke private channel

### 5.3 Channel WebSocket

Dashboard subscribe ke channel:
```javascript
Echo.private(`device.${deviceId}`)
    .listen('.sensor.data.received', (data) => {
        // Update chart, vital signs, status secara real-time
    });
```

Data yang dikirim via WebSocket (dari `SensorDataReceived::broadcastWith()`):
```json
{
    "device_id": "SATS-DEV04",
    "latest": {
        "heart_rate": 82,
        "spo2": 97,
        "temperature": 36.8,
        "status": "normal",
        "kategori_usia": "Dewasa",
        "created_at": "14:30",
        "ml_prediction": "Pasien akan STABIL (72%)",
        "ml_condition": "NORMAL",
        "ml_risk_level": "Low Risk",
        "ml_probabilities": {"membaik": 15, "stabil": 72, "memburuk": 13},
        "ml_predicted_at": "14:28"
    },
    "history": {
        "labels": ["14:20", "14:21", ...],
        "heart_rate": [80, 82, ...],
        "spo2": [97, 96, ...],
        "temperature": [36.7, 36.8, ...]
    }
}
```

---

## 6. Handling Kategori Usia & Reset

### 6.1 Perilaku Hardware Saat Ganti Kategori

Di kode hardware (`third_test.cpp`, line 470-483):

```cpp
case 2:  // Double-click: Ganti Mode
    if (currentAge == BALITA) currentAge = ANAK_ANAK;
    else if (currentAge == ANAK_ANAK) currentAge = DEWASA;
    else if (currentAge == DEWASA) currentAge = LANSIA;
    else currentAge = BALITA;
    // Simpan ke NVS
    preferences.begin("sats", false);
    preferences.putInt("age", (int)currentAge);
    preferences.end();
    // RESTART!
    ESP.restart();
    break;
```

**Yang terjadi saat user ganti kategori usia (2x click):**
1. Kategori usia berputar: Balita → Anak-anak → Dewasa → Lansia → Balita
2. Disimpan ke NVS (persistent storage)
3. **ESP32 RESTART** — semua state di-reset

### 6.2 Dampak ke Website

Saat ESP32 restart:

| Aspek | Dampak | Solusi |
|-------|--------|--------|
| Koneksi WiFi | Terputus, reconnect otomatis | WiFiManager handles ini |
| HTTP Client | Terputus | Perlu reconnect setelah restart |
| Data sensor | Reset (`resetSensorData()`) | Normal — data baru mulai dari nol |
| Monitoring session | **TIDAK terpengaruh** | Session tetap active sampai device kirim `offline` |
| Device status | Tetap `online` di DB | Perlu kirim `PATCH /status: online` lagi setelah restart |

### 6.3 Alur Setelah Restart

```
ESP32 Restart
     │
     ├─ 1. Setup: WiFi reconnect (otomatis)
     ├─ 2. Setup: Kirim PATCH /status: online
     │        → Server: device sudah online, session tetap active
     ├─ 3. Loop: Tunggu jari terdeteksi
     ├─ 4. Loop: Kirim data sensor (dengan kategori usia baru)
     │        → Data masuk ke session yang sama
     └─ 5. Saat device dimatikan: Kirim PATCH /status: offline
              → Server: finalize session, copy data ke sensor_readings
```

> **PENTING:** Setelah restart, ESP32 **HARUS** mengirim `PATCH /status: online` lagi. Jika tidak, server menganggap device masih online (status tidak berubah otomatis). Namun, data sensor tetap bisa masuk karena API key authentication tidak terpengaruh status device.

### 6.4 Implikasi untuk Monitoring Session

- **Session tidak dibuat ulang** saat restart — hanya dibuat saat device pertama kali online
- **Data dari kategori usia berbeda** akan masuk ke **session yang sama** — ini sesuai desain karena session = 1 kali perjalanan ambulans
- **Finalisasi session** terjadi saat device kirim `offline`, bukan saat restart

---

## 7. Monitoring Session Lifecycle

### 7.1 Lifecycle Diagram

```
┌─────────────────────────────────────────────────────────────────┐
│                 MONITORING SESSION LIFECYCLE                     │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│  Device ON                                                      │
│  (PATCH /status: online)                                        │
│      │                                                          │
│      ▼                                                          │
│  ┌──────────────┐                                               │
│  │   SESSION    │ ◄── Auto-created by server                    │
│  │   ACTIVE     │     (MonitoringSessionService::createSession) │
│  │              │                                               │
│  │  sensor_data │ ◄── Data masuk dari POST /sensor-data         │
│  │  ┌────────┐  │                                               │
│  │  │ HR: 82 │  │                                               │
│  │  │ SpO2:97│  │                                               │
│  │  │ Temp:  │  │                                               │
│  │  │ 36.8   │  │                                               │
│  │  └────────┘  │                                               │
│  └──────────────┘                                               │
│      │                                                          │
│      │  Device OFF                                              │
│      │  (PATCH /status: offline)                                │
│      ▼                                                          │
│  ┌──────────────┐                                               │
│  │   SESSION    │ ◄── Auto-finalized by server                  │
│  │  COMPLETED   │     (MonitoringSessionService::finalizeSession)│
│  │              │                                               │
│  │  1. Copy sensor_data → sensor_readings (permanen)            │
│  │  2. Update session status = completed                        │
│  │  3. Delete semua sensor_data untuk device ini                │
│  └──────────────┘                                               │
│                                                                 │
└─────────────────────────────────────────────────────────────────┘
```

### 7.2 Apa yang Terjadi di Server

**Saat device online (`PATCH /status: online`):**
```php
// DeviceDataController::updateDeviceStatus() line 305-315
if ($status === 'online') {
    $nakesConfig = NakesDeviceConfig::where('device_id', $deviceId)->first();
    $userId = $nakesConfig?->user_id ?? 1;  // fallback ke user 1

    $activeSession = $this->sessionService->getActiveSession($deviceId);
    if (!$activeSession) {
        $session = $this->sessionService->createSession($deviceId, $userId);
    }
}
```

**Saat device offline (`PATCH /status: offline`):**
```php
// DeviceDataController::updateDeviceStatus() line 319-325
if ($status === 'offline') {
    $activeSession = $this->sessionService->getActiveSession($deviceId);
    if ($activeSession) {
        $this->sessionService->finalizeSession($activeSession->id);
    }
}
```

### 7.3 Medical Record Number Format

Setiap session mendapat nomor rekam medis otomatis:
```
Format: RM-{DEVICE_ID}-{YYYYMMDD}-{SEQ}
Contoh: RM-SATS-DEV04-20260613-001
```

---

## 8. Implementation Guide untuk ESP32

### 8.1 Library yang Dibutuhkan

ESP32 sudah memiliki library yang dibutuhkan:
- `WiFi.h` — sudah ada di ESP32 core
- `WiFiManager.h` — sudah digunakan di kode
- `HTTPClient.h` — **perlu ditambah** untuk HTTP requests

### 8.2 Konfigurasi yang Perlu Di-Hardcode

```cpp
// =============================================
//  API CONFIGURATION (Hardcode)
// =============================================
#define API_BASE_URL    "http://YOUR_SERVER_IP:8000/api"
#define API_KEY         "dev_xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx"
#define DEVICE_ID       "SATS-DEV04"

// Interval pengiriman data (ms)
#define SEND_INTERVAL   1000  // 1 detik (sesuai simulator)
```

### 8.3 Fungsi HTTP yang Dibutuhkan

#### 8.3.1 Kirim Data Sensor

```cpp
#include <HTTPClient.h>

bool sendSensorData(int heartRate, int spo2, float temperature, 
                    const char* status, const char* kategoriUsia) {
    if (WiFi.status() != WL_CONNECTED) return false;
    
    HTTPClient http;
    String url = String(API_BASE_URL) + "/device/" + DEVICE_ID + "/sensor-data";
    
    http.begin(url);
    http.addHeader("Content-Type", "application/json");
    http.addHeader("X-API-Key", API_KEY);
    http.addHeader("Idempotency-Key", generateIdempotencyKey());
    
    // Build JSON payload
    JsonDocument doc;
    doc["heart_rate"] = heartRate;
    doc["spo2"] = spo2;
    doc["temperature"] = temperature;
    doc["status"] = status;
    doc["kategori_usia"] = kategoriUsia;
    
    String jsonString;
    serializeJson(doc, jsonString);
    
    int httpResponseCode = http.POST(jsonString);
    http.end();
    
    return (httpResponseCode == 202);
}
```

#### 8.3.2 Update Status Device

```cpp
bool sendDeviceStatus(const char* status) {
    if (WiFi.status() != WL_CONNECTED) return false;
    
    HTTPClient http;
    String url = String(API_BASE_URL) + "/device/" + DEVICE_ID + "/status";
    
    http.begin(url);
    http.addHeader("Content-Type", "application/json");
    http.addHeader("X-API-Key", API_KEY);
    
    JsonDocument doc;
    doc["status"] = status;
    
    String jsonString;
    serializeJson(doc, jsonString);
    
    int httpResponseCode = http.PATCH(jsonString);
    http.end();
    
    return (httpResponseCode == 200);
}
```

#### 8.3.3 Kirim System Status

```cpp
bool sendSystemStatus(const char* monitoringStatus, int batteryLevel, int signalStrength) {
    if (WiFi.status() != WL_CONNECTED) return false;
    
    HTTPClient http;
    String url = String(API_BASE_URL) + "/device/" + DEVICE_ID + "/system-status";
    
    http.begin(url);
    http.addHeader("Content-Type", "application/json");
    http.addHeader("X-API-Key", API_KEY);
    http.addHeader("Idempotency-Key", generateIdempotencyKey());
    
    JsonDocument doc;
    doc["monitoring_status"] = monitoringStatus;
    doc["battery_level"] = batteryLevel;
    doc["signal_strength"] = signalStrength;
    
    String jsonString;
    serializeJson(doc, jsonString);
    
    int httpResponseCode = http.POST(jsonString);
    http.end();
    
    return (httpResponseCode == 202);
}
```

#### 8.3.4 Generate Idempotency Key

```cpp
String generateIdempotencyKey() {
    // Generate 32 hex chars (16 bytes)
    byte bytes[16];
    for (int i = 0; i < 16; i++) {
        bytes[i] = random(0, 256);
    }
    
    String hex = "";
    for (int i = 0; i < 16; i++) {
        if (bytes[i] < 16) hex += "0";
        hex += String(bytes[i], HEX);
    }
    return hex;
}
```

### 8.4 Mapping Helper Functions

```cpp
// Convert status UPPERCASE → lowercase untuk API
const char* getApiStatus(const char* status) {
    if (strcmp(status, "NORMAL") == 0) return "normal";
    if (strcmp(status, "WARNING") == 0) return "warning";
    if (strcmp(status, "CRITICAL") == 0) return "critical";
    return "no_finger";  // WAITING atau status lain
}

// Convert age category enum → string untuk API
const char* getApiKategoriUsia(AgeCategory age) {
    switch (age) {
        case BALITA:     return "Balita";
        case ANAK_ANAK:  return "Anak-anak";  // DENGAN DASH!
        case DEWASA:     return "Dewasa";
        case LANSIA:     return "Lansia";
        default:         return "Dewasa";
    }
}
```

### 8.5 Integrasi ke Main Loop

```cpp
// Tambahkan di setup(), SETELAH WiFi connected:
void setup() {
    // ... existing setup code ...
    
    // Kirim status online setelah WiFi connected
    if (wifiConnected) {
        sendDeviceStatus("online");
        sendSystemStatus("active", 88, WiFi.RSSI());
    }
}

// Tambahkan di loop(), di dalam STATE_MONITORING:
void loop() {
    // ... existing loop code ...
    
    case STATE_MONITORING:
        // ... existing sensor reading code ...
        
        // Kirim data sensor setiap SEND_INTERVAL
        static unsigned long lastSendTime = 0;
        if (millis() - lastSendTime >= SEND_INTERVAL) {
            lastSendTime = millis();
            
            int dHR = hasHR ? (int)(smoothHR + 0.5f) : 0;
            int dSpO2 = hasSpO2 ? (int)(smoothSpO2 + 0.5f) : 0;
            float dTemp = smoothTempVal > 0 ? smoothTempVal : 0;
            
            sendSensorData(
                dHR,
                dSpO2,
                dTemp,
                getApiStatus(currentStatus),
                getApiKategoriUsia(currentAge)
            );
        }
        break;
}
```

### 8.6 Handling Device Mati/Hidup

ESP32 tidak bisa mengirim `offline` saat power dicabut. **Server sudah punya solusi otomatis:**

#### Auto-Offline (Sudah Terimplementasi)

Server memiliki scheduled command `devices:mark-stale-offline` yang berjalan setiap 5 detik:

```
Alur auto-offline:
1. Scheduler cek device dengan status=online dan last_seen > 30 detik
2. Jika ditemukan → set status=offline, finalisasi session, broadcast status change
3. Device berikutnya kirim data → auto-reactivate (status=online, session baru)
```

**Bagaimana ini bekerja dengan ESP32:**
- ESP32 mati mendadak → tidak kirim data → `last_seen` tidak ter-update
- Setelah 30 detik → scheduler tandai device offline → session finalisasi
- ESP32 hidup lagi → kirim data pertama → device otomatis aktif kembali → session baru dibuat

**Tidak perlu perubahan di ESP32!** Cukup kirim data sensor secara berkala, server handle sisanya.

#### Graceful Shutdown (Opsional, untuk UX lebih baik)

Jika ESP32 punya tombol power, kirim offline sebelum mati:

```cpp
void gracefulShutdown() {
    sendSensorData(0, 0, 0, "no_finger", getApiKategoriUsia(currentAge));
    sendDeviceStatus("offline");
    delay(500);  // Tunggu HTTP selesai
    ESP.deepSleep(0);
}
```

Ini membuat device langsung offline di dashboard (tidak perlu tunggu 30 detik timeout).

---

## 9. Testing & Debugging

### 9.1 Test dengan Postman/curl

**Test registrasi device:**
```bash
curl -X POST http://localhost:8000/api/device/register \
  -H "Content-Type: application/json" \
  -d '{"device_id": "TEST-01", "name": "Test Device"}'
```

**Test kirim sensor data:**
```bash
curl -X POST http://localhost:8000/api/device/TEST-01/sensor-data \
  -H "Content-Type: application/json" \
  -H "X-API-Key: dev_xxxxxxxx" \
  -H "Idempotency-Key: abc123def456" \
  -d '{"heart_rate": 82, "spo2": 97, "temperature": 36.8, "status": "normal", "kategori_usia": "Dewasa"}'
```

**Test update status:**
```bash
curl -X PATCH http://localhost:8000/api/device/TEST-01/status \
  -H "Content-Type: application/json" \
  -H "X-API-Key: dev_xxxxxxxx" \
  -d '{"status": "online"}'
```

### 9.2 Monitoring di Server

**Cek log device:**
```bash
tail -f storage/logs/laravel.log | grep "device_id"
```

**Cek log audit device:**
```bash
tail -f storage/logs/device-audit.log
```

**Cek database:**
```sql
-- Cek device terdaftar
SELECT * FROM devices WHERE device_id = 'SATS-DEV04';

-- Cek API key
SELECT * FROM api_keys WHERE device_id = 'SATS-DEV04';

-- Cek monitoring session
SELECT * FROM monitoring_sessions WHERE device_id = 'SATS-DEV04' ORDER BY id DESC LIMIT 5;

-- Cek sensor data terbaru
SELECT * FROM sensor_datas WHERE device_id = 'SATS-DEV04' ORDER BY created_at DESC LIMIT 10;

-- Cek activity log
SELECT * FROM activity_log WHERE device_id = 'SATS-DEV04' ORDER BY created_at DESC LIMIT 10;
```

### 9.3 Verifikasi Real-Time

1. Buka dashboard di browser (nakes/dokter)
2. Buka browser DevTools → Network → WS tab
3. Pastikan ada koneksi WebSocket ke channel `device.SATS-DEV04`
4. Kirim data dari hardware → data harus langsung muncul di dashboard tanpa refresh

### 9.4 Cek Response Code

| Code | Arti | Action |
|------|------|--------|
| `202` | Data diterima, di-queue | ✅ Berhasil |
| `200` | Status updated | ✅ Berhasil |
| `401` | API key tidak valid | ❌ Cek API key |
| `404` | Device tidak ditemukan | ❌ Registrasi device dulu |
| `422` | Validasi gagal | ❌ Cek format data |
| `429` | Rate limit exceeded | ⚠️ Kurangi frekuensi kirim |
| `409` | Duplikat (idempotent) | ⚠️ Normal, abaikan |

---

## 10. Troubleshooting

### 10.1 Device Tidak Bisa Kirim Data

**Symptom:** HTTP 401 Unauthorized
```
"message": "API key missing in X-API-Key header"
```
**Solusi:** Pastikan header `X-API-Key` dikirim dengan benar.

---

**Symptom:** HTTP 401 Invalid API key
```
"message": "Invalid API key for this device or key expired"
```
**Solusi:** 
1. Cek API key di database: `SELECT * FROM api_keys WHERE device_id = 'SATS-DEV04'`
2. Pastikan `is_active = 1` dan `expires_at` belum lewat
3. Pastikan key yang di-hardcode di ESP32 benar

---

**Symptom:** HTTP 404 Device not found
```
"message": "Device not found"
```
**Solusi:** Registrasi device dulu via API atau dashboard superadmin.

---

**Symptom:** HTTP 422 Validation error
```
"message": "The status field must be one of: normal, warning, critical, no_finger"
```
**Solusi:** Pastikan status dalam **lowercase**. Hardware menghasilkan UPPERCASE, harus di-convert.

---

### 10.2 Data Tidak Muncul di Dashboard

**Symptom:** Data masuk ke database tapi dashboard tidak update

**Solusi:**
1. Pastikan Laravel Reverb berjalan: `php artisan reverb:start`
2. Pastikan WebSocket connection aktif di browser (cek DevTools → WS)
3. Pastikan device status `online` (bukan `offline`)

---

### 10.3 Monitoring Session Bermasalah

**Symptom:** Session tidak dibuat saat device online

**Solusi:**
1. Pastikan mengirim `PATCH /status: online` (bukan POST)
2. Cek log server untuk error
3. Pastikan ada user dengan role `nakes` yang ter-assign ke device via `nakes_device_configs`

---

**Symptom:** Session tidak finalisasi saat device offline

**Solusi:**
1. Pastikan mengirim `PATCH /status: offline`
2. Jika device mati mendadak (power loss), server tidak bisa finalisasi otomatis
3. Perlu mekanisme timeout di server (lihat opsi heartbeat di section 8.6)

---

### 10.4 Kategori Usia Tidak Sesuai

**Symptom:** Data masuk dengan kategori usia yang salah

**Solusi:**
1. Pastikan mapping `ANAK_ANAK` → `"Anak-anak"` (DENGAN DASH)
2. Jangan gunakan `getAgeName()` langsung karena mengembalikan `"Anak"` (tanpa dash)
3. Buat fungsi `getApiKategoriUsia()` khusus untuk API

---

## Appendix A: Contoh JSON Payload Lengkap

### Sensor Data (Normal, Dewasa)
```json
{
    "heart_rate": 82,
    "spo2": 97,
    "temperature": 36.8,
    "status": "normal",
    "kategori_usia": "Dewasa"
}
```

### Sensor Data (Critical, Balita)
```json
{
    "heart_rate": 170,
    "spo2": 85,
    "temperature": 39.5,
    "status": "critical",
    "kategori_usia": "Balita"
}
```

### Sensor Data (No Finger)
```json
{
    "heart_rate": 0,
    "spo2": 0,
    "temperature": 0,
    "status": "no_finger",
    "kategori_usia": "Dewasa"
}
```

### Device Status (Online)
```json
{
    "status": "online"
}
```

### System Status
```json
{
    "monitoring_status": "active",
    "battery_level": 88,
    "signal_strength": 75
}
```

---

## Appendix B: Ringkasan Perubahan yang Dibutuhkan

### Tidak Mengubah Kode Hardware (Sesuai Ketentuan)

Dokumen ini hanya menjelaskan **cara integrasi**, bukan mengubah kode. Perubahan yang dijelaskan adalah **tambahan kode** untuk HTTP client, bukan modifikasi kode existing.

### Yang Perlu Ditambahkan ke ESP32:

1. **Include library:** `HTTPClient.h`, `ArduinoJson.h`
2. **Konfigurasi:** `API_BASE_URL`, `API_KEY`, `DEVICE_ID`, `SEND_INTERVAL`
3. **Fungsi HTTP:** `sendSensorData()`, `sendDeviceStatus()`, `sendSystemStatus()`
4. **Helper:** `getApiStatus()`, `getApiKategoriUsia()`, `generateIdempotencyKey()`
5. **Integrasi:** Panggil fungsi HTTP di `setup()` dan `loop()`

### Yang TIDAK Perlu Diubah:

1. Sensor reading logic (MAX30102, DS18B20)
2. NEWS2 classification algorithm
3. Button handling
4. Display logic (OLED, LCD)
5. WiFi connection (WiFiManager)
6. State machine (IDLE, MONITORING)
