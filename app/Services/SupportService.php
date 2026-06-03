<?php

namespace App\Services;

use App\Events\SupportReportCreated;
use App\Models\SupportReport;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Storage;

class SupportService
{
    /**
     * Simpan report baru dari guest.
     */
    public function store(array $data): SupportReport
    {
        // Handle file upload jika ada
        $attachmentPath = null;
        if (isset($data['attachment']) && $data['attachment']) {
            $attachmentPath = $data['attachment']->store('reports', 'public');
        }

        $report = SupportReport::create([
            'category' => $data['category'],
            'device_id' => $data['device_id'] ?? null,
            'role_requested' => $data['role_requested'] ?? null,
            'institution' => $data['institution'] ?? null,
            'full_name' => $data['full_name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'urgency' => $data['urgency'],
            'detail' => $data['detail'],
            'attachment_path' => $attachmentPath,
            'status' => 'baru',
        ]);

        // Broadcast ke superadmin dashboard via WebSocket
        SupportReportCreated::dispatch($report);

        return $report;
    }

    /**
     * Ambil reports dengan filter, search, dan pagination.
     */
    public function getFilteredReports(array $filters): LengthAwarePaginator
    {
        return SupportReport::query()
            ->status($filters['status'] ?? null)
            ->ofCategory($filters['category'] ?? null)
            ->ofUrgency($filters['urgency'] ?? null)
            ->search($filters['search'] ?? null)
            ->latest()
            ->paginate(10);
    }

    /**
     * Hitung jumlah report yang belum dibaca (status = baru).
     */
    public function getUnreadCount(): int
    {
        return SupportReport::where('status', 'baru')->count();
    }

    /**
     * Tandai report sebagai "diproses".
     */
    public function markAsProcessed(SupportReport $report): void
    {
        $report->update([
            'status' => 'diproses',
            'responded_at' => now(),
        ]);
    }

    /**
     * Tandai report sebagai "selesai".
     */
    public function markAsCompleted(SupportReport $report): void
    {
        $report->update([
            'status' => 'selesai',
            'responded_at' => now(),
        ]);
    }

    /**
     * Tambah catatan admin.
     */
    public function addAdminNote(SupportReport $report, string $note): void
    {
        $report->update(['admin_notes' => $note]);
    }

    /**
     * Hapus report + file attachment jika ada.
     */
    public function destroy(SupportReport $report): void
    {
        if ($report->attachment_path) {
            Storage::disk('public')->delete($report->attachment_path);
        }
        $report->delete();
    }
}
