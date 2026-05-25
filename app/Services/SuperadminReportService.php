<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\Devices;
use App\Models\FailedSensorData;
use App\Models\MonitoringSession;
use App\Models\Patient;
use App\Models\SensorReading;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class SuperadminReportService
{
    /**
     * Mapping kategori filter ke event types.
     */
    private const KATEGORI_MAP = [
        'autentikasi' => ['user.login', 'user.logout', 'password.reset_request', 'password.reset_success'],
        'device' => ['device.online', 'device.offline', 'device.added', 'device.deleted'],
        'monitoring' => ['monitoring.started', 'monitoring.stopped', 'monitoring.completed'],
        'user' => ['user.added', 'user.deleted'],
        'pasien' => ['patient.registered', 'patient.warning', 'patient.critical'],
        'instruksi' => ['instruction.sent', 'instruction.completed'],
    ];

    /**
     * Data operasional: stat cards, tren, device utilization, distribusi kondisi.
     */
    public function getOperasionalData(string $dari, string $sampai, ?string $deviceId = null): array
    {
        $dariCarbon = Carbon::parse($dari)->startOfDay();
        $sampaiCarbon = Carbon::parse($sampai)->endOfDay();

        // Stat cards
        $totalSesi = MonitoringSession::whereBetween('started_at', [$dariCarbon, $sampaiCarbon])->count();
        $totalPasien = Patient::whereHas('monitoringSessions', function ($q) use ($dariCarbon, $sampaiCarbon) {
            $q->whereBetween('started_at', [$dariCarbon, $sampaiCarbon]);
        })->count();
        $deviceAktif = Devices::where('status', 'online')->count();
        $dataGagal = FailedSensorData::whereBetween('failed_at', [$dariCarbon, $sampaiCarbon])->count();

        // Tren sesi per hari
        $trenPerHari = MonitoringSession::whereBetween('started_at', [$dariCarbon, $sampaiCarbon])
            ->selectRaw('DATE(started_at) as tanggal, COUNT(*) as jumlah')
            ->groupBy('tanggal')
            ->orderBy('tanggal')
            ->get();

        // Device utilization
        $deviceUtilization = MonitoringSession::whereBetween('started_at', [$dariCarbon, $sampaiCarbon])
            ->when($deviceId, fn($q) => $q->where('device_id', $deviceId))
            ->selectRaw('device_id, COUNT(*) as jumlah_sesi, AVG(TIMESTAMPDIFF(MINUTE, started_at, COALESCE(ended_at, NOW()))) as durasi_avg')
            ->groupBy('device_id')
            ->with('device')
            ->get();

        // Distribusi kondisi dari sensor readings
        $distribusiKondisi = SensorReading::whereHas('session', function ($q) use ($dariCarbon, $sampaiCarbon, $deviceId) {
            $q->whereBetween('started_at', [$dariCarbon, $sampaiCarbon]);
            if ($deviceId) {
                $q->where('device_id', $deviceId);
            }
        })
            ->selectRaw('status, COUNT(*) as jumlah')
            ->groupBy('status')
            ->get()
            ->pluck('jumlah', 'status');

        return [
            'totalSesi' => $totalSesi,
            'totalPasien' => $totalPasien,
            'deviceAktif' => $deviceAktif,
            'dataGagal' => $dataGagal,
            'trenPerHari' => $trenPerHari,
            'deviceUtilization' => $deviceUtilization,
            'distribusiKondisi' => $distribusiKondisi,
            'dari' => $dari,
            'sampai' => $sampai,
            'deviceId' => $deviceId,
        ];
    }

    /**
     * Data audit: stat cards + log aktivitas.
     */
    public function getAuditData(string $dari, string $sampai, ?string $kategori = null, ?string $deviceId = null): array
    {
        $dariCarbon = Carbon::parse($dari)->startOfDay();
        $sampaiCarbon = Carbon::parse($sampai)->endOfDay();

        // Stat cards
        $totalAktivitas = ActivityLog::whereBetween('created_at', [$dariCarbon, $sampaiCarbon])->count();
        $loginHariIni = ActivityLog::where('type', 'user.login')
            ->whereDate('created_at', today())
            ->count();
        $perubahanUser = ActivityLog::whereIn('type', ['user.added', 'user.deleted'])
            ->whereBetween('created_at', [$dariCarbon, $sampaiCarbon])
            ->count();
        $perubahanDevice = ActivityLog::whereIn('type', ['device.added', 'device.deleted'])
            ->whereBetween('created_at', [$dariCarbon, $sampaiCarbon])
            ->count();

        // Log list dengan filter
        $logs = ActivityLog::whereBetween('created_at', [$dariCarbon, $sampaiCarbon])
            ->when($kategori && isset(self::KATEGORI_MAP[$kategori]), function ($q) use ($kategori) {
                $q->whereIn('type', self::KATEGORI_MAP[$kategori]);
            })
            ->when($deviceId, function ($q) use ($deviceId) {
                $q->where('device_id', $deviceId);
            })
            ->orderByDesc('created_at')
            ->paginate(50);

        return [
            'totalAktivitas' => $totalAktivitas,
            'loginHariIni' => $loginHariIni,
            'perubahanUser' => $perubahanUser,
            'perubahanDevice' => $perubahanDevice,
            'logs' => $logs,
            'dari' => $dari,
            'sampai' => $sampai,
            'kategori' => $kategori,
            'deviceId' => $deviceId,
        ];
    }

    /**
     * Daftar semua device untuk filter dropdown.
     */
    public function getAllDevices(): Collection
    {
        return Devices::select('device_id')->orderBy('device_id')->get();
    }

    /**
     * Daftar kategori untuk filter dropdown.
     */
    public function getKategoriList(): array
    {
        return [
            '' => 'Semua Kategori',
            'autentikasi' => 'Autentikasi',
            'device' => 'Device',
            'monitoring' => 'Monitoring',
            'user' => 'User',
            'pasien' => 'Pasien',
            'instruksi' => 'Instruksi',
        ];
    }

    /**
     * Generate & stream PDF laporan superadmin.
     */
    public function generatePdf(string $dari, string $sampai, string $exportType = 'both', ?string $deviceId = null, ?string $kategori = null)
    {
        $operasionalData = null;
        $auditData = null;
        $grafikBase64 = null;

        if (in_array($exportType, ['operasional', 'both'])) {
            $operasionalData = $this->getOperasionalData($dari, $sampai, $deviceId);
            $grafikBase64 = $this->generateTrenChartBase64($operasionalData['trenPerHari']);
        }

        if (in_array($exportType, ['audit', 'both'])) {
            $auditData = $this->getAuditData($dari, $sampai, $kategori, $deviceId);
            // Limit logs for PDF (gunakan query yang sudah terfilter)
            $dariCarbon = Carbon::parse($dari)->startOfDay();
            $sampaiCarbon = Carbon::parse($sampai)->endOfDay();
            $logQuery = ActivityLog::whereBetween('created_at', [$dariCarbon, $sampaiCarbon]);
            if ($kategori && isset(self::KATEGORI_MAP[$kategori])) {
                $logQuery->whereIn('type', self::KATEGORI_MAP[$kategori]);
            }
            if ($deviceId) {
                $logQuery->where('device_id', $deviceId);
            }
            $auditData['logs'] = $logQuery->orderByDesc('created_at')->limit(50)->get();
        }

        $pdf = Pdf::loadView('pages.superadmin.laporan-pdf', compact(
            'operasionalData', 'auditData', 'grafikBase64', 'dari', 'sampai', 'exportType'
        ))->setPaper('a4', 'landscape');

        $namaFile = 'Laporan_Superadmin_' . ucfirst($exportType) . '_' . now()->format('Ymd') . '.pdf';

        return $pdf->stream($namaFile);
    }

    /**
     * Generate chart tren sesi per hari sebagai base64 PNG via QuickChart.io.
     */
    private function generateTrenChartBase64(Collection $trenPerHari): ?string
    {
        if ($trenPerHari->isEmpty()) {
            return null;
        }

        $labels = $trenPerHari->pluck('tanggal')->toArray();
        $data = $trenPerHari->pluck('jumlah')->toArray();

        $chartConfig = [
            'type' => 'line',
            'data' => [
                'labels' => $labels,
                'datasets' => [[
                    'label' => 'Jumlah Sesi',
                    'data' => $data,
                    'borderColor' => 'rgb(0,62,48)',
                    'backgroundColor' => 'rgba(0,62,48,0.1)',
                    'borderWidth' => 2,
                    'pointRadius' => 4,
                    'tension' => 0.3,
                    'fill' => true,
                ]],
            ],
            'options' => [
                'plugins' => ['legend' => ['display' => false]],
                'scales' => [
                    'y' => [
                        'beginAtZero' => true,
                        'title' => ['display' => true, 'text' => 'Jumlah Sesi'],
                        'ticks' => ['stepSize' => 1],
                    ],
                ],
            ],
        ];

        return $this->fetchQuickChart($chartConfig, 700, 250);
    }

    /**
     * Fetch chart image dari QuickChart.io, return base64 atau null.
     */
    private function fetchQuickChart(array $chartConfig, int $width = 600, int $height = 250): ?string
    {
        $url = 'https://quickchart.io/chart?c=' . urlencode(json_encode($chartConfig)) . "&w={$width}&h={$height}&bkg=white";

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
}
