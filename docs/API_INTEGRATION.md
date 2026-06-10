# API Integration Guide

Panduan integrasi API Patient Monitoring ke web project.

---

## Cara Kerja API

API menggunakan **2 langkah** (Gradio 6.x):

```
┌─────────────────┐         ┌─────────────────┐
│   Web / App     │         │  Hugging Face   │
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

---

## API Detail

```
Base URL: https://dalvero-sats-monitoring.hf.space
```

### Step 1: Kirim Data → Dapat `event_id`

```
POST /gradio_api/call/predict_manual
Content-Type: application/json
```

**Request Body:**

```json
{
    "data": ["Dewasa", 80, 36.7, 97, 85, 36.8, 96, 90, 36.9, 95, 95, 37.0, 94, 100, 37.2, 93]
}
```

**Urutan data:**

| Index | Isi | Tipe | Keterangan |
|-------|-----|------|------------|
| 0 | Kategori Usia | string | "Balita" / "Anak-anak" / "Dewasa" / "Lansia" |
| 1-3 | Menit 1 | float | HR, Temp, SpO2 |
| 4-6 | Menit 2 | float | HR, Temp, SpO2 |
| 7-9 | Menit 3 | float | HR, Temp, SpO2 |
| 10-12 | Menit 4 | float | HR, Temp, SpO2 |
| 13-15 | Menit 5 | float | HR, Temp, SpO2 |

Total: **1 string + 15 angka = 16 elemen**

**Response:**

```json
{
    "event_id": "a1b2c3d4e5f6..."
}
```

### Step 2: Ambil Hasil Pakai `event_id`

```
GET /gradio_api/call/predict_manual/{event_id}
```

**Response (Server-Sent Events format):**

```
event: complete
data: ["Pasien akan MEMBURUK (51%) dalam 5 menit ke depan", "Membaik   :  19% ####\nStabil    :  30% ######\nMemburuk  :  51% ##########", "WARNING", "Medium Risk", 19, 30, 51]
```

**Parse `data` array:**

| Index | Isi | Tipe | Contoh |
|-------|-----|------|--------|
| `[0]` | Prediksi | string | `"Pasien akan MEMBURUK (51%) dalam 5 menit ke depan"` |
| `[1]` | Probabilitas detail (bar chart) | string | `"Membaik: 19% ..."` |
| `[2]` | Kondisi | string | `"NORMAL"` / `"WARNING"` / `"CRITICAL"` |
| `[3]` | Risk Level | string | `"Low Risk"` / `"Medium Risk"` / `"High Risk"` |
| `[4]` | Membaik (%) | number | `19` |
| `[5]` | Stabil (%) | number | `30` |
| `[6]` | Memburuk (%) | number | `51` |

> **Tip:** Gunakan index `[4]`, `[5]`, `[6]` untuk menampilkan probabilitas di frontend. Nilainya sudah berupa angka bulat (0-100), langsung bisa dipakai.

---

## Kategori Usia

| Value | Keterangan |
|-------|------------|
| `"Balita"` | Usia 1-5 tahun |
| `"Anak-anak"` | Usia 6-11 tahun |
| `"Dewasa"` | Usia 12-65 tahun |
| `"Lansia"` | Usia 65++ tahun |

Threshold vital signs menyesuaikan kategori usia. HR 110 bpm normal untuk Balita tapi WARNING untuk Lansia.

---

## Contoh Data Request

### Normal (Dewasa, sehat)

```json
{
    "data": ["Dewasa", 80, 36.7, 97, 78, 36.6, 97, 82, 36.7, 98, 79, 36.8, 97, 81, 36.7, 97]
}
```

### Warning (Dewasa, menuju kritis)

```json
{
    "data": ["Dewasa", 85, 36.8, 96, 90, 36.9, 95, 95, 37.0, 94, 100, 37.1, 93, 105, 37.2, 92]
}
```

### Critical (Dewasa, kritis)

```json
{
    "data": ["Dewasa", 100, 37.5, 91, 110, 37.8, 89, 120, 38.2, 87, 125, 38.5, 85, 130, 39.0, 82]
}
```

### Balita (sehat)

```json
{
    "data": ["Balita", 100, 36.8, 97, 98, 36.7, 98, 102, 36.9, 97, 99, 36.8, 97, 101, 36.8, 97]
}
```

### Lansia (menuju kritis)

```json
{
    "data": ["Lansia", 75, 36.5, 95, 80, 36.7, 94, 88, 36.9, 93, 95, 37.1, 91, 105, 37.4, 89]
}
```

---

## JavaScript / Fetch API

```javascript
const API_URL = "https://dalvero-sats-monitoring.hf.space";

async function predictPatient(ageGroup, vitalSigns) {
    // ageGroup = "Balita" | "Anak-anak" | "Dewasa" | "Lansia"
    // vitalSigns = [hr1, temp1, spo2, ..., hr5, temp5, spo2]

    const data = [ageGroup, ...vitalSigns];

    // Step 1: Kirim data, dapat event_id
    const response1 = await fetch(`${API_URL}/gradio_api/call/predict_manual`, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ data: data })
    });
    const { event_id } = await response1.json();

    // Step 2: Ambil hasil pakai event_id
    const response2 = await fetch(`${API_URL}/gradio_api/call/predict_manual/${event_id}`);
    const text = await response2.text();

    // Parse SSE response
    const match = text.match(/data: (.+)/);
    const [prediction, probabilities, condition, riskLevel, membaik, stabil, memburuk] = JSON.parse(match[1]);

    return { prediction, probabilities, condition, riskLevel, membaik, stabil, memburuk };
}

// Cara pakai:
const result = await predictPatient("Dewasa", [
    80, 36.7, 97,
    85, 36.8, 96,
    90, 36.9, 95,
    95, 37.0, 94,
    100, 37.2, 93
]);

console.log(result.prediction);
// "Pasien akan MEMBURUK (51%) dalam 5 menit ke depan"

console.log(result.condition);
// "WARNING"

console.log(`Membaik: ${result.membaik}%, Stabil: ${result.stabil}%, Memburuk: ${result.memburuk}%`);
// "Membaik: 19%, Stabil: 30%, Memburuk: 51%"
```

---

## React / Next.js Contoh

```jsx
import { useState } from "react";

const API_URL = "https://dalvero-sats-monitoring.hf.space";

export default function PatientMonitor() {
    const [result, setResult] = useState(null);
    const [loading, setLoading] = useState(false);
    const [ageGroup, setAgeGroup] = useState("Dewasa");

    async function handlePredict(vitalSigns) {
        setLoading(true);
        try {
            const res1 = await fetch(`${API_URL}/gradio_api/call/predict_manual`, {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ data: [ageGroup, ...vitalSigns] }),
            });
            const { event_id } = await res1.json();

            const res2 = await fetch(
                `${API_URL}/gradio_api/call/predict_manual/${event_id}`
            );
            const text = await res2.text();
            const match = text.match(/data: (.+)/);
            const [prediction, probabilities, condition, riskLevel, membaik, stabil, memburuk] =
                JSON.parse(match[1]);

            setResult({ prediction, probabilities, condition, riskLevel, membaik, stabil, memburuk });
        } catch (err) {
            console.error("Prediction failed:", err);
        }
        setLoading(false);
    }

    return (
        <div>
            <select value={ageGroup} onChange={(e) => setAgeGroup(e.target.value)}>
                <option value="Balita">Balita (1-5)</option>
                <option value="Anak-anak">Anak-anak (6-11)</option>
                <option value="Dewasa">Dewasa (12-65)</option>
                <option value="Lansia">Lansia (65++)</option>
            </select>

            <button onClick={() => handlePredict([
                80, 36.7, 97,
                85, 36.8, 96,
                90, 36.9, 95,
                95, 37.0, 94,
                100, 37.2, 93
            ])} disabled={loading}>
                {loading ? "Loading..." : "Prediksi"}
            </button>

            {result && (
                <div>
                    <h3>{result.prediction}</h3>
                    <p>Kondisi: {result.condition}</p>
                    <p>Risk: {result.riskLevel}</p>
                    <p>Membaik: {result.membaik}% | Stabil: {result.stabil}% | Memburuk: {result.memburuk}%</p>
                </div>
            )}
        </div>
    );
}
```

---

## Vue.js Contoh

```vue
<template>
  <div>
    <select v-model="ageGroup">
      <option value="Balita">Balita (1-5)</option>
      <option value="Anak-anak">Anak-anak (6-11)</option>
      <option value="Dewasa">Dewasa (12-65)</option>
      <option value="Lansia">Lansia (65++)</option>
    </select>

    <button @click="handlePredict" :disabled="loading">
      {{ loading ? 'Loading...' : 'Prediksi' }}
    </button>

    <div v-if="result">
      <h3>{{ result.prediction }}</h3>
      <p>Kondisi: {{ result.condition }}</p>
      <p>Risk: {{ result.riskLevel }}</p>
      <p>Membaik: {{ result.membaik }}% | Stabil: {{ result.stabil }}% | Memburuk: {{ result.memburuk }}%</p>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'

const API_URL = 'https://dalvero-sats-monitoring.hf.space'
const result = ref(null)
const loading = ref(false)
const ageGroup = ref('Dewasa')

async function handlePredict() {
  loading.value = true
  try {
    const vitalSigns = [80, 36.7, 97, 85, 36.8, 96, 90, 36.9, 95, 95, 37.0, 94, 100, 37.2, 93]

    const res1 = await fetch(`${API_URL}/gradio_api/call/predict_manual`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ data: [ageGroup.value, ...vitalSigns] })
    })
    const { event_id } = await res1.json()

    const res2 = await fetch(`${API_URL}/gradio_api/call/predict_manual/${event_id}`)
    const text = await res2.text()
    const match = text.match(/data: (.+)/)
    const [prediction, probabilities, condition, riskLevel, membaik, stabil, memburuk] = JSON.parse(match[1])

    result.value = { prediction, probabilities, condition, riskLevel, membaik, stabil, memburuk }
  } catch (err) {
    console.error('Prediction failed:', err)
  }
  loading.value = false
}
</script>
```

---

## Laravel Contoh

### Service Class

Buat file `app/Services/PatientMonitoringService.php`:

```php
<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class PatientMonitoringService
{
    protected string $apiUrl = "https://dalvero-sats-monitoring.hf.space";

    public function predict(string $ageGroup, array $vitalSigns): array
    {
        // Step 1: Kirim data, dapat event_id
        $response1 = Http::post("{$this->apiUrl}/gradio_api/call/predict_manual", [
            'data' => array_merge([$ageGroup], $vitalSigns),
        ]);

        $eventId = $response1->json('event_id');

        // Step 2: Ambil hasil pakai event_id
        $response2 = Http::get("{$this->apiUrl}/gradio_api/call/predict_manual/{$eventId}");

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

### Controller

```php
<?php

namespace App\Http\Controllers;

use App\Services\PatientMonitoringService;
use Illuminate\Http\Request;

class PatientController extends Controller
{
    public function predict(Request $request)
    {
        $service = new PatientMonitoringService();

        $ageGroup = $request->input('age_group'); // "Dewasa"
        $readings = $request->input('vital_signs');
        // Format: [{hr: 80, temp: 36.7, spo2: 97}, ...]

        $vitalSigns = $service->formatVitalSigns($readings);
        $result = $service->predict($ageGroup, $vitalSigns);

        return response()->json($result);
    }
}
```

### Route

```php
// routes/api.php
use App\Http\Controllers\PatientController;

Route::post('/patient/predict', [PatientController::class, 'predict']);
```

### Cara Pakai di Blade

```html
<!-- resources/views/patient/monitor.blade.php -->
<form id="predictForm">
    @csrf

    <select id="ageGroup">
        <option value="Balita">Balita (1-5)</option>
        <option value="Anak-anak">Anak-anak (6-11)</option>
        <option value="Dewasa" selected>Dewasa (12-65)</option>
        <option value="Lansia">Lansia (65++)</option>
    </select>

    <button type="submit">Prediksi</button>
</form>

<div id="result"></div>

<script>
document.getElementById('predictForm').addEventListener('submit', async (e) => {
    e.preventDefault();

    const ageGroup = document.getElementById('ageGroup').value;
    const vitalSigns = [
        {hr: 80, temp: 36.7, spo2: 97},
        {hr: 85, temp: 36.8, spo2: 96},
        {hr: 90, temp: 36.9, spo2: 95},
        {hr: 95, temp: 37.0, spo2: 94},
        {hr: 100, temp: 37.2, spo2: 93},
    ];

    const res = await fetch('/api/patient/predict', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
        },
        body: JSON.stringify({
            age_group: ageGroup,
            vital_signs: vitalSigns
        }),
    });

    const data = await res.json();
    document.getElementById('result').innerHTML = `
        <h3>${data.prediction}</h3>
        <p>Kondisi: ${data.condition}</p>
        <p>Risk: ${data.risk_level}</p>
        <p>Membaik: ${data.membaik}% | Stabil: ${data.stabil}% | Memburuk: ${data.memburuk}%</p>
    `;
});
</script>
```

### Contoh Request dari Postman ke Laravel

```
POST http://your-laravel-app.test/api/patient/predict

Body (JSON):
{
    "age_group": "Dewasa",
    "vital_signs": [
        {"hr": 80, "temp": 36.7, "spo2": 97},
        {"hr": 85, "temp": 36.8, "spo2": 96},
        {"hr": 90, "temp": 36.9, "spo2": 95},
        {"hr": 95, "temp": 37.0, "spo2": 94},
        {"hr": 100, "temp": 37.2, "spo2": 93}
    ]
}
```

---

## PHP Native (tanpa Framework)

```php
<?php
function predictPatient($ageGroup, $vitalSigns) {
    $apiUrl = "https://dalvero-sats-monitoring.hf.space";

    // Step 1: Kirim data
    $ch = curl_init("$apiUrl/gradio_api/call/predict_manual");
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
        "data" => array_merge([$ageGroup], $vitalSigns)
    ]));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response1 = json_decode(curl_exec($ch), true);
    curl_close($ch);

    $eventId = $response1["event_id"];

    // Step 2: Ambil hasil
    $ch = curl_init("$apiUrl/gradio_api/call/predict_manual/$eventId");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response2 = curl_exec($ch);
    curl_close($ch);

    preg_match('/data: (.+)/', $response2, $matches);
    $data = json_decode($matches[1], true);

    return [
        "prediction" => $data[0],
        "probabilities" => $data[1],
        "condition" => $data[2],
        "risk_level" => $data[3],
        "membaik" => $data[4],
        "stabil" => $data[5],
        "memburuk" => $data[6],
    ];
}

// Cara pakai:
$result = predictPatient("Dewasa", [
    80, 36.7, 97,
    85, 36.8, 96,
    90, 36.9, 95,
    95, 37.0, 94,
    100, 37.2, 93
]);

echo $result["prediction"];
// "Pasien akan MEMBURUK (51%) dalam 5 menit ke depan"
?>
```

---

## Data Mapping

Untuk mengirim data dari sensor/database ke API, susun array dengan urutan:

```
Index 0:     Kategori Usia → "Balita" / "Anak-anak" / "Dewasa" / "Lansia"
Index 1-3:   Menit 1 → [HR, Temp, SpO2]
Index 4-6:   Menit 2 → [HR, Temp, SpO2]
Index 7-9:   Menit 3 → [HR, Temp, SpO2]
Index 10-12: Menit 4 → [HR, Temp, SpO2]
Index 13-15: Menit 5 → [HR, Temp, SpO2]
```

**Contoh helper function:**

```javascript
function formatVitalSigns(ageGroup, readings) {
    // ageGroup = "Dewasa"
    // readings = array of {hr, temp, spo2}
    const data = [ageGroup];
    for (const r of readings) {
        data.push(r.hr, r.temp, r.spo2);
    }
    return data;
}

// Cara pakai:
const readings = [
    { hr: 80, temp: 36.7, spo2: 97 },
    { hr: 85, temp: 36.8, spo2: 96 },
    { hr: 90, temp: 36.9, spo2: 95 },
    { hr: 95, temp: 37.0, spo2: 94 },
    { hr: 100, temp: 37.2, spo2: 93 },
];

const data = formatVitalSigns("Dewasa", readings);
// ["Dewasa", 80, 36.7, 97, 85, 36.8, 96, 90, 36.9, 95, ...]
```
