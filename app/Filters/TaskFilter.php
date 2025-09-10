<?php

namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;

class TaskFilter implements TaskFilterInterface
{
    public function apply(Builder $query, array $filters): void
    {
        $filterMappings = [
            'status' => 'status',
            'priority' => 'priority',
            'project_id' => 'project_id',
            'assigned_to' => 'assigned_to',
        ];

        foreach ($filterMappings as $filterKey => $column) {
            if (isset($filters[$filterKey])) {
                $value = $this->prepareFilterValue($filterKey, $filters[$filterKey]);
                $query->where($column, $value);
            }
        }
    }

    public function applySorting(Builder $query, array $filters): void
    {
        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortDirection = strtolower($filters['sort_direction'] ?? 'desc');

        $allowedSortColumns = ['created_at', 'due_date', 'title', 'priority'];
        $allowedDirections = ['asc', 'desc'];

        if (! in_array($sortBy, $allowedSortColumns)) {
            $sortBy = 'created_at';
        }

        if (! in_array($sortDirection, $allowedDirections)) {
            $sortDirection = 'desc';
        }

        $query->orderBy($sortBy, $sortDirection);
    }

    protected function prepareFilterValue(string $filterKey, $value): mixed
    {
        $integerFilters = ['project_id', 'assigned_to'];

        return in_array($filterKey, $integerFilters) ? (int) $value : $value;
    }
}
