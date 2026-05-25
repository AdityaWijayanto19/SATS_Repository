<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\Devices;
use App\Models\MonitoringSession;
use App\Models\Patient;
use App\Models\SensorData;
use App\Models\SensorReading;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MonitoringSessionService
{
    /**
     * Create a new monitoring session when device goes online.
     */
    public function createSession(string $deviceId, int $userId): MonitoringSession
    {
        $medicalRecordNumber = $this->generateMedicalRecordNumber($deviceId);

        $session = MonitoringSession::create([
            'device_id' => $deviceId,
            'medical_record_number' => $medicalRecordNumber,
            'created_by' => $userId,
            'started_at' => now(),
            'status' => 'active',
            'total_readings' => 0,
        ]);

        ActivityLog::log(
            'monitoring.started',
            "Sesi monitoring dimulai: {$medicalRecordNumber}",
            'System',
            'system',
            $deviceId
        );

        Log::info("Monitoring session created: {$medicalRecordNumber} for device {$deviceId}");

        return $session;
    }

    /**
     * Finalize a session: copy sensor_data to sensor_readings, update status.
     */
    public function finalizeSession(int $sessionId): ?MonitoringSession
    {
        return DB::transaction(function () use ($sessionId) {
            $session = MonitoringSession::findOrFail($sessionId);

            if ($session->status === 'completed') {
                return $session;
            }

            // Copy sensor_data to sensor_readings
            $sensorData = SensorData::where('device_id', $session->device_id)
                ->where('created_at', '>=', $session->started_at)
                ->when($session->ended_at, fn($q) => $q->where('created_at', '<=', $session->ended_at))
                ->orderBy('created_at')
                ->get();

            $readings = [];
            foreach ($sensorData as $data) {
                $readings[] = [
                    'session_id' => $session->id,
                    'heart_rate' => $data->heart_rate,
                    'spo2' => $data->spo2,
                    'temperature' => $data->temperature,
                    'status' => $data->status,
                    'recorded_at' => $data->created_at,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            if (!empty($readings)) {
                SensorReading::insert($readings);
            }

            // Update session
            $session->update([
                'status' => 'completed',
                'ended_at' => now(),
                'total_readings' => count($readings),
            ]);

            // Delete ALL sensor_data for this device (data already copied to sensor_readings)
            SensorData::where('device_id', $session->device_id)->delete();

            ActivityLog::log(
                'monitoring.completed',
                "Sesi monitoring selesai: {$session->medical_record_number} ({$session->total_readings} data)",
                'System',
                'system',
                $session->device_id
            );

            Log::info("Monitoring session finalized: {$session->medical_record_number}, {$session->total_readings} readings copied");

            return $session;
        });
    }

    /**
     * Cancel a session.
     */
    public function cancelSession(int $sessionId): ?MonitoringSession
    {
        $session = MonitoringSession::findOrFail($sessionId);

        $session->update([
            'status' => 'cancelled',
            'ended_at' => now(),
        ]);

        // Delete all sensor_data for this device
        SensorData::where('device_id', $session->device_id)->delete();

        Log::info("Monitoring session cancelled: {$session->medical_record_number}");

        return $session;
    }

    /**
     * Link patient data to a session.
     */
    public function linkPatient(int $sessionId, array $patientData, ?int $dokterId = null): MonitoringSession
    {
        $session = MonitoringSession::findOrFail($sessionId);

        // Check if patient already exists for this session
        if ($session->patient_id) {
            // Update existing patient
            $patient = Patient::findOrFail($session->patient_id);
            $patient->update($patientData);
        } else {
            // Create new patient
            $patientData['no_rekam_medis'] = $session->medical_record_number;
            $patientData['device_id'] = $session->device_id;
            $patientData['nakes_id'] = $session->created_by;

            $patient = Patient::create($patientData);
            $session->update(['patient_id' => $patient->id]);
        }

        // Assign dokter ke session
        if ($dokterId) {
            $session->update(['dokter_id' => $dokterId]);
        }

        Log::info("Patient linked to session: {$session->medical_record_number}");

        return $session->fresh();
    }

    /**
     * Get sessions for a device (for laporan filter).
     */
    public function getSessionsForDevice(string $deviceId)
    {
        return MonitoringSession::where('device_id', $deviceId)
            ->with('patient')
            ->orderBy('started_at', 'desc')
            ->get();
    }

    /**
     * Get completed sessions for a device (for laporan dropdown).
     */
    public function getCompletedSessionsForDevice(string $deviceId)
    {
        return MonitoringSession::where('device_id', $deviceId)
            ->where('status', 'completed')
            ->with('patient')
            ->orderBy('started_at', 'desc')
            ->get();
    }

    /**
     * Get active session for a device.
     */
    public function getActiveSession(string $deviceId): ?MonitoringSession
    {
        return MonitoringSession::where('device_id', $deviceId)
            ->where('status', 'active')
            ->with('patient')
            ->latest('started_at')
            ->first();
    }

    /**
     * Auto-generate medical record number.
     * Format: RM-{DEVICE_ID}-{YYYYMMDD}-{SEQ}
     */
    public function generateMedicalRecordNumber(string $deviceId): string
    {
        $today = Carbon::now()->format('Ymd');
        $prefix = "RM-{$deviceId}-{$today}";

        // Count existing sessions today for this device
        $count = MonitoringSession::where('medical_record_number', 'like', "{$prefix}-%")->count();
        $seq = str_pad($count + 1, 3, '0', STR_PAD_LEFT);

        return "{$prefix}-{$seq}";
    }
}
