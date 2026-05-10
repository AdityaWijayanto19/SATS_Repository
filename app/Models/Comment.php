<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    protected $fillable = [
        'device_id',
        'user_id',
        'teks',
        'respon',
        'responded_by',
        'responded_at',
    ];

    protected $casts = [
        'responded_at' => 'datetime',
    ];

    public function device()
    {
        return $this->belongsTo(Devices::class, 'device_id', 'device_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function responder()
    {
        return $this->belongsTo(User::class, 'responded_by');
    }
}
