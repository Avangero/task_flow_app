<?php

namespace App\Repositories\Project;

use App\Models\Project;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ProjectRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    public function findById(int $id): ?Project;

    public function create(array $attributes): Project;

    public function update(Project $project, array $attributes): Project;

    public function delete(Project $project): void;

    public function countAll(): int;
}
