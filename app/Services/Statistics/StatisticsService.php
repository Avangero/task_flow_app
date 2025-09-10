<?php

namespace App\Services\Statistics;

use App\Models\User;
use App\Repositories\Statistics\StatisticsRepositoryInterface;

class StatisticsService implements StatisticsServiceInterface
{
    public function __construct(private readonly StatisticsRepositoryInterface $repository) {}

    public function getSummary(): array
    {
        $totalProjects = $this->repository->countProjects();
        $totalTasks = $this->repository->countTasks();
        $tasksByStatus = $this->repository->countTasksByStatus();
        $overdueTasks = $this->repository->countOverdueTasks();

        $top = $this->repository->topActiveUsers(5);
        $userIds = array_map(fn ($r) => $r['user_id'], $top);
        $users = User::query()->whereIn('id', $userIds)->get()->keyBy('id');

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

        return [
            'total_projects' => $totalProjects,
            'total_tasks' => $totalTasks,
            'tasks_by_status' => $tasksByStatus,
            'overdue_tasks' => $overdueTasks,
            'top_users' => $topUsers,
        ];
    }
}
