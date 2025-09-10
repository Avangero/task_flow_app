<?php

namespace App\Repositories\Task;

use App\Models\Task;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface TaskRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    public function findById(int $id): ?Task;

    public function create(array $attributes): Task;

    public function update(Task $task, array $attributes): Task;

    public function delete(Task $task): void;
}
