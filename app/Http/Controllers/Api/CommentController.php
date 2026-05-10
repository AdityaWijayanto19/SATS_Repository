<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{
    /**
     * GET /api/comments?device_id=X
     */
    public function index(Request $request): JsonResponse
    {
        $request->validate(['device_id' => 'required|string']);

        $comments = Comment::with(['user:id,name', 'responder:id,name'])
            ->where('device_id', $request->device_id)
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(fn($c) => [
                'id'            => $c->id,
                'teks'          => $c->teks,
                'user_name'     => $c->user->name ?? 'Dokter',
                'waktu'         => $c->created_at->format('H:i'),
                'respon'        => $c->respon,
                'respon_nama'   => $c->responder?->name,
                'responWaktu'   => $c->responded_at?->format('H:i'),
            ]);

        return response()->json([
            'success' => true,
            'data'    => $comments,
        ]);
    }

    /**
     * POST /api/comments
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'device_id' => 'required|string|exists:devices,device_id',
            'teks'      => 'required|string',
        ]);

        $comment = Comment::create([
            'device_id' => $request->device_id,
            'user_id'   => Auth::id(),
            'teks'      => $request->teks,
        ]);

        $comment->load('user:id,name');

        return response()->json([
            'success' => true,
            'data'    => [
                'id'        => $comment->id,
                'teks'      => $comment->teks,
                'user_name' => $comment->user->name ?? 'Dokter',
                'waktu'     => $comment->created_at->format('H:i'),
                'respon'    => null,
                'responWaktu' => null,
            ],
        ], 201);
    }

    /**
     * PATCH /api/comments/{comment}/respond
     */
    public function respond(Request $request, Comment $comment): JsonResponse
    {
        $request->validate(['respon' => 'required|string']);

        $comment->update([
            'respon'        => $request->respon,
            'responded_by'  => Auth::id(),
            'responded_at'  => now(),
        ]);

        $comment->load('responder:id,name');

        return response()->json([
            'success' => true,
            'data'    => [
                'id'          => $comment->id,
                'respon'      => $comment->respon,
                'respon_nama' => $comment->responder->name ?? 'Nakes',
                'responWaktu' => $comment->responded_at->format('H:i'),
            ],
        ]);
    }
}
