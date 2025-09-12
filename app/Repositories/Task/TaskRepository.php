<?php

namespace App\Repositories\Task;

use App\Enums\TaskStatus;
use App\Filters\TaskFilterInterface;
use App\Models\Task;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

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

    public function countAll(): int
    {
        return Task::query()->count();
    }

    public function countByStatus(): array
    {
        $rows = DB::table('tasks')
            ->select('status', DB::raw('COUNT(*) as aggregate_count'))
            ->groupBy('status')
            ->get();

        $result = [];

        foreach ($rows as $row) {
            $result[(string) $row->status] = (int) $row->aggregate_count;
        }

        foreach (TaskStatus::cases() as $status) {
            $key = $status->value;
            if (! array_key_exists($key, $result)) {
                $result[$key] = 0;
            }
        }

        return $result;
    }

    public function countOverdue(): int
    {
        return Task::query()
            ->whereIn('status', [TaskStatus::PENDING->value, TaskStatus::IN_PROGRESS->value])
            ->whereNotNull('due_date')
            ->where('due_date', '<', now())
            ->count();
    }

    public function topCreators(int $limit = 5): array
    {
        $rows = Task::query()
            ->select('created_by as user_id')
            ->selectRaw('COUNT(*) as tasks_count')
            ->groupBy('created_by')
            ->orderByDesc('tasks_count')
            ->limit($limit)
            ->get();

        return collect($rows)
            ->map(fn ($r) => ['user_id' => (int) $r->user_id, 'tasks_count' => (int) $r->tasks_count])
            ->all();
    }
}
