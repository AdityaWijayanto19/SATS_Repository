<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    public function edit()
    {
        $user = Auth::user();
        $avatars = $this->getAvatarsForRole($user->role);

        return view('pages.profile.edit', compact('user', 'avatars'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'password' => ['nullable', 'confirmed', Password::min(8)],
            'photo' => 'nullable|string|max:500',
        ]);

        // Only update password if provided
        if (empty($validated['password'])) {
            unset($validated['password']);
        }

        $user->update($validated);

        return redirect()->route('profile.edit')->with('success', 'Profil berhasil diperbarui.');
    }

    private function getAvatarsForRole(string $role): array
    {
        return [
            "assets/photo_profile/{$role}_1.png",
            "assets/photo_profile/{$role}_2.png",
            "assets/photo_profile/{$role}_3.png",
            "assets/photo_profile/{$role}_4.png",
        ];
    }
}
