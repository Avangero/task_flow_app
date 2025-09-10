<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class StatisticsResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'total_projects' => $this->resource['total_projects'] ?? 0,
            'total_tasks' => $this->resource['total_tasks'] ?? 0,
            'tasks_by_status' => $this->resource['tasks_by_status'] ?? [],
            'overdue_tasks' => $this->resource['overdue_tasks'] ?? 0,
            'top_users' => $this->resource['top_users'] ?? [],
        ];
    }
}
