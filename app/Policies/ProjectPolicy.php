<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;

class ProjectPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->isManager() || $user->isUser();
    }

    public function view(User $user, Project $project): bool
    {
        return $user->isAdmin() || $user->isManager() || $user->isUser();
    }

    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->isManager();
    }

    public function update(User $user, Project $project): bool
    {
        return $user->isAdmin() || $project->created_by === $user->id;
    }

    public function delete(User $user, Project $project): bool
    {
        return $user->isAdmin() || $project->created_by === $user->id;
    }
}
