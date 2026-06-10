<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class SensorData extends Model
{
    protected $table = 'sensor_datas';

    protected $fillable = [
        'device_id',
        'heart_rate',
        'spo2',
        'temperature',
        'kategori_usia',
        'status',
    ];

    protected $casts = [
        'heart_rate' => 'integer',
        'spo2' => 'integer',
        'temperature' => 'float',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relationship: Belong to Device
     */
    public function device()
    {
        return $this->belongsTo(Devices::class, 'device_id', 'device_id');
    }

    public function scopeLatest($query, $deviceId)
    {
        return $query->where('device_id', $deviceId)
            ->orderByDesc('created_at')
            ->limit(1);
    }

    public function scopeWithinRange($query, $deviceId, $from, $to)
    {
        return $query->where('device_id', $deviceId)
            ->whereBetween('created_at', [$from, $to])
            ->orderByDesc('created_at');
    }

    public function scopeOnlyVitals($query)
    {
        return $query->select(
            'id',
            'device_id',
            'heart_rate',
            'spo2',
            'temperature',
            'status',
            'created_at'
        );
    }

    /**
     * Status badge (for dashboard)
     */
    protected function statusBadge(): Attribute
    {
        return Attribute::make(
            get: fn($value) => match ($this->status) {
                'critical' => 'Critical',
                'warning' => 'Warning',
                'normal' => 'Normal',
                'no_finger' => 'No Finger',
                default => 'No Finger',
            }
        );
    }
}
