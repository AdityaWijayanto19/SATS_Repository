<?php

namespace App\Http\Controllers;

use App\Services\SuperadminReportService;
use Illuminate\Http\Request;

class SuperadminLaporanController extends Controller
{
    public function __construct(
        private SuperadminReportService $reportService
    ) {}

    /**
     * Halaman laporan superadmin (tampil di browser).
     */
    public function index(Request $request)
    {
        $dari = $request->get('dari', now()->subDays(7)->toDateString());
        $sampai = $request->get('sampai', now()->toDateString());
        $kategori = $request->get('kategori', '');
        $deviceId = $request->get('device_id', '');
        $tab = $request->get('tab', 'operasional');

        // Ambil data untuk kedua tab (supaya switch tanpa reload)
        $operasionalData = $this->reportService->getOperasionalData($dari, $sampai, $deviceId ?: null);
        $auditData = $this->reportService->getAuditData($dari, $sampai, $kategori ?: null, $deviceId ?: null);
        $devices = $this->reportService->getAllDevices();
        $kategoriList = $this->reportService->getKategoriList();

        return view('pages.superadmin.laporan', compact(
            'operasionalData', 'auditData', 'devices', 'kategoriList',
            'dari', 'sampai', 'kategori', 'deviceId', 'tab'
        ));
    }

    /**
     * Generate & stream PDF laporan superadmin.
     */
    public function pdf(Request $request)
    {
        $dari = $request->get('dari', now()->subDays(7)->toDateString());
        $sampai = $request->get('sampai', now()->toDateString());
        $exportType = $request->get('export_type', 'both');
        $deviceId = $request->get('device_id', '');
        $kategori = $request->get('kategori', '');

        return $this->reportService->generatePdf($dari, $sampai, $exportType, $deviceId ?: null, $kategori ?: null);
    }
}
