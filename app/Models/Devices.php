<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Devices extends Model
{
    protected $table = 'devices';
    protected $primaryKey = 'device_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'device_id',
        'status',
        'last_seen',
        'ml_prediction',
        'ml_condition',
        'ml_risk_level',
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
}
