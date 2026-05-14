<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreInstructionRequest;
use App\Http\Requests\StoreInstructionReportRequest;
use App\Http\Requests\CompleteInstructionRequest;
use App\Http\Requests\UpdateInstructionRequest;
use App\Models\Instruction;
use App\Services\InstructionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class InstructionController extends Controller
{
    public function __construct(protected InstructionService $service) {}

    public function index(Request $request)
    {
        try {
            $request->validate(['device_id' => 'required|string']);
            $instructions = $this->service->getInstructions($request->device_id);
            return response()->json(['success' => true, 'data' => $instructions]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Instruction index error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan'
            ], 500);
        }
    }

    public function store(StoreInstructionRequest $request)
    {
        try {
            $data = $request->validated();

            $instruction = $this->service->storeInstruction($data);
            return response()->json([
                'success' => true,
                'data' => $instruction,
                'message' => 'Instruksi berhasil dibuat'
            ], 201);
        } catch (\Exception $e) {
            Log::error('Instruction store error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage() ?: 'Gagal membuat instruksi'
            ], 500);
        }
    }

    public function storeReport(StoreInstructionReportRequest $request)
    {
        try {
            $report = $this->service->storeReport($request->validated());
            return response()->json([
                'success' => true,
                'data' => $report,
                'message' => 'Laporan berhasil disubmit'
            ], 201);
        } catch (\Exception $e) {
            Log::error('Instruction report store error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage() ?: 'Gagal submit laporan'
            ], 500);
        }
    }

    public function complete(CompleteInstructionRequest $request, Instruction $instruction)
    {
        try {
            // Support both field names for backward compatibility
            $respon = $request->respon_nakes ?? $request->respon ?? '';

            $updated = $this->service->completeInstruction($instruction, $respon);
            return response()->json([
                'success' => true,
                'data' => $updated,
                'message' => 'Instruksi berhasil diselesaikan'
            ]);
        } catch (\Exception $e) {
            Log::warning('Instruction complete warning: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    public function update(UpdateInstructionRequest $request, Instruction $instruction)
    {
        try {
            $updated = $this->service->updateInstruction($instruction, $request->validated());
            return response()->json([
                'success' => true,
                'data' => $updated,
                'message' => 'Instruksi berhasil diupdate'
            ]);
        } catch (\Exception $e) {
            Log::error('Instruction update error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage() ?: 'Gagal update instruksi'
            ], 500);
        }
    }
}
