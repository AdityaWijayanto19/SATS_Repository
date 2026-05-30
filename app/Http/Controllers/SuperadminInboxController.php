<?php

namespace App\Http\Controllers;

use App\Models\SupportReport;
use App\Services\SupportService;
use Illuminate\Http\Request;

class SuperadminInboxController extends Controller
{
    public function __construct(
        private SupportService $supportService
    ) {}

    /**
     * Tampilkan halaman inbox dengan filter & pagination.
     */
    public function index(Request $request)
    {
        $filters = $request->only(['status', 'category', 'urgency', 'search']);
        $reports = $this->supportService->getFilteredReports($filters);
        $unreadCount = $this->supportService->getUnreadCount();

        return view('pages.superadmin.inbox', compact('reports', 'filters', 'unreadCount'));
    }

    /**
     * Detail satu report (JSON untuk modal).
     */
    public function show(SupportReport $report)
    {
        return response()->json([
            'success' => true,
            'data' => [
                'id' => $report->id,
                'category' => $report->category,
                'category_label' => $report->category_label,
                'device_id' => $report->device_id,
                'role_requested' => $report->role_requested,
                'institution' => $report->institution,
                'full_name' => $report->full_name,
                'email' => $report->email,
                'phone' => $report->phone,
                'urgency' => $report->urgency,
                'urgency_label' => $report->urgency_label,
                'detail' => $report->detail,
                'attachment_url' => $report->attachment_path ? asset('storage/' . $report->attachment_path) : null,
                'status' => $report->status,
                'status_label' => $report->status_label,
                'admin_notes' => $report->admin_notes,
                'responded_at' => $report->responded_at?->format('d M Y, H:i'),
                'created_at' => $report->created_at->format('d M Y, H:i'),
            ],
        ]);
    }

    /**
     * Update status + catatan admin.
     */
    public function update(Request $request, SupportReport $report)
    {
        $validated = $request->validate([
            'status' => 'sometimes|in:baru,diproses,selesai',
            'admin_notes' => 'nullable|string|max:1000',
        ]);

        if (isset($validated['status'])) {
            if ($validated['status'] === 'diproses') {
                $this->supportService->markAsProcessed($report);
            } elseif ($validated['status'] === 'selesai') {
                $this->supportService->markAsCompleted($report);
            }
        }

        if (isset($validated['admin_notes'])) {
            $this->supportService->addAdminNote($report, $validated['admin_notes']);
        }

        return response()->json([
            'success' => true,
            'message' => 'Report berhasil diperbarui.',
        ]);
    }

    /**
     * Hapus report.
     */
    public function destroy(SupportReport $report)
    {
        $this->supportService->destroy($report);

        return response()->json([
            'success' => true,
            'message' => 'Report berhasil dihapus.',
        ]);
    }
}
