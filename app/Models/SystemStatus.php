<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemStatus extends Model
{
    protected $table = 'system_statuses';
    protected $primaryKey = 'device_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'device_id',
        'monitoring_status',
        'battery_level',
        'signal_strength',
        'updated_at',
    ];

    protected $casts = [
        'battery_level' => 'integer',
        'signal_strength' => 'integer',
        'updated_at' => 'datetime',
    ];

    public $timestamps = false; // Hanya use updated_at, tidak created_at

    /**
     * Relationship: Belong to Device
     */
    public function device()
    {
        return $this->belongsTo(Devices::class, 'device_id', 'device_id');
    }

    /**
     * Check if battery low
     */
    public function isBatteryLow(): bool
    {
        return $this->battery_level !== null && $this->battery_level < 20;
    }

    /**
     * Check if signal weak
     */
    public function isSignalWeak(): bool
    {
        return $this->signal_strength !== null && $this->signal_strength < 30;
    }
}
