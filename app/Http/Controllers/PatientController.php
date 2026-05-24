<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Services\MonitoringSessionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PatientController extends Controller
{
    public function __construct(
        protected MonitoringSessionService $sessionService
    ) {}

    /**
     * Store patient data and link to monitoring session.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'device_id' => 'required|string|exists:devices,device_id',
            'session_id' => 'nullable|integer|exists:monitoring_sessions,id',
            'nama' => 'required|string|max:255',
            'nik' => 'nullable|string|max:20',
            'tanggal_lahir' => 'nullable|date',
            'jenis_kelamin' => 'required|in:L,P',
            'umur' => 'required|integer|min:0|max:150',
            'penyakit_alergi' => 'nullable|string|max:500',
            'catatan_tambahan' => 'nullable|string|max:1000',
        ]);

        // Find session: use provided session_id or find active session
        $session = null;
        if ($validated['session_id'] ?? null) {
            $session = \App\Models\MonitoringSession::find($validated['session_id']);
        } else {
            $session = $this->sessionService->getActiveSession($validated['device_id']);
        }

        if (!$session) {
            return back()->withErrors([
                'device_id' => 'Tidak ada sesi monitoring yang ditemukan untuk perangkat ini.'
            ])->withInput();
        }

        // Link patient to session
        $session = $this->sessionService->linkPatient($session->id, $validated);

        ActivityLog::log(
            'patient.registered',
            "Data pasien terdaftar: {$session->patient->nama} ({$session->medical_record_number})",
            Auth::user()->name,
            'nakes',
            $validated['device_id']
        );

        Log::info("Patient data saved for session {$session->medical_record_number}");

        // Redirect back to laporan if session_id was provided, otherwise to input-data-pasien
        if ($validated['session_id'] ?? null) {
            return redirect()
                ->route('laporan.index', ['session_id' => $session->id])
                ->with('success', "Data pasien berhasil disimpan.");
        }

        return redirect()
            ->route('input-data-pasien')
            ->with('success', "Data pasien berhasil disimpan. No. Rekam Medis: {$session->medical_record_number}");
    }
}
