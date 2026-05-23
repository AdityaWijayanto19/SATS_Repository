<?php

namespace App\Models;

use App\Events\ActivityLogCreated;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class ActivityLog extends Model
{
    protected $table = 'activity_log';
    public $timestamps = false;

    protected $fillable = [
        'type',
        'message',
        'user_name',
        'user_role',
        'icon',
        'device_id',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    const ICON_MAP = [
        'user.login' => 'blue',
        'user.logout' => 'red',
        'user.added' => 'emerald',
        'user.deleted' => 'red',
        'password.reset_request' => 'gray',
        'password.reset_success' => 'gray',
        'device.online' => 'emerald',
        'device.offline' => 'red',
        'device.added' => 'emerald',
        'device.deleted' => 'red',
        'monitoring.started' => 'violet',
        'monitoring.stopped' => 'violet',
        'patient.warning' => 'amber',
        'patient.critical' => 'red',
        'instruction.sent' => 'indigo',
        'instruction.completed' => 'green',
    ];

    public static function log(
        string $type,
        string $message,
        ?string $userName = null,
        ?string $userRole = null,
        ?string $deviceId = null
    ): self {
        $entry = self::create([
            'type' => $type,
            'message' => $message,
            'user_name' => $userName,
            'user_role' => $userRole,
            'icon' => self::ICON_MAP[$type] ?? 'gray',
            'device_id' => $deviceId,
            'created_at' => now(),
        ]);

        try {
            broadcast(new ActivityLogCreated($entry));
        } catch (\Exception $e) {
            Log::warning('Broadcast activity log gagal', ['error' => $e->getMessage()]);
        }

        return $entry;
    }
}
