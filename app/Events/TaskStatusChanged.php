<?php

namespace App\Events;

use App\Enums\TaskStatus;
use App\Models\Task;

readonly class TaskStatusChanged
{
    public function __construct(
        public Task $task,
        public TaskStatus $oldStatus,
        public TaskStatus $newStatus
    ) {}
}
