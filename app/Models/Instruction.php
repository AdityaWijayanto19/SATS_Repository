<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Instruction extends Model
{
    // Tentukan nama tabel eksplisit
    protected $table = 'instructions';

    protected $fillable = [
        'device_id',
        'instruksi_dokter',
        'respon_nakes',
        'laporan_nakes',
        'is_completed',
        'completed_by',
        'completed_at',
        'nakes_id',
        'dokter_id',
    ];

    protected $casts = [
        'is_completed' => 'boolean',
        'completed_at' => 'datetime',
    ];

    // Relasi: dokter yang memberi instruksi (via user_id atau dokter_id)
    public function dokter()
    {
        return $this->belongsTo(User::class, 'dokter_id')->select('id', 'name', 'photo');
    }

    // Relasi: nakes yang melaksanakan (via nakes_id atau completed_by)
    public function nakes()
    {
        return $this->belongsTo(User::class, 'nakes_id')->select('id', 'name', 'photo');
    }

    public function device()
    {
        return $this->belongsTo(Devices::class, 'device_id', 'device_id');
    }
}
