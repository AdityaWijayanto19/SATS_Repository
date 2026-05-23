<?php

namespace App\Events;

use App\Models\Instruction;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class InstructionStatusUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Instruction $instruction) {}

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        return [new PrivateChannel('device.' . $this->instruction->device_id)];
    }

    public function broadcastAs(): string
    {
        return 'instruction.updated';
    }

    // Tentukan exact payload yang di-broadcast ke frontend
    public function broadcastWith(): array
    {
        return [
            'instruction' => [
                'id'           => $this->instruction->id,
                'device_id'    => $this->instruction->device_id,
                'is_completed' => (bool) $this->instruction->is_completed,
                'respon_nakes' => $this->instruction->respon_nakes,
                'completed_by' => $this->instruction->nakes?->name ?? '—',
                'completed_at' => $this->instruction->completed_at?->format('H:i'),
                'updated_at'   => $this->instruction->updated_at->toIso8601String(),
            ],
        ];
    }
}
