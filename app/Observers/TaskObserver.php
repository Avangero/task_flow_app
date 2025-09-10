<?php

namespace App\Observers;

use App\Enums\TaskStatus;
use App\Events\TaskAssigned;
use App\Events\TaskStatusChanged;
use App\Models\Task;

class TaskObserver
{
    public function updated(Task $task): void
    {
        if ($task->wasChanged('assigned_to')) {
            $previousAssigneeId = $task->getOriginal('assigned_to');
            event(new TaskAssigned($task->fresh(['project', 'assignee', 'author']), $previousAssigneeId));
        }

        if ($task->wasChanged('status')) {
            $oldStatus = $task->getOriginal('status');
            $newStatus = $task->status;

            $oldStatus = $oldStatus instanceof TaskStatus ? $oldStatus : TaskStatus::from($oldStatus);

            event(new TaskStatusChanged($task->fresh(['project', 'assignee', 'author']), $oldStatus, $newStatus));
        }
    }
}
