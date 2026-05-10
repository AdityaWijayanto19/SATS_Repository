<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Patient extends Model
{
    protected $fillable = [
        'device_id',
        'nama',
        'jenis_kelamin',
        'umur',
        'catatan_tambahan',
        'nakes_id',
    ];

    protected $casts = [
        'umur' => 'integer',
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
}
