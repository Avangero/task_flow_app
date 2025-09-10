<?php

namespace App\Repositories\Statistics;

use App\Enums\TaskStatus;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Support\Facades\DB;

class StatisticsRepository implements StatisticsRepositoryInterface
{
    public function countProjects(): int
    {
        return Project::query()->count();
    }

    public function countTasks(): int
    {
        return Task::query()->count();
    }

    public function countTasksByStatus(): array
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

    public function countOverdueTasks(): int
    {
        return Task::query()
            ->whereIn('status', [TaskStatus::PENDING->value, TaskStatus::IN_PROGRESS->value])
            ->whereNotNull('due_date')
            ->where('due_date', '<', now())
            ->count();
    }

    public function topActiveUsers(int $limit = 5): array
    {
        $rows = DB::table('tasks')
            ->select('created_by as user_id', DB::raw('COUNT(*) as tasks_count'))
            ->groupBy('created_by')
            ->orderByDesc('tasks_count')
            ->limit($limit)
            ->get();

        return collect($rows)->map(fn ($r) => ['user_id' => (int) $r->user_id, 'tasks_count' => (int) $r->tasks_count])->all();
    }
}
