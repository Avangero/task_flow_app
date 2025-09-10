<?php

namespace App\Listeners;

use App\Events\TaskAssigned;
use App\Mail\TaskAssignedMail;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;

class SendTaskAssignedEmail implements ShouldQueue
{
    use InteractsWithQueue;

    public bool $afterCommit = true;

    public function handle(TaskAssigned $event): void
    {
        $task = $event->task;
        if (! ($task->assignee && $task->assignee->email)) {
            return;
        }

        $previousAssignee = $event->previousAssigneeId ? User::find($event->previousAssigneeId) : null;
        Mail::to($task->assignee->email)->queue(new TaskAssignedMail($task, $previousAssignee));
    }
}
