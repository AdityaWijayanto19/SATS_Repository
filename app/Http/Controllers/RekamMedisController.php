<?php

namespace App\Http\Controllers;

use App\Models\MonitoringSession;
use App\Services\ReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class RekamMedisController extends Controller
{
    public function __construct(
        protected ReportService $reportService
    ) {}

    /**
     * Daftar rekam medis milik dokter yang login.
     */
    public function index()
    {
        $user = Auth::user();
        $rekamMedis = $this->reportService->getRekamMedisList($user->id);

        return view('pages.dokter.rekam-medis', compact('rekamMedis'));
    }

    /**
     * Detail satu rekam medis.
     */
    public function show($id)
    {
        $user = Auth::user();

        $session = MonitoringSession::where('id', $id)
            ->where('dokter_id', $user->id)
            ->where('status', 'completed')
            ->with(['patient', 'creator', 'device'])
            ->firstOrFail();

        $vitalSigns = ['heart_rate', 'spo2', 'temperature'];
        $chartData = $this->reportService->getHistoryForChart($session->id, $vitalSigns);
        $latestReading = $this->reportService->getLatestReading($session->id);
        $stats = $this->reportService->getSessionStats($session->id);

        return view('pages.dokter.rekam-medis-show', compact(
            'session', 'chartData', 'latestReading', 'stats', 'vitalSigns'
        ));
    }

    /**
     * Generate & stream PDF rekam medis.
     */
    public function pdf($id, Request $request)
    {
        $user = Auth::user();

        $session = MonitoringSession::where('id', $id)
            ->where('dokter_id', $user->id)
            ->where('status', 'completed')
            ->with(['patient', 'creator', 'device'])
            ->firstOrFail();

        // Filter rentang waktu (konversi Asia/Jakarta → UTC untuk query DB)
        $dari = $request->get('dari');
        $sampai = $request->get('sampai');

        $startTime = null;
        $endTime = null;

        if ($dari && $session->started_at) {
            $startTime = Carbon::createFromFormat('Y-m-d H:i', $session->started_at->toDateString() . ' ' . $dari, 'Asia/Jakarta')->setTimezone('UTC');
        }
        if ($sampai && $session->started_at) {
            $endTime = Carbon::createFromFormat('Y-m-d H:i', $session->started_at->toDateString() . ' ' . $sampai, 'Asia/Jakarta')->setTimezone('UTC');
        }

        $vitalSigns = ['heart_rate', 'spo2', 'temperature'];
        $chartData = $this->reportService->getHistoryForChart($session->id, $vitalSigns, $startTime, $endTime);
        $latestReading = $this->reportService->getLatestReading($session->id);
        $stats = $this->reportService->getSessionStats($session->id, $startTime, $endTime);

        // Load sensor readings dengan filter waktu untuk tabel
        $session->load(['sensorReadings' => function ($q) use ($startTime, $endTime) {
            if ($startTime) {
                $q->where('recorded_at', '>=', $startTime);
            }
            if ($endTime) {
                $q->where('recorded_at', '<=', $endTime);
            }
            $q->orderBy('recorded_at', 'asc')->limit(100);
        }]);

        // Generate chart via QuickChart.io
        $grafikBase64 = $this->generateChartBase64($chartData, $vitalSigns);

        $pdf = Pdf::loadView('pages.dokter.rekam-medis-pdf', compact(
            'session', 'chartData', 'latestReading', 'stats', 'grafikBase64', 'vitalSigns', 'dari', 'sampai'
        ))->setPaper('a4', 'portrait');

        $namaFile = 'RekamMedis_' . ($session->medical_record_number ?? 'Unknown') . '_' . now()->format('Ymd') . '.pdf';

        return $pdf->stream($namaFile);
    }

    /**
     * Hitung jumlah readings dalam rentang waktu (AJAX).
     */
    public function countReadings($id, Request $request)
    {
        $user = Auth::user();

        $session = MonitoringSession::where('id', $id)
            ->where('dokter_id', $user->id)
            ->where('status', 'completed')
            ->firstOrFail();

        $dari = $request->get('dari');
        $sampai = $request->get('sampai');

        $query = \App\Models\SensorReading::where('session_id', $session->id);

        if ($dari && $session->started_at) {
            $startTime = Carbon::createFromFormat('Y-m-d H:i', $session->started_at->toDateString() . ' ' . $dari, 'Asia/Jakarta')->setTimezone('UTC');
            $query->where('recorded_at', '>=', $startTime);
        }
        if ($sampai && $session->started_at) {
            $endTime = Carbon::createFromFormat('Y-m-d H:i', $session->started_at->toDateString() . ' ' . $sampai, 'Asia/Jakarta')->setTimezone('UTC');
            $query->where('recorded_at', '<=', $endTime);
        }

        return response()->json(['count' => $query->count()]);
    }

    /**
     * Generate chart sebagai base64 PNG via QuickChart.io.
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
