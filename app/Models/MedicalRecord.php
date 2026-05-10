<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MedicalRecord extends Model
{
    protected $table = 'medical_records';

    protected $fillable = [
        'patient_id',
        'device_id',
        'heart_rate',
        'spo2',
        'temperature',
        'status',
        'prediction',
    ];

    protected $casts = [
        'heart_rate' => 'integer',
        'spo2' => 'integer',
        'temperature' => 'float',
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class, 'patient_id');
    }

    public function device()
    {
        return $this->belongsTo(Devices::class, 'device_id', 'device_id');
    }
}
