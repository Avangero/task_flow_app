<?php

namespace App\Services\Statistics;

use App\Models\User;
use App\Repositories\Project\ProjectRepositoryInterface;
use App\Repositories\Task\TaskRepositoryInterface;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection as SupportCollection;
use Illuminate\Support\Enumerable;
use Illuminate\Support\Facades\Cache;

readonly class StatisticsService implements StatisticsServiceInterface
{
    public function __construct(
        protected ProjectRepositoryInterface $projectRepository,
        protected TaskRepositoryInterface $taskRepository,
    ) {}

    public function getSummary(): array
    {
        $key = 'statistics:summary';
        $ttl = (int) config('cache.ttl', 3600);

        return Cache::remember($key, $ttl, function () {
            $totalProjects = $this->projectRepository->countAll();
            $totalTasks = $this->taskRepository->countAll();
            $tasksByStatus = $this->taskRepository->countByStatus();
            $overdueTasks = $this->taskRepository->countOverdue();

            $top = $this->taskRepository->topCreators(5);
            $userIds = array_map(fn ($r) => $r['user_id'], $top);
            $users = User::query()->whereIn('id', $userIds)->get()->keyBy('id');

            $topUsers = $this->getTopUsers($top, $users);

            return [
                'total_projects' => $totalProjects,
                'total_tasks' => $totalTasks,
                'tasks_by_status' => $tasksByStatus,
                'overdue_tasks' => $overdueTasks,
                'top_users' => $topUsers,
            ];
        });
    }

    protected function getTopUsers(array $top, EloquentCollection|Enumerable|SupportCollection $users): array
    {
        $topUsers = [];

        foreach ($top as $row) {
            $user = $users->get($row['user_id']);

            if (! $user) {
                continue;
            }

            $topUsers[] = [
                'id' => (int) $user->id,
                'first_name' => (string) $user->first_name,
                'last_name' => (string) $user->last_name,
                'email' => $user->email,
                'tasks_count' => (int) $row['tasks_count'],
            ];
        }

        return $topUsers;
    }
}
