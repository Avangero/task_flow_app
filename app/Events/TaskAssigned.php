<?php

namespace App\Events;

use App\Models\Task;

readonly class TaskAssigned
{
    public function __construct(
        public Task $task,
        public ?int $previousAssigneeId
    ) {}
}
