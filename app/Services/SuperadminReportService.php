<?php

namespace App\Services;

use Barryvdh\DomPDF\Facade\Pdf;

class SuperadminReportService
{
    /**
     * Data laporan superadmin.
     */
    public function getReportData(string $dari, string $sampai, string $ambulans = ''): array
    {
        // TODO: Ganti dengan data real dari database
        $totalPenggunaanAlat = 100;
        $totalAktivitasUser = 20;
        $totalLaporanTersimpan = 58;

        $dataSensor = $this->getDummySensorData();

        if ($ambulans) {
            $dataSensor = $dataSensor->where('ambulans', $ambulans);
        }

        $daftarAmbulans = ['Ambulans Pelita', 'Ambulans Sehat', 'Ambulans Cepat', 'Ambulans Sentosa'];

        return compact(
            'dari', 'sampai', 'ambulans',
            'totalPenggunaanAlat', 'totalAktivitasUser', 'totalLaporanTersimpan',
            'dataSensor', 'daftarAmbulans'
        );
    }

    /**
     * Generate & stream PDF laporan superadmin.
     */
    public function generatePdf(string $dari, string $sampai, string $ambulans = '')
    {
        // TODO: Ganti dengan data real dari database
        $dataSensor = $this->getDummySensorData();

        if ($ambulans) {
            $dataSensor = $dataSensor->where('ambulans', $ambulans);
        }

        $grafikBase64 = $this->generateChartBase64($dataSensor);

        $pdf = Pdf::loadView('pages.superadmin.laporan-pdf', compact(
            'dari', 'sampai', 'ambulans', 'dataSensor', 'grafikBase64'
        ))->setPaper('a4', 'landscape');

        $filterNama = $ambulans ? str_replace(' ', '_', $ambulans) : 'Semua';
        $namaFile = 'Laporan_Sensor_' . $filterNama . '_' . now()->format('Ymd') . '.pdf';

        return $pdf->stream($namaFile);
    }

    /**
     * Generate grafik vital sign sebagai base64 PNG via QuickChart.io.
     */
    public function generateChartBase64($dataSensor): ?string
    {
        $labels = $dataSensor->pluck('waktu')->toArray();
        $heartRates = $dataSensor->pluck('heart_rate')->toArray();
        $spo2Values = $dataSensor->pluck('spo2')->toArray();

        $chartConfig = [
            'type' => 'line',
            'data' => [
                'labels' => $labels,
                'datasets' => [
                    [
                        'label' => 'Heart Rate (bpm)',
                        'data' => $heartRates,
                        'borderColor' => 'rgb(239,68,68)',
                        'borderWidth' => 2,
                        'pointRadius' => 3,
                        'tension' => 0.4,
                        'fill' => false,
                        'yAxisID' => 'y',
                    ],
                    [
                        'label' => 'SpO2 (%)',
                        'data' => $spo2Values,
                        'borderColor' => 'rgb(59,130,246)',
                        'borderWidth' => 2,
                        'pointRadius' => 3,
                        'tension' => 0.4,
                        'fill' => false,
                        'yAxisID' => 'y1',
                    ],
                ],
            ],
            'options' => [
                'plugins' => ['legend' => ['display' => true]],
                'scales' => [
                    'y' => [
                        'type' => 'linear',
                        'position' => 'left',
                        'title' => ['display' => true, 'text' => 'Heart Rate (bpm)'],
                    ],
                    'y1' => [
                        'type' => 'linear',
                        'position' => 'right',
                        'min' => 80,
                        'max' => 100,
                        'title' => ['display' => true, 'text' => 'SpO2 (%)'],
                        'grid' => ['drawOnChartArea' => false],
                    ],
                ],
            ],
        ];

        $url = 'https://quickchart.io/chart?c=' . urlencode(json_encode($chartConfig)) . '&w=700&h=250&bkg=white';

        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            $imageData = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);

            if ($httpCode === 200 && $imageData && !$error) {
                return base64_encode($imageData);
            }

            \Log::warning("QuickChart fetch failed: HTTP {$httpCode}, Error: {$error}");
            return null;
        } catch (\Exception $e) {
            \Log::warning('QuickChart exception: ' . $e->getMessage());
            return null;
        }
    }

    private function getDummySensorData()
    {
        return collect([
            (object)['waktu' => '08:00:15', 'device' => 'SATS Wearable-1', 'ambulans' => 'Ambulans Pelita', 'heart_rate' => 72, 'spo2' => 98, 'temperature' => 36.5, 'klasifikasi' => 'Normal'],
            (object)['waktu' => '08:05:22', 'device' => 'SATS Wearable-1', 'ambulans' => 'Ambulans Pelita', 'heart_rate' => 75, 'spo2' => 97, 'temperature' => 36.6, 'klasifikasi' => 'Normal'],
            (object)['waktu' => '08:10:30', 'device' => 'SATS Wearable-2', 'ambulans' => 'Ambulans Pelita', 'heart_rate' => 80, 'spo2' => 96, 'temperature' => 36.7, 'klasifikasi' => 'Normal'],
            (object)['waktu' => '08:15:45', 'device' => 'SATS Wearable-1', 'ambulans' => 'Ambulans Pelita', 'heart_rate' => 95, 'spo2' => 94, 'temperature' => 37.0, 'klasifikasi' => 'Warning'],
            (object)['waktu' => '08:20:10', 'device' => 'SATS Wearable-3', 'ambulans' => 'Ambulans Sehat', 'heart_rate' => 68, 'spo2' => 99, 'temperature' => 36.4, 'klasifikasi' => 'Normal'],
            (object)['waktu' => '08:25:33', 'device' => 'SATS Wearable-1', 'ambulans' => 'Ambulans Pelita', 'heart_rate' => 110, 'spo2' => 91, 'temperature' => 37.5, 'klasifikasi' => 'Warning'],
            (object)['waktu' => '08:30:05', 'device' => 'SATS Wearable-2', 'ambulans' => 'Ambulans Pelita', 'heart_rate' => 78, 'spo2' => 97, 'temperature' => 36.5, 'klasifikasi' => 'Normal'],
            (object)['waktu' => '08:35:18', 'device' => 'SATS Wearable-1', 'ambulans' => 'Ambulans Pelita', 'heart_rate' => 125, 'spo2' => 88, 'temperature' => 38.1, 'klasifikasi' => 'Critical'],
            (object)['waktu' => '08:40:42', 'device' => 'SATS Wearable-3', 'ambulans' => 'Ambulans Sehat', 'heart_rate' => 70, 'spo2' => 98, 'temperature' => 36.5, 'klasifikasi' => 'Normal'],
            (object)['waktu' => '08:45:55', 'device' => 'SATS Wearable-1', 'ambulans' => 'Ambulans Pelita', 'heart_rate' => 100, 'spo2' => 93, 'temperature' => 37.2, 'klasifikasi' => 'Warning'],
            (object)['waktu' => '08:50:20', 'device' => 'SATS Wearable-2', 'ambulans' => 'Ambulans Pelita', 'heart_rate' => 74, 'spo2' => 98, 'temperature' => 36.6, 'klasifikasi' => 'Normal'],
            (object)['waktu' => '08:55:48', 'device' => 'SATS Wearable-1', 'ambulans' => 'Ambulans Pelita', 'heart_rate' => 85, 'spo2' => 95, 'temperature' => 36.8, 'klasifikasi' => 'Normal'],
        ]);
    }
}
