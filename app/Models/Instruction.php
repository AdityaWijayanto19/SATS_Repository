<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Instruction extends Model
{
    // Tentukan nama tabel eksplisit
    protected $table = 'instructions';

    protected $fillable = [
        'device_id',
        'user_id',
        'instruksi_dokter',      // Updated: was 'teks'
        'respon_nakes',          // Updated: was 'respon'
        'laporan_nakes',         // New field
        'is_completed',
        'completed_by',
        'completed_at',
        'nakes_id',              // New field
        'dokter_id',             // New field
    ];

    protected $casts = [
        'is_completed' => 'boolean',
        'completed_at' => 'datetime',
    ];

    // Relasi: dokter yang memberi instruksi (via user_id atau dokter_id)
    public function dokter()
    {
        return $this->belongsTo(User::class, 'dokter_id')->select('id', 'name');
    }

    // Relasi: nakes yang melaksanakan (via nakes_id atau completed_by)
    public function nakes()
    {
        return $this->belongsTo(User::class, 'nakes_id')->select('id', 'name');
    }

    // Relasi: user yang membuat instruksi (fallback ke user_id)
    public function creator()
    {
        return $this->belongsTo(User::class, 'user_id')->select('id', 'name');
    }

    public function device()
    {
        return $this->belongsTo(Devices::class, 'device_id', 'device_id');
    }
}
