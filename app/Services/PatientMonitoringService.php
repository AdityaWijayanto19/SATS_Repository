<?php

/**
 * PatientMonitoringService — Integrasi Machine Learning Hugging Face Spaces
 *
 * Service ini memanggil API prediksi kondisi pasien dari model ML yang di-host
 * di Hugging Face Spaces (Gradio). API menggunakan 2-step async pattern:
 *   1. POST data vital signs → dapat event_id
 *   2. GET hasil prediksi pakai event_id (Server-Sent Events)
 *
 * Input: 16 elemen (1 kategori usia + 5 menit × 3 vital signs: HR, Temp, SpO2)
 * Output: prediksi teks, probabilitas, kondisi (NORMAL/WARNING/CRITICAL), risk level
 *
 * Dokumentasi lengkap: API_INTEGRATION.md
 */

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class PatientMonitoringService
{
    protected string $apiUrl = "https://dalvero-sats-monitoring.hf.space";

    /**
     * Kirim data vital signs ke ML API dan ambil hasil prediksi.
     *
     * @param array $vitalSigns Array 16 elemen [ageGroup, hr1, temp1, spo21, ..., hr5, temp5, spo25]
     * @return array|null ['prediction' => string, 'probabilities' => string, 'condition' => string, 'risk_level' => string]
     */
    public function predict(array $vitalSigns): ?array
    {
        try {
            Log::info('ML API: calling predict', ['data_count' => count($vitalSigns)]);

            // Step 1: Kirim data, dapat event_id
            $response1 = Http::timeout(10)->post("{$this->apiUrl}/gradio_api/call/predict_manual", [
                'data' => $vitalSigns,
            ]);

            if (!$response1->successful()) {
                Log::error('ML API Step 1 failed', ['status' => $response1->status(), 'body' => $response1->body()]);
                return null;
            }

            $eventId = $response1->json('event_id');
            if (!$eventId) {
                Log::error('ML API: no event_id returned', ['response' => $response1->json()]);
                return null;
            }

            Log::info('ML API: got event_id', ['event_id' => $eventId]);

            // Step 2: Ambil hasil pakai event_id (SSE response)
            $response2 = Http::timeout(15)->get("{$this->apiUrl}/gradio_api/call/predict_manual/{$eventId}");

            if (!$response2->successful()) {
                Log::error('ML API Step 2 failed', ['status' => $response2->status(), 'body' => $response2->body()]);
                return null;
            }

            // Parse SSE response
            $body = $response2->body();

            // Cari baris "data: [...]" terakhir (kadang ada event: complete sebelumnya)
            if (!preg_match_all('/data: (.+)/', $body, $matches)) {
                Log::error('ML API: no data line found', ['body' => substr($body, 0, 500)]);
                return null;
            }

            // Ambil data line terakhir
            $lastDataLine = end($matches[1]);
            $data = json_decode($lastDataLine, true);

            Log::info('ML API parsed response', [
                'raw_line' => substr($lastDataLine, 0, 300),
                'decoded_type' => gettype($data),
                'decoded_keys' => is_array($data) ? array_keys($data) : null,
            ]);

            // Response bisa indexed array [pred, prob, cond, risk, membaik, stabil, memburuk] atau object
            if (is_array($data) && isset($data[0])) {
                // Indexed array format — 7 elemen sesuai API_INTEGRATION.md
                return [
                    'prediction'    => $data[0],
                    'probabilities' => $data[1] ?? '',
                    'condition'     => $data[2] ?? 'NORMAL',
                    'risk_level'    => $data[3] ?? 'Low Risk',
                    'membaik'       => is_numeric($data[4] ?? null) ? (int) $data[4] : null,
                    'stabil'        => is_numeric($data[5] ?? null) ? (int) $data[5] : null,
                    'memburuk'      => is_numeric($data[6] ?? null) ? (int) $data[6] : null,
                ];
            } elseif (is_array($data) && (isset($data['prediction']) || isset($data['data']))) {
                // Object format — mungkin ada key 'data' yang berisi array
                $inner = $data['data'] ?? $data;
                if (is_array($inner) && isset($inner[0])) {
                    return [
                        'prediction'    => $inner[0],
                        'probabilities' => $inner[1] ?? '',
                        'condition'     => $inner[2] ?? 'NORMAL',
                        'risk_level'    => $inner[3] ?? 'Low Risk',
                        'membaik'       => is_numeric($inner[4] ?? null) ? (int) $inner[4] : null,
                        'stabil'        => is_numeric($inner[5] ?? null) ? (int) $inner[5] : null,
                        'memburuk'      => is_numeric($inner[6] ?? null) ? (int) $inner[6] : null,
                    ];
                }
                return [
                    'prediction'    => $inner['prediction'] ?? json_encode($inner),
                    'probabilities' => $inner['probabilities'] ?? '',
                    'condition'     => $inner['condition'] ?? 'NORMAL',
                    'risk_level'    => $inner['risk_level'] ?? 'Low Risk',
                    'membaik'       => is_numeric($inner['membaik'] ?? null) ? (int) $inner['membaik'] : null,
                    'stabil'        => is_numeric($inner['stabil'] ?? null) ? (int) $inner['stabil'] : null,
                    'memburuk'      => is_numeric($inner['memburuk'] ?? null) ? (int) $inner['memburuk'] : null,
                ];
            }

            Log::error('ML API: unrecognized response format', ['data' => $data]);
            return null;

            return [
                'prediction'    => $data[0],  // "Pasien akan MEMBURUK (63%) dalam 5 menit ke depan"
                'probabilities' => $data[1],  // Detail probabilitas
                'condition'     => $data[2],  // "NORMAL" / "WARNING" / "CRITICAL"
                'risk_level'    => $data[3],  // "Low Risk" / "Medium Risk" / "High Risk"
            ];
        } catch (\Exception $e) {
            Log::error('ML API exception', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Ambil 5 data sensor terakhir dari device dan format untuk ML API.
     *
     * @param string $deviceId
     * @return array|null Array 16 elemen [ageGroup, hr1, temp1, spo2, ..., hr5, temp5, spo25] atau null jika data kosong
     */
    public function getVitalSignsForDevice(string $deviceId): ?array
    {
        // Ambil 5 data terakhir (urut ASC untuk urutan waktu yang benar)
        $readings = \App\Models\SensorData::where('device_id', $deviceId)
            ->orderByDesc('created_at')
            ->limit(5)
            ->get()
            ->reverse()
            ->values();

        if ($readings->isEmpty()) {
            return null;
        }

        // Ambil kategori_usia dari data sensor terbaru
        $ageGroup = $readings->last()->kategori_usia ?? 'Dewasa';

        // Jika kurang dari 5, duplikasi data pertama untuk mengisi
        while ($readings->count() < 5) {
            $readings->prepend($readings->first());
        }

        // Format ke flat array: [ageGroup, HR, Temp, SpO2] × 5 = 1 string + 15 angka = 16 elemen
        // Urutan sesuai API_INTEGRATION.md: kategori_usia, HR, Temp, SpO2
        $data = [$ageGroup];
        foreach ($readings as $r) {
            $data[] = $r->heart_rate ?? 80;       // HR
            $data[] = $r->temperature ?? 36.5;    // Temp
            $data[] = $r->spo2 ?? 97;             // SpO2
        }

        return $data;
    }

    /**
     * Ambil prediksi untuk device dengan caching (hindari panggil API berulang).
     * Cache 2 menit karena prediksi tidak perlu real-time detik.
     *
     * @param string $deviceId
     * @return array|null
     */
    public function getPredictionForDevice(string $deviceId): ?array
    {
        $cacheKey = "ml_prediction_{$deviceId}";

        return Cache::remember($cacheKey, 120, function () use ($deviceId) {
            $vitalSigns = $this->getVitalSignsForDevice($deviceId);
            if (!$vitalSigns) {
                return null;
            }
            return $this->predict($vitalSigns);
        });
    }
}
