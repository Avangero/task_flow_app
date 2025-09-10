<?php

namespace App\Services\Task;

use App\Models\Task as TaskModel;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface TaskServiceInterface
{
    public function list(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    public function show(int $id): TaskModel;

    public function store(array $attributes): TaskModel;

    public function update(TaskModel $task, array $attributes): TaskModel;

    public function delete(TaskModel $task): void;
}
