<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function create(User $user): bool
    {
        return $user->role === 'superadmin';
    }

    public function update(User $user): bool
    {
        return $user->role === 'superadmin';
    }

    public function delete(User $user): bool
    {
        return $user->role === 'superadmin';
    }
}
