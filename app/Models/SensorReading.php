<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SensorReading extends Model
{
    protected $fillable = [
        'session_id',
        'heart_rate',
        'spo2',
        'temperature',
        'status',
        'recorded_at',
    ];

    protected $casts = [
        'heart_rate' => 'integer',
        'spo2' => 'integer',
        'temperature' => 'float',
        'recorded_at' => 'datetime',
    ];

    public function session(): BelongsTo
    {
        return $this->belongsTo(MonitoringSession::class, 'session_id');
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'critical' => 'Kritis',
            'warning' => 'Peringatan',
            'normal' => 'Normal',
            default => '-',
        };
    }
}
