<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
// use App\Models\Pasien;
// use App\Models\VitalSign;
// use App\Models\RiwayatKondisi;

class LaporanController extends Controller
{
    /**
     * Halaman preview laporan (tampil di browser).
     */
    public function index(Request $request)
    {
        $pasienId = $request->get('pasien_id', 1);
        $dari     = $request->get('dari', now()->subDays(7)->toDateString());
        $sampai   = $request->get('sampai', now()->toDateString());

        // ── Ambil data dari database ──────────────────────────────
        // $pasien        = Pasien::findOrFail($pasienId);
        // $vitalTerbaru  = VitalSign::where('pasien_id', $pasienId)->latest()->first();
        // $riwayat       = RiwayatKondisi::where('pasien_id', $pasienId)
        //                     ->whereBetween('waktu', [$dari . ' 00:00:00', $sampai . ' 23:59:59'])
        //                     ->orderBy('waktu')->get();
        // $labelGrafik   = $vitalSigns->pluck('kode')->toArray();
        // $dataSistolik  = $vitalSigns->pluck('sistolik')->toArray();
        // $dataDiastolik = $vitalSigns->pluck('diastolik')->toArray();

        // ── Data dummy (hapus setelah DB tersambung) ──────────────
        $pasien        = null;
        $vitalTerbaru  = null;
        $riwayat       = [];
        $labelGrafik   = ['PM001','PM002','PM003','PM004','PM005','PM006','PM007','PM008','PM009','PM010'];
        $dataSistolik  = [130,125,135,128,140,132,138,126,134,129];
        $dataDiastolik = [82,78,85,80,88,83,86,79,84,81];

        // ── Prediksi ML ───────────────────────────────────────────
        // TODO: Ganti dengan data dari endpoint ML, misal:
        // $prediksi = Http::get("/api/device/{$pasienId}/prediction")->object();
        $prediksi = (object)[
            'risk_level'        => 'warning',
            'risk_percent'      => 20,
            'timeframe_minutes' => 15,
            'message'           => 'Kondisi pasien berpotensi memburuk 20% dalam 15 menit ke depan berdasarkan tren Heart Rate dan SpO2.',
        ];

        $role = auth()->user()->role;
        $viewFolder = $role === 'dokter' ? 'pages.dokter' : 'pages.nakes';

        return view($viewFolder . '.laporan', compact(
            'pasien', 'vitalTerbaru', 'riwayat',
            'dari', 'sampai',
            'labelGrafik', 'dataSistolik', 'dataDiastolik',
            'prediksi'
        ));
    }

    /**
     * Generate & stream PDF laporan.
     */
    public function pdf(Request $request)
    {
        $pasienId = $request->get('pasien_id', 1);
        $dari     = $request->get('dari', now()->subDays(7)->toDateString());
        $sampai   = $request->get('sampai', now()->toDateString());

        // ── Ambil data dari database ──────────────────────────────
        // $pasien       = Pasien::findOrFail($pasienId);
        // $vitalTerbaru = VitalSign::where('pasien_id', $pasienId)->latest()->first();
        // $riwayat      = RiwayatKondisi::where('pasien_id', $pasienId)
        //                     ->whereBetween('waktu', [$dari.' 00:00:00', $sampai.' 23:59:59'])
        //                     ->orderBy('waktu')->get();
        // $sistolik     = VitalSign::where('pasien_id', $pasienId)->pluck('sistolik')->toArray();
        // $diastolik    = VitalSign::where('pasien_id', $pasienId)->pluck('diastolik')->toArray();
        // $labels       = VitalSign::where('pasien_id', $pasienId)->pluck('kode')->toArray();

        // ── Data dummy ─────────────────────────────────────────────
        $pasien       = null;
        $vitalTerbaru = null;
        $riwayat      = [];
        $sistolik     = [130,125,135,128,140,132,138,126,134,129];
        $diastolik    = [82,78,85,80,88,83,86,79,84,81];
        $labels       = ['PM001','PM002','PM003','PM004','PM005','PM006','PM007','PM008','PM009','PM010'];

        // ── Prediksi ML ───────────────────────────────────────────
        // TODO: Ganti dengan data dari endpoint ML
        $prediksi = (object)[
            'risk_level'        => 'warning',
            'risk_percent'      => 20,
            'timeframe_minutes' => 15,
            'message'           => 'Kondisi pasien berpotensi memburuk 20% dalam 15 menit ke depan berdasarkan tren Heart Rate dan SpO2.',
        ];

        // ── Generate grafik via QuickChart.io ─────────────────────
        $grafikBase64 = $this->generateChartBase64($labels, $sistolik, $diastolik);

        $role = auth()->user()->role;
        $viewFolder = $role === 'dokter' ? 'pages.dokter' : 'pages.nakes';

        $pdf = Pdf::loadView($viewFolder . '/laporan-pdf', compact(
            'pasien', 'vitalTerbaru', 'riwayat',
            'dari', 'sampai', 'grafikBase64',
            'prediksi'
        ))->setPaper('a4', 'portrait');

        $namaFile = 'Laporan_' . ($pasien->no_rekam_medis ?? '24E56') . '_' . now()->format('Ymd') . '.pdf';

        return $pdf->stream($namaFile);
    }

    /**
     * Ambil grafik sebagai base64 PNG via QuickChart.io.
     */
    private function generateChartBase64(array $labels, array $sistolik, array $diastolik): ?string
    {
        $chartConfig = [
            'type' => 'line',
            'data' => [
                'labels'   => $labels,
                'datasets' => [
                    [
                        'label'       => 'Sistolik',
                        'data'        => $sistolik,
                        'borderColor' => 'rgb(220,38,38)',
                        'borderWidth' => 2,
                        'pointRadius' => 3,
                        'tension'     => 0.4,
                        'fill'        => false,
                    ],
                    [
                        'label'       => 'Diastolik',
                        'data'        => $diastolik,
                        'borderColor' => 'rgb(59,130,246)',
                        'borderWidth' => 2,
                        'pointRadius' => 3,
                        'tension'     => 0.4,
                        'fill'        => false,
                    ],
                ],
            ],
            'options' => [
                'plugins' => ['legend' => ['display' => true]],
                'scales'  => [
                    'y' => ['title' => ['display' => true, 'text' => 'Tekanan (mmHg)']],
                ],
            ],
        ];

        $url = 'https://quickchart.io/chart?c=' . urlencode(json_encode($chartConfig)) . '&w=500&h=200&bkg=white';

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