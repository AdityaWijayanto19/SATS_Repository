<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FailedSensorData extends Model
{
    protected $table = 'failed_sensor_datas';

    protected $fillable = [
        'device_id',
        'payload',
        'error_message',
        'retry_count',
        'last_retry_at',
        'failed_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'last_retry_at' => 'datetime',
        'failed_at' => 'datetime',
    ];

    public function device()
    {
        return $this->belongsTo(Devices::class, 'device_id', 'device_id');
    }

    public function incrementRetry()
    {
        $this->increment('retry_count');
        $this->update(['last_retry_at' => now()]);
    }
}
