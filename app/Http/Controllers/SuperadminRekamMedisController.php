<?php

namespace App\Http\Controllers;

use App\Models\MonitoringSession;
use App\Models\SensorReading;
use App\Models\User;
use App\Services\ReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SuperadminRekamMedisController extends Controller
{
    public function __construct(
        protected ReportService $reportService
    ) {}

    /**
     * Daftar semua rekam medis (completed sessions) dengan search, filter, pagination.
     */
    public function index(Request $request)
    {
        $search = $request->get('search');
        $filterDokter = $request->get('dokter_id');
        $filterNakes = $request->get('nakes_id');

        $query = MonitoringSession::where('status', 'completed')
            ->with(['patient', 'creator', 'device', 'dokter'])
            ->orderByDesc('ended_at');

        // Search: nama pasien atau No. RM
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('medical_record_number', 'like', "%{$search}%")
                  ->orWhereHas('patient', fn($pq) => $pq->where('nama', 'like', "%{$search}%"));
            });
        }

        // Filter by dokter
        if ($filterDokter) {
            $query->where('dokter_id', $filterDokter);
        }

        // Filter by nakes (creator)
        if ($filterNakes) {
            $query->where('created_by', $filterNakes);
        }

        $rekamMedis = $query->paginate(15)->withQueryString();

        // Hitung stats untuk setiap session
        $rekamMedis->getCollection()->transform(function ($session) {
            return [
                'session' => $session,
                'stats' => $this->reportService->getSessionStats($session->id),
            ];
        });

        // Data untuk filter dropdown
        $dokters = User::where('role', 'dokter')->select('id', 'name')->orderBy('name')->get();
        $nakesList = User::where('role', 'nakes')->select('id', 'name')->orderBy('name')->get();

        return view('pages.superadmin.rekam-medis', compact(
            'rekamMedis', 'dokters', 'nakesList', 'search', 'filterDokter', 'filterNakes'
        ));
    }

    /**
     * Detail satu rekam medis.
     */
    public function show($id)
    {
        $session = MonitoringSession::where('id', $id)
            ->where('status', 'completed')
            ->with(['patient', 'creator', 'device', 'dokter'])
            ->firstOrFail();

        $vitalSigns = ['heart_rate', 'spo2', 'temperature'];
        $chartData = $this->reportService->getHistoryForChart($session->id, $vitalSigns);
        $latestReading = $this->reportService->getLatestReading($session->id);
        $stats = $this->reportService->getSessionStats($session->id);

        return view('pages.superadmin.rekam-medis-show', compact(
            'session', 'chartData', 'latestReading', 'stats', 'vitalSigns'
        ));
    }

    /**
     * Hapus rekam medis (session + sensor readings + patient jika tidak ada session lain).
     */
    public function destroy($id)
    {
        $session = MonitoringSession::where('id', $id)
            ->where('status', 'completed')
            ->with('patient')
            ->firstOrFail();

        $rmNumber = $session->medical_record_number;

        return DB::transaction(function () use ($session, $rmNumber) {
            // Hapus sensor readings
            SensorReading::where('session_id', $session->id)->delete();

            // Cek apakah patient punya session lain
            $patient = $session->patient;
            $otherSessions = MonitoringSession::where('patient_id', $patient?->id)
                ->where('id', '!=', $session->id)
                ->count();

            // Hapus session
            $session->delete();

            // Hapus patient jika tidak ada session lain
            if ($patient && $otherSessions === 0) {
                $patient->delete();
            }

            Log::info("Rekam medis deleted by superadmin", [
                'medical_record_number' => $rmNumber,
                'deleted_by' => auth()->id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => "Rekam medis {$rmNumber} berhasil dihapus.",
            ]);
        });
    }

    /**
     * Generate & stream PDF rekam medis.
     */
    public function pdf($id, Request $request)
    {
        $session = MonitoringSession::where('id', $id)
            ->where('status', 'completed')
            ->with(['patient', 'creator', 'device', 'dokter'])
            ->firstOrFail();

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

        $session->load(['sensorReadings' => function ($q) use ($startTime, $endTime) {
            if ($startTime) {
                $q->where('recorded_at', '>=', $startTime);
            }
            if ($endTime) {
                $q->where('recorded_at', '<=', $endTime);
            }
            $q->orderBy('recorded_at', 'asc')->limit(100);
        }]);

        $grafikBase64 = $this->generateChartBase64($chartData, $vitalSigns);

        $pdf = Pdf::loadView('pages.superadmin.rekam-medis-pdf', compact(
            'session', 'chartData', 'latestReading', 'stats', 'grafikBase64', 'vitalSigns', 'dari', 'sampai'
        ))->setPaper('a4', 'portrait');

        $namaFile = 'RekamMedis_' . ($session->medical_record_number ?? 'Unknown') . '_' . now()->format('Ymd') . '.pdf';

        return $pdf->stream($namaFile);
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

            Log::warning("QuickChart fetch failed: HTTP {$httpCode}, Error: {$error}");
            return null;
        } catch (\Exception $e) {
            Log::warning('QuickChart exception: ' . $e->getMessage());
            return null;
        }
    }
}
