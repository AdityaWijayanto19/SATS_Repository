<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Patient extends Model
{
    protected $fillable = [
        'no_rekam_medis',
        'device_id',
        'nama',
        'nik',
        'tanggal_lahir',
        'jenis_kelamin',
        'umur',
        'penyakit_alergi',
        'catatan_tambahan',
        'nakes_id',
    ];

    protected $casts = [
        'umur' => 'integer',
        'tanggal_lahir' => 'date',
    ];

    public function device()
    {
        return $this->belongsTo(Devices::class, 'device_id', 'device_id');
    }

    public function nakes()
    {
        return $this->belongsTo(User::class, 'nakes_id');
    }

    public function medicalRecords()
    {
        return $this->hasMany(MedicalRecord::class, 'patient_id');
    }

    public function monitoringSessions()
    {
        return $this->hasMany(MonitoringSession::class, 'patient_id');
    }
}
