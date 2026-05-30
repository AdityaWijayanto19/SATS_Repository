<?php

namespace App\Events;

use App\Models\SupportReport;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SupportReportCreated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public SupportReport $report,
    ) {}

    public function broadcastOn(): array
    {
        return [new PrivateChannel('superadmin.dashboard')];
    }

    public function broadcastAs(): string
    {
        return 'support.report.created';
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->report->id,
            'full_name' => $this->report->full_name,
            'email' => $this->report->email,
            'category' => $this->report->category,
            'category_label' => $this->report->category_label,
            'urgency' => $this->report->urgency,
            'urgency_label' => $this->report->urgency_label,
            'status' => $this->report->status,
            'status_label' => $this->report->status_label,
            'created_at' => $this->report->created_at->setTimezone('Asia/Jakarta')->format('d M Y'),
            'created_at_time' => $this->report->created_at->setTimezone('Asia/Jakarta')->format('H:i'),
        ];
    }
}
