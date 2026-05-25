<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\ActivityLog;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function __construct(
        protected readonly UserService $userService
    ) {}


    public function store(CreateUserRequest $request): RedirectResponse
    {
        $user = $this->userService->createUser($request->validated());

        $admin = Auth::user();
        ActivityLog::log('user.added', "Admin {$admin->name} menambahkan user {$user->name}", $admin->name, $admin->role);

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

    public function destroy(User $user): RedirectResponse
    {
        $userName = $user->name;
        $this->userService->deleteUser($user);

        $admin = Auth::user();
        ActivityLog::log('user.deleted', "Admin {$admin->name} menghapus user {$userName}", $admin->name, $admin->role);

        return redirect()
            ->route('superadmin.manajemen-user')
            ->with('success', 'User berhasil dihapus.');
    }
}
