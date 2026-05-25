<?php

namespace App\Http\Controllers;

use App\Models\MonitoringSession;
use App\Models\NakesDeviceConfig;
use App\Services\MonitoringSessionService;
use App\Services\ReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class LaporanController extends Controller
{
    public function __construct(
        protected ReportService $reportService,
        protected MonitoringSessionService $sessionService
    ) {}

    /**
     * Halaman preview laporan (tampil di browser).
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $sessionId = $request->get('session_id');
        $vitalSigns = $request->get('vital_signs', ['heart_rate', 'spo2', 'temperature']);

        // Auto-detect device based on role
        $deviceId = null;
        $monitoredDevices = collect();

        if ($user->role === 'nakes') {
            // Nakes: get device from their config
            $nakesConfig = NakesDeviceConfig::where('user_id', $user->id)->first();
            $deviceId = $nakesConfig?->device_id;
        } elseif ($user->role === 'dokter') {
            // Dokter: get devices they are monitoring
            $monitoredDevices = \App\Models\DeviceMonitoring::where('dokter_id', $user->id)
                ->with('device')
                ->get()
                ->pluck('device');

            // Use first device or selected device
            $deviceId = $request->get('device_id') ?? $monitoredDevices->first()?->device_id;
        }

        // Get completed sessions for this device
        $sessions = collect();
        if ($deviceId) {
            $sessions = $this->sessionService->getCompletedSessionsForDevice($deviceId);
        }

        // Get report data if session selected
        $session = null;
        $patient = null;
        $chartData = null;
        $latestReading = null;
        $stats = null;

        if ($sessionId) {
            $session = $this->reportService->getReportData($sessionId, $vitalSigns);
            $patient = $session->patient;
            $chartData = $this->reportService->getHistoryForChart($sessionId, $vitalSigns);
            $latestReading = $this->reportService->getLatestReading($sessionId);
            $stats = $this->reportService->getSessionStats($sessionId);
        }

        // ML prediction (TODO: replace with real ML endpoint)
        $prediksi = (object) [
            'risk_level' => 'warning',
            'risk_percent' => 20,
            'timeframe_minutes' => 15,
            'message' => 'Kondisi pasien berpotensi memburuk 20% dalam 15 menit ke depan berdasarkan tren Heart Rate dan SpO2.',
        ];

        $role = $user->role;
        $viewFolder = $role === 'dokter' ? 'pages.dokter' : 'pages.nakes';

        return view($viewFolder . '.laporan', compact(
            'session', 'patient', 'sessions', 'chartData',
            'latestReading', 'stats', 'deviceId', 'sessionId',
            'vitalSigns', 'prediksi', 'monitoredDevices'
        ));
    }

    /**
     * API: Get session data as JSON for AJAX updates.
     */
    public function sessionData(Request $request)
    {
        $sessionId = $request->get('session_id');
        $vitalSigns = $request->get('vital_signs', ['heart_rate', 'spo2', 'temperature']);

        if (!$sessionId) {
            return response()->json(['error' => 'Session ID required'], 400);
        }

        $session = $this->reportService->getReportData($sessionId, $vitalSigns);
        $patient = $session->patient;
        $chartData = $this->reportService->getHistoryForChart($sessionId, $vitalSigns);
        $latestReading = $this->reportService->getLatestReading($sessionId);
        $stats = $this->reportService->getSessionStats($sessionId);

        // Render partials
        $patientHtml = view('pages.nakes.partials._laporan-patient', compact('session', 'patient'))->render();
        $contentHtml = view('pages.nakes.partials._laporan-content', compact('session', 'patient', 'chartData', 'latestReading', 'stats', 'vitalSigns'))->render();
        $sidebarHtml = view('pages.nakes.partials._laporan-sidebar', compact('session', 'vitalSigns'))->render();

        return response()->json([
            'patient' => $patient,
            'chartData' => $chartData,
            'latestReading' => $latestReading,
            'stats' => $stats,
            'patientHtml' => $patientHtml,
            'contentHtml' => $contentHtml,
            'sidebarHtml' => $sidebarHtml,
            'sessionInfo' => [
                'id' => $session->id,
                'medical_record_number' => $session->medical_record_number,
                'started_at' => $session->started_at?->setTimezone('Asia/Jakarta')->format('d M Y, H:i'),
                'ended_at' => $session->ended_at?->setTimezone('Asia/Jakarta')->format('d M Y, H:i'),
                'total_readings' => $session->total_readings,
            ],
        ]);
    }

    /**
     * Generate & stream PDF laporan.
     */
    public function pdf(Request $request)
    {
        $sessionId = $request->get('session_id');
        $vitalSigns = $request->get('vital_signs', ['heart_rate', 'spo2', 'temperature']);

        if (!$sessionId) {
            abort(400, 'Session ID diperlukan untuk generate PDF.');
        }

        $session = $this->reportService->getReportData($sessionId, $vitalSigns);
        $patient = $session->patient;
        $chartData = $this->reportService->getHistoryForChart($sessionId, $vitalSigns);
        $latestReading = $this->reportService->getLatestReading($sessionId);
        $stats = $this->reportService->getSessionStats($sessionId);

        // ML prediction (TODO: replace with real ML endpoint)
        $prediksi = (object) [
            'risk_level' => 'warning',
            'risk_percent' => 20,
            'timeframe_minutes' => 15,
            'message' => 'Kondisi pasien berpotensi memburuk 20% dalam 15 menit ke depan berdasarkan tren Heart Rate dan SpO2.',
        ];

        // Generate chart
        $grafikBase64 = $this->generateChartBase64($chartData, $vitalSigns);

        $role = auth()->user()->role;
        $viewFolder = $role === 'dokter' ? 'pages.dokter' : 'pages.nakes';

        $pdf = Pdf::loadView($viewFolder . '/laporan-pdf', compact(
            'session', 'patient', 'chartData', 'latestReading',
            'stats', 'grafikBase64', 'vitalSigns', 'prediksi'
        ))->setPaper('a4', 'portrait');

        $namaFile = 'Laporan_' . ($session->medical_record_number ?? 'Unknown') . '_' . now()->format('Ymd') . '.pdf';

        return $pdf->stream($namaFile);
    }

    /**
     * Generate chart as base64 PNG via QuickChart.io.
     */
    private function generateChartBase64(array $chartData, array $vitalSigns): ?string
    {
        $colors = [
            'heart_rate' => ['rgb(220,38,38)', 'Heart Rate (bpm)'],
            'spo2' => ['rgb(59,130,246)', 'SpO2 (%)'],
            'temperature' => ['rgb(234,179,8)', 'Suhu (°C)'],
        ];

        $datasets = [];
        foreach ($vitalSigns as $sign) {
            if (isset($chartData['datasets'][$sign]) && isset($colors[$sign])) {
                $datasets[] = [
                    'label' => $colors[$sign][1],
                    'data' => $chartData['datasets'][$sign],
                    'borderColor' => $colors[$sign][0],
                    'borderWidth' => 2,
                    'pointRadius' => 2,
                    'tension' => 0.4,
                    'fill' => false,
                ];
            }
        }

        if (empty($datasets)) {
            return null;
        }

        $chartConfig = [
            'type' => 'line',
            'data' => [
                'labels' => $chartData['labels'],
                'datasets' => $datasets,
            ],
            'options' => [
                'plugins' => ['legend' => ['display' => true]],
                'scales' => [
                    'y' => ['title' => ['display' => true, 'text' => 'Nilai']],
                ],
            ],
        ];

        $url = 'https://quickchart.io/chart?c=' . urlencode(json_encode($chartConfig)) . '&w=600&h=250&bkg=white';

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
