<?php

namespace App\Events;

use App\Models\Instruction;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class InstructionReportSubmitted implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Instruction $instruction) {}

    public function broadcastOn(): array
    {
        return [new PrivateChannel('device.' . $this->instruction->device_id)];
    }

    public function broadcastAs(): string
    {
        // Pakai dot notation lebih standar di Laravel Echo
        return 'instruction.report.submitted';
    }

    public function broadcastWith(): array
    {
        return [
            'instruction' => [
                'id'              => $this->instruction->id,
                'device_id'       => $this->instruction->device_id,
                'laporan_nakes'   => $this->instruction->laporan_nakes,
                'nakes_name'      => $this->instruction->nakes?->name ?? 'Nakes SATS',
                'waktu'           => $this->instruction->created_at->format('H:i'),
                'created_at'      => $this->instruction->created_at->toIso8601String(),
                'is_completed'    => false,
            ],
        ];
    }
}
