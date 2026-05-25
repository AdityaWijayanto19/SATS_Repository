<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeviceMonitoring extends Model
{
    protected $fillable = ['device_id', 'dokter_id'];

    public function device()
    {
        return $this->belongsTo(Devices::class, 'device_id', 'device_id');
    }

    public function dokter()
    {
        return $this->belongsTo(User::class, 'dokter_id');
    }
}
