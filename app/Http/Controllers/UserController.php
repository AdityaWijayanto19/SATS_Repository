<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\ActivityLog;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function __construct(
        protected readonly UserService $userService
    ) {}


    public function store(CreateUserRequest $request)
    {
        $user = $this->userService->createUser($request->validated());

        $admin = Auth::user();
        ActivityLog::log('user.added', "Admin {$admin->name} menambahkan user {$user->name}", $admin->name, $admin->role);

        // AJAX response
        if ($request->expectsJson() || $request->header('Accept') === 'application/json') {
            return response()->json([
                'success' => true,
                'message' => 'User berhasil dibuat.',
                'data' => [
                    'id' => $user->id,
                    'nama' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'role_label' => match ($user->role) {
                        'superadmin' => 'Super Admin',
                        'dokter' => 'Dokter',
                        'nakes' => 'Perawat',
                        default => $user->role,
                    },
                    'bergabung' => $user->created_at->format('d M Y'),
                ],
            ], 201);
        }

        return redirect()
            ->route('superadmin.manajemen-user')
            ->with('success', 'User berhasil dibuat.');
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $this->userService->updateUser(
            $user,
            $request->validated()
        );

        return redirect()
            ->route('superadmin.manajemen-user')
            ->with('success', 'User berhasil diperbarui.');
    }

    public function destroy(User $user, Request $request)
    {
        $userName = $user->name;
        $this->userService->deleteUser($user);

        $admin = Auth::user();
        ActivityLog::log('user.deleted', "Admin {$admin->name} menghapus user {$userName}", $admin->name, $admin->role);

        // AJAX response
        if ($request->expectsJson() || $request->header('Accept') === 'application/json') {
            return response()->json([
                'success' => true,
                'message' => 'User berhasil dihapus.',
            ]);
        }

        return redirect()
            ->route('superadmin.manajemen-user')
            ->with('success', 'User berhasil dihapus.');
    }
}
