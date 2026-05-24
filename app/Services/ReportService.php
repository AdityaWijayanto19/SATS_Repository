<?php

namespace App\Services;

use App\Models\MonitoringSession;
use App\Models\SensorReading;
use Illuminate\Support\Collection;

class ReportService
{
    /**
     * Get report data for a session.
     * Returns sensor readings filtered by selected vital signs.
     */
    public function getReportData(int $sessionId, array $vitalSigns = ['heart_rate', 'spo2', 'temperature']): ?MonitoringSession
    {
        return MonitoringSession::with(['patient', 'sensorReadings' => function ($query) use ($vitalSigns) {
            $query->select(array_merge(['id', 'session_id', 'status', 'recorded_at'], $vitalSigns));
            $query->orderBy('recorded_at', 'asc');
        }])->findOrFail($sessionId);
    }

    /**
     * Get the latest reading for a session (for summary card).
     */
    public function getLatestReading(int $sessionId): ?SensorReading
    {
        return SensorReading::where('session_id', $sessionId)
            ->orderBy('recorded_at', 'desc')
            ->first();
    }

    /**
     * Get history data formatted for Chart.js.
     */
    public function getHistoryForChart(int $sessionId, array $vitalSigns = ['heart_rate', 'spo2', 'temperature']): array
    {
        $readings = SensorReading::where('session_id', $sessionId)
            ->select(array_merge(['recorded_at'], $vitalSigns))
            ->orderBy('recorded_at', 'asc')
            ->get();

        $labels = [];
        $datasets = [];

        foreach ($vitalSigns as $sign) {
            $datasets[$sign] = [];
        }

        foreach ($readings as $reading) {
            $labels[] = $reading->recorded_at->format('H:i:s');
            foreach ($vitalSigns as $sign) {
                $datasets[$sign][] = $reading->$sign;
            }
        }

        return [
            'labels' => $labels,
            'datasets' => $datasets,
        ];
    }

    /**
     * Get statistics for a session.
     */
    public function getSessionStats(int $sessionId): array
    {
        $readings = SensorReading::where('session_id', $sessionId);

        return [
            'total_readings' => (clone $readings)->count(),
            'avg_heart_rate' => round((clone $readings)->avg('heart_rate'), 1),
            'avg_spo2' => round((clone $readings)->avg('spo2'), 1),
            'avg_temperature' => round((clone $readings)->avg('temperature'), 1),
            'min_heart_rate' => (clone $readings)->min('heart_rate'),
            'max_heart_rate' => (clone $readings)->max('heart_rate'),
            'min_spo2' => (clone $readings)->min('spo2'),
            'max_spo2' => (clone $readings)->max('spo2'),
            'min_temperature' => (clone $readings)->min('temperature'),
            'max_temperature' => (clone $readings)->max('temperature'),
            'critical_count' => (clone $readings)->where('status', 'critical')->count(),
            'warning_count' => (clone $readings)->where('status', 'warning')->count(),
            'normal_count' => (clone $readings)->where('status', 'normal')->count(),
        ];
    }

    /**
     * Get session summary for laporan list.
     */
    public function getSessionSummaries(string $deviceId): Collection
    {
        return MonitoringSession::where('device_id', $deviceId)
            ->where('status', 'completed')
            ->with('patient')
            ->orderBy('started_at', 'desc')
            ->get()
            ->map(function ($session) {
                $latestReading = $this->getLatestReading($session->id);
                $stats = $this->getSessionStats($session->id);

                return [
                    'session' => $session,
                    'patient' => $session->patient,
                    'latest_reading' => $latestReading,
                    'stats' => $stats,
                ];
            });
    }
}
