<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\Instruction;
use App\Events\InstructionSent;
use App\Events\InstructionStatusUpdated;
use App\Events\InstructionReportSubmitted;
use Illuminate\Support\Facades\Auth;

class InstructionService
{
    public function getInstructions(string $device_id)
    {
        // Query ke tabel instructions berdasarkan device_id
        return Instruction::where('device_id', $device_id)
            ->with(['dokter:id,name,photo', 'nakes:id,name,photo'])
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function ($item) {
                return [
                    'id'              => $item->id,
                    'instruksi_dokter' => $item->instruksi_dokter,
                    'is_completed'    => (bool) $item->is_completed,
                    'user_name'       => $item->dokter?->name ?? 'Dokter SATS',
                    'user_photo'      => $item->dokter?->photo ?? null,
                    'nakes_name'      => $item->nakes?->name ?? 'Nakes SATS',
                    'nakes_photo'     => $item->nakes?->photo ?? null,
                    'waktu'           => $item->created_at->setTimezone('Asia/Jakarta')->format('H:i'),
                    'completed_at'    => $item->completed_at ? $item->completed_at->setTimezone('Asia/Jakarta')->format('H:i') : null,
                    'completed_by'    => $item->nakes?->name ?? '—',
                    'respon_nakes'    => $item->respon_nakes,
                    'laporan_nakes'   => $item->laporan_nakes,
                ];
            });
    }

    public function storeInstruction(array $data)
    {
        $instruction = Instruction::create([
            'device_id'         => $data['device_id'],
            'dokter_id'         => Auth::id(), // Dokter yang memberi instruksi
            'instruksi_dokter'  => $data['instruksi_dokter'] ?? '',
        ]);

        $formatted = $this->formatSingleInstruction($instruction);
        try {
            broadcast(new InstructionSent($instruction->load('dokter:id,name')));
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning('Broadcast InstructionSent failed: ' . $e->getMessage());
        }

        $user = Auth::user();
        ActivityLog::log('instruction.sent', "Dokter {$user->name} mengirim instruksi pada perangkat", $user->name, $user->role, $data['device_id']);

        return $formatted;
    }

    public function completeInstruction(Instruction $instruction, string $respon)
    {
        // Idempotency check
        if ($instruction->is_completed) {
            throw new \Exception('Instruction sudah diselesaikan sebelumnya');
        }

        $instruction->update([
            'is_completed'  => true,
            'respon_nakes'  => $respon, // Updated column name
            'nakes_id'      => Auth::id(), // Nakes yang melaksanakan
            'completed_by'  => Auth::id(),
            'completed_at'  => now(),
        ]);

        // Broadcast ke dokter bahwa tugas sudah selesai
        try {
            broadcast(new InstructionStatusUpdated($instruction->load('nakes:id,name')));
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning('Broadcast InstructionStatusUpdated failed: ' . $e->getMessage());
        }

        $user = Auth::user();
        ActivityLog::log('instruction.completed', "Nakes {$user->name} menyelesaikan instruksi pada perangkat", $user->name, $user->role, $instruction->device_id);

        return $instruction;
    }

    public function updateInstruction(Instruction $instruction, array $data)
    {
        // Dokter update instruksi_dokter
        $instruction->update([
            'instruksi_dokter' => $data['instruksi_dokter'],
            'dokter_id'        => Auth::id(),
        ]);

        // Broadcast ke nakes bahwa ada instruksi baru
        try {
            broadcast(new InstructionSent($instruction->load('dokter:id,name')));
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning('Broadcast InstructionSent failed: ' . $e->getMessage());
        }

        return $instruction;
    }

    public function storeReport(array $data)
    {
        // Nakes submit laporan DULUAN (sebelum instruksi dokter dibuat)
        $instruction = Instruction::create([
            'device_id'     => $data['device_id'],
            'nakes_id'      => Auth::id(),  // Nakes yang melapor
            'laporan_nakes' => $data['laporan_nakes'],
            // instruksi_dokter, respon_nakes masih null
        ]);

        // Broadcast ke dokter bahwa ada laporan baru
        $formatted = $this->formatSingleInstruction($instruction);
        try {
            broadcast(new InstructionReportSubmitted($instruction->load('nakes:id,name')));
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning('Broadcast InstructionReportSubmitted failed: ' . $e->getMessage());
        }

        return $formatted;
    }

    private function formatSingleInstruction($item)
    {
        $currentUser = Auth::user();
        return [
            'id'              => $item->id,
            'instruksi_dokter' => $item->instruksi_dokter,
            'is_completed'    => (bool) $item->is_completed,
            'user_name'        => $item->dokter?->name ?? ($item->instruksi_dokter ? $currentUser->name : 'Dokter SATS'),
            'user_photo'       => $item->dokter?->photo ?? ($item->instruksi_dokter ? $currentUser->photo : null),
            'nakes_name'       => $item->nakes?->name ?? ($item->laporan_nakes ? $currentUser->name : 'Nakes SATS'),
            'nakes_photo'      => $item->nakes?->photo ?? ($item->laporan_nakes ? $currentUser->photo : null),
            'waktu'           => now()->setTimezone('Asia/Jakarta')->format('H:i'),
            'completed_at'    => $item->completed_at ? $item->completed_at->setTimezone('Asia/Jakarta')->format('H:i') : null,
            'respon_nakes'    => $item->respon_nakes,
            'laporan_nakes'   => $item->laporan_nakes,
        ];
    }
}
