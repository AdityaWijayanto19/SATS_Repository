<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class MonitoringSession extends Model
{
    protected $fillable = [
        'device_id',
        'patient_id',
        'medical_record_number',
        'created_by',
        'dokter_id',
        'started_at',
        'ended_at',
        'status',
        'total_readings',
        'notes',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'total_readings' => 'integer',
    ];

    public function device(): BelongsTo
    {
        return $this->belongsTo(Devices::class, 'device_id', 'device_id');
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function dokter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dokter_id');
    }

    public function sensorReadings(): HasMany
    {
        return $this->hasMany(SensorReading::class, 'session_id');
    }

    public function latestReading(): HasOne
    {
        return $this->hasOne(SensorReading::class, 'session_id')->latestOfMany('recorded_at');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeForDevice($query, string $deviceId)
    {
        return $query->where('device_id', $deviceId);
    }
}
