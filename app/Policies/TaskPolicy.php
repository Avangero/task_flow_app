<?php

namespace App\Policies;

use App\Models\Task;
use App\Models\User;

class TaskPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->isManager() || $user->isUser();
    }

    public function view(User $user, Task $task): bool
    {
        return $user->isAdmin() || $task->assigned_to === $user->id || $task->created_by === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->isManager();
    }

    public function update(User $user, Task $task): bool
    {
        if ($user->isAdmin() || $task->created_by === $user->id) {
            return true;
        }

        return $user->isUser() && $task->assigned_to === $user->id;
    }

    public function delete(User $user, Task $task): bool
    {
        return $user->isAdmin() || $task->created_by === $user->id;
    }
}
