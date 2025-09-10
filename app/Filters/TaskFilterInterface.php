<?php

namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;

interface TaskFilterInterface
{
    public function apply(Builder $query, array $filters): void;

    public function applySorting(Builder $query, array $filters): void;
}
