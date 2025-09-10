<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->isManager();
    }

    public function view(User $user, User $target): bool
    {
        return $user->isAdmin() || $user->isManager() || $user->id === $target->id;
    }

    public function update(User $user, User $target): bool
    {
        return $user->isAdmin() || $user->id === $target->id;
    }
}
