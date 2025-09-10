<?php

namespace App\Repositories\Statistics;

interface StatisticsRepositoryInterface
{
    public function countProjects(): int;

    public function countTasks(): int;

    public function countTasksByStatus(): array;

    public function countOverdueTasks(): int;

    public function topActiveUsers(int $limit = 5): array;
}
