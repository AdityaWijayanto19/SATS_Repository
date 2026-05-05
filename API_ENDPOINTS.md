# API Endpoints - SATS IoT Device

## Base URL
```
http://localhost:8000/api
```

## Authentication
Semua endpoint (kecuali health check) memerlukan:
```
Header: X-API-Key: test_key_device_01
```

---

## 1. Health Check (Test Koneksi)
**Tidak perlu API Key**

```
GET /
```

**Response (200 OK):**
```json
{
  "message": "API SATS running",
  "version": "1.0.0",
  "timestamp": "2026-05-04T12:00:00Z"
}
```

**Contoh cURL:**
```bash
curl -X GET http://localhost:8000/api/
```

---

## 2. Store Sensor Data ⭐ (PALING SERING DIPAKAI)

Endpoint untuk **mengirim data sensor real-time** dari perangkat ke server.

```
POST /device/{device_id}/sensor-data
```

**Full URL:**
```
POST http://localhost:8000/api/device/DEVICE_01/sensor-data
```

**Required Headers:**
```
X-API-Key: test_key_device_01
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

**Field Details:**

| Field | Type | Required | Range | Description |
|-------|------|----------|-------|-------------|
| `heart_rate` | integer | ❌ | 0-250 | BPM (beats per minute) |
| `spo2` | integer | ❌ | 0-100 | Oxygen saturation percentage |
| `temperature` | float | ❌ | 20-45 | Celsius |
| `status` | string | ❌ | normal/warning/critical | Health status |
| `prediction` | string | ❌ | Max 50 chars | Prediction result |

**Response (201 Created):**
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
  -d '{
    "heart_rate": 85,
    "spo2": 98,
    "temperature": 36.5,
    "status": "normal"
  }'
```

---

## 3. Get Latest Sensor Data

Endpoint untuk **mengambil data sensor terbaru** yang sudah disimpan.

```
GET /device/{device_id}/sensor-data/latest
```

**Full URL:**
```
GET http://localhost:8000/api/device/DEVICE_01/sensor-data/latest
```

**Required Headers:**
```
X-API-Key: test_key_device_01
```

**Response (200 OK):**
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

**Response (404 Not Found):**
```json
{
  "success": false,
  "message": "No sensor data found"
}
```

**Contoh cURL:**
```bash
curl -X GET http://localhost:8000/api/device/DEVICE_01/sensor-data/latest \
  -H "X-API-Key: test_key_device_01"
```

---

## 4. Store System Status

Endpoint untuk **mengirim status sistem** (battery, signal, monitoring status).

```
POST /device/{device_id}/system-status
```

**Full URL:**
```
POST http://localhost:8000/api/device/DEVICE_01/system-status
```

**Required Headers:**
```
X-API-Key: test_key_device_01
Content-Type: application/json
```

**Request Body:**
```json
{
  "monitoring_status": "active",
  "battery_level": 85,
  "signal_strength": -45
}
```

**Field Details:**

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `monitoring_status` | string | ❌ | `active` atau `inactive` |
| `battery_level` | integer | ❌ | 0-100 (percentage) |
| `signal_strength` | integer | ❌ | dBm value (usually -30 to -100) |

**Response (200 OK):**
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

**Contoh cURL:**
```bash
curl -X POST http://localhost:8000/api/device/DEVICE_01/system-status \
  -H "X-API-Key: test_key_device_01" \
  -H "Content-Type: application/json" \
  -d '{
    "monitoring_status": "active",
    "battery_level": 85,
    "signal_strength": -45
  }'
```

---

## 5. Get System Status

Endpoint untuk **mengambil status sistem** yang sudah disimpan.

```
GET /device/{device_id}/system-status
```

**Full URL:**
```
GET http://localhost:8000/api/device/DEVICE_01/system-status
```

**Required Headers:**
```
X-API-Key: test_key_device_01
```

**Response (200 OK):**
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

**Response (404 Not Found):**
```json
{
  "success": false,
  "message": "No system status found"
}
```

**Contoh cURL:**
```bash
curl -X GET http://localhost:8000/api/device/DEVICE_01/system-status \
  -H "X-API-Key: test_key_device_01"
```

---

## 6. Get Device Configuration

Endpoint untuk **mengambil konfigurasi device** dari server.

```
GET /device/{device_id}/config
```

**Full URL:**
```
GET http://localhost:8000/api/device/DEVICE_01/config
```

**Required Headers:**
```
X-API-Key: test_key_device_01
```

**Response (200 OK):**
```json
{
  "success": true,
  "data": {
    "device_id": "DEVICE_01",
    "status": "online",
    "last_seen": "2026-05-04T12:00:00Z",
    "created_at": "2026-04-30T10:00:00Z"
  }
}
```

**Contoh cURL:**
```bash
curl -X GET http://localhost:8000/api/device/DEVICE_01/config \
  -H "X-API-Key: test_key_device_01"
```

---

## 7. Get Latest Sensor Data (Legacy)

Endpoint lama untuk backward compatibility.

```
GET /sensor-data/{device_id}/latest
```

**Full URL:**
```
GET http://localhost:8000/api/sensor-data/DEVICE_01/latest
```

**Response (200 OK):**
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
    "created_at": "2026-05-04T12:00:00Z"
  }
}
```

---

## Error Responses

### 401 Unauthorized
```json
{
  "success": false,
  "message": "Invalid API key for this device or key expired"
}
```

### 422 Validation Error
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "heart_rate": ["The heart rate must be between 0 and 250."],
    "spo2": ["The spo2 must be between 0 and 100."]
  }
}
```

### 404 Not Found
```json
{
  "success": false,
  "message": "Device not found"
}
```

### 500 Server Error
```json
{
  "success": false,
  "message": "Server error: [error details]"
}
```

---

## Test Credentials

**Device ID:** `DEVICE_01`  
**API Key:** `test_key_device_01`

**Device ID:** `DEVICE_02`  
**API Key:** `test_key_device_02`

---

## IoT Device Integration Flow

```
┌─────────────────────────────────────────────────────────┐
│                    IoT Device                            │
│                                                          │
│  1. Baca Sensor (Heart Rate, SpO2, Temperature)        │
│  2. POST /device/{id}/sensor-data                       │
│  3. POST /device/{id}/system-status (optional)          │
│  4. GET /device/{id}/sensor-data/latest (optional)      │
│  5. GET /device/{id}/system-status (optional)           │
│  6. Repeat setiap 10-60 detik                           │
│                                                          │
└─────────────────────────────────────────────────────────┘
```

---

## Recommended Interval

- **Sensor Data:** Setiap 10-30 detik
- **System Status:** Setiap 1-5 menit
- **Get Latest Data:** Setiap 5-10 menit (optional)

---

## HTTP Status Codes

| Code | Meaning |
|------|---------|
| 200 | OK - Request berhasil |
| 201 | Created - Data berhasil disimpan |
| 302 | Redirect - Biasanya form validation failed |
| 401 | Unauthorized - API Key invalid |
| 404 | Not Found - Resource tidak ditemukan |
| 422 | Unprocessable Entity - Validation error |
| 500 | Server Error |

---

Last Updated: 2026-05-04
