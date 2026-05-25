<?php

namespace App\Services;

use App\Models\User;

class ProfileService
{
    /**
     * Data untuk halaman edit profil.
     */
    public function getProfileData(User $user): array
    {
        $avatars = $this->getAvatarsForRole($user->role);

        return compact('user', 'avatars');
    }

    /**
     * Update profil user.
     */
    public function updateProfile(User $user, array $data): void
    {
        if (empty($data['password'])) {
            unset($data['password']);
        }

        $user->update($data);
    }

    /**
     * Daftar avatar berdasarkan role.
     */
    public function getAvatarsForRole(string $role): array
    {
        return [
            "assets/photo_profile/{$role}_1.png",
            "assets/photo_profile/{$role}_2.png",
            "assets/photo_profile/{$role}_3.png",
            "assets/photo_profile/{$role}_4.png",
        ];
    }
}
