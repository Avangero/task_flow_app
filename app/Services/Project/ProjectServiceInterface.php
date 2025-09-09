<?php

namespace App\Services\Project;

use App\Models\Project as ProjectModel;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ProjectServiceInterface
{
    public function list(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    public function show(int $id): ProjectModel;

    public function store(array $attributes): ProjectModel;

    public function update(ProjectModel $project, array $attributes): ProjectModel;

    public function delete(ProjectModel $project): void;
}
