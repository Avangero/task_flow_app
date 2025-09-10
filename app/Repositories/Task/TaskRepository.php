<?php

namespace App\Repositories\Task;

use App\Filters\TaskFilterInterface;
use App\Models\Task;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class TaskRepository implements TaskRepositoryInterface
{
    protected TaskFilterInterface $taskFilter;

    public function __construct(TaskFilterInterface $taskFilter)
    {
        $this->taskFilter = $taskFilter;
    }

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Task::query()->with(['project', 'assignee', 'author']);

        $this->taskFilter->apply($query, $filters);
        $this->taskFilter->applySorting($query, $filters);

        return $query->paginate($perPage);
    }

    public function findById(int $id): ?Task
    {
        return Task::with(['project', 'assignee', 'author'])->find($id);
    }

    public function create(array $attributes): Task
    {
        return Task::create($attributes);
    }

    public function update(Task $task, array $attributes): Task
    {
        $task->fill($attributes);
        $task->save();

        return $task->refresh();
    }

    public function delete(Task $task): void
    {
        $task->delete();
    }
}
