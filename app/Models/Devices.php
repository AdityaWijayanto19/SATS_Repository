<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Devices extends Model
{
    protected $table = 'devices';
    protected $primaryKey = 'device_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'device_id',
        'status',
        'monitored_by',
        'last_seen',
        'ml_prediction',
        'ml_condition',
        'ml_risk_level',
        'ml_probabilities',
        'ml_predicted_at',
    ];

    protected $casts = [
        'ml_predicted_at' => 'datetime',
    ];

    public function sensorData()
    {
        return $this->hasMany(SensorData::class, 'device_id', 'device_id');
    }

    public function systemStatus()
    {
        return $this->hasOne(SystemStatus::class, 'device_id', 'device_id');
    }

    public function apiKeys()
    {
        return $this->hasMany(ApiKey::class, 'device_id', 'device_id');
    }

    public function patients()
    {
        return $this->hasMany(Patient::class, 'device_id', 'device_id');
    }

    public function medicalRecords()
    {
        return $this->hasMany(MedicalRecord::class, 'device_id', 'device_id');
    }

    public function monitoredBy()
    {
        return $this->belongsTo(User::class, 'monitored_by');
    }

    public function monitoredByDokters()
    {
        return $this->belongsToMany(User::class, 'device_monitorings', 'device_id', 'dokter_id');
    }
}
