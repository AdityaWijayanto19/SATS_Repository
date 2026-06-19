<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSupportReportRequest;
use App\Models\SupportReport;
use App\Services\SupportService;
use Illuminate\Http\Request;

class SupportController extends Controller
{
    public function __construct(
        private SupportService $supportService
    ) {}

    /**
     * Simpan report dari guest (tanpa auth).
     */
    public function store(StoreSupportReportRequest $request)
    {
        // Rate limit: max 5 submit per email per hari
        $todayCount = SupportReport::where('email', $request->validated('email'))
            ->whereDate('created_at', today())
            ->count();

        if ($todayCount >= 5) {
            return response()->json([
                'success' => false,
                'message' => 'Anda sudah mengirim 5 laporan hari ini. Silakan coba lagi besok.',
            ], 429);
        }

        $report = $this->supportService->store($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Pesan berhasil dikirim. Superadmin akan segera merespons.',
            'data' => [
                'id' => $report->id,
                'category' => $report->category_label,
                'status' => $report->status_label,
            ],
        ], 201);
    }
}
