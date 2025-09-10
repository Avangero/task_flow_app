<?php

namespace App\Repositories\Project;

use App\Models\Project;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ProjectRepository implements ProjectRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Project::query()->with(['author']);

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->paginate($perPage);
    }

    public function findById(int $id): ?Project
    {
        return Project::with(['author'])->find($id);
    }

    public function create(array $attributes): Project
    {
        return Project::create($attributes);
    }

    public function update(Project $project, array $attributes): Project
    {
        $project->fill($attributes);
        $project->save();

        return $project->refresh();
    }

    public function delete(Project $project): void
    {
        $project->delete();
    }
}
