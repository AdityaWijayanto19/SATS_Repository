<?php

namespace App\Events;

use App\Models\ActivityLog;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ActivityLogCreated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public ActivityLog $log,
    ) {}

    public function broadcastOn(): array
    {
        return [new PrivateChannel('superadmin.dashboard')];
    }

    public function broadcastAs(): string
    {
        return 'activity.log.created';
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->log->id,
            'type' => $this->log->type,
            'message' => $this->log->message,
            'icon' => $this->log->icon,
            'user_name' => $this->log->user_name,
            'user_role' => $this->log->user_role,
            'device_id' => $this->log->device_id,
            'created_at' => $this->log->created_at?->setTimezone('Asia/Jakarta')->format('d M Y, H:i'),
        ];
    }
}
