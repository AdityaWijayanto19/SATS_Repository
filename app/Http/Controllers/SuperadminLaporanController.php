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
        $ambulans = $request->get('ambulans', '');

        $data = $this->reportService->getReportData($dari, $sampai, $ambulans);

        return view('pages.superadmin.laporan', $data);
    }

    /**
     * Generate & stream PDF laporan superadmin.
     */
    public function pdf(Request $request)
    {
        $dari = $request->get('dari', now()->subDays(7)->toDateString());
        $sampai = $request->get('sampai', now()->toDateString());
        $ambulans = $request->get('ambulans', '');

        return $this->reportService->generatePdf($dari, $sampai, $ambulans);
    }
}
