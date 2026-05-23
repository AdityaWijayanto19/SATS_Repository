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

class InstructionSent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Instruction $instruction) {}

    public function broadcastOn(): array
    {
        // Gunakan PrivateChannel agar hanya user yang authorized yang bisa dengar
        return [new PrivateChannel('device.' . $this->instruction->device_id)];
    }

    public function broadcastAs(): string
    {
        return 'instruction.created';
    }

    // Tentukan exact payload yang di-broadcast ke frontend
    public function broadcastWith(): array
    {
        return [
            'instruction' => [
                'id'           => $this->instruction->id,
                'device_id'    => $this->instruction->device_id,
                'instruksi_dokter' => $this->instruction->instruksi_dokter,
                'is_completed' => (bool) $this->instruction->is_completed,
                'user_name'    => $this->instruction->dokter?->name ?? 'Dokter SATS',
                'waktu'        => $this->instruction->created_at->setTimezone('Asia/Jakarta')->format('H:i'),
                'created_at'   => $this->instruction->created_at->toIso8601String(),
                'completed_at' => null,
                'completed_by' => '—',
                'respon'       => null,
            ],
        ];
    }
}
