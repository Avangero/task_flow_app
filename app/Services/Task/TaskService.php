<?php

namespace App\Services\Task;

use App\Exceptions\ApiException;
use App\Models\Task as TaskModel;
use App\Repositories\Task\TaskRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class TaskService implements TaskServiceInterface
{
    public function __construct(protected readonly TaskRepositoryInterface $repository) {}

    public function list(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->paginate($filters, $perPage);
    }

    /**
     * @throws ApiException
     */
    public function show(int $id): TaskModel
    {
        $task = $this->repository->findById($id);
        if (! $task) {
            throw new ApiException(__('api.http.not_found'), 404);
        }

        return $task;
    }

    public function store(array $attributes): TaskModel
    {
        return $this->repository->create($attributes);
    }

    public function update(TaskModel $task, array $attributes): TaskModel
    {
        return $this->repository->update($task, $attributes);
    }

    public function delete(TaskModel $task): void
    {
        $this->repository->delete($task);
    }
}
