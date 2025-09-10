<?php

namespace App\Listeners;

use App\Events\TaskStatusChanged;
use App\Mail\TaskStatusChangedMail;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;

class SendTaskStatusChangedEmail implements ShouldQueue
{
    use InteractsWithQueue;

    public bool $afterCommit = true;

    public function handle(TaskStatusChanged $event): void
    {
        $task = $event->task;
        $emails = [];

        if ($task->assignee && $task->assignee->email) {
            $emails[] = $task->assignee->email;
        }

        if ($task->author && $task->author->email) {
            $emails[] = $task->author->email;
        }

        $emails = array_values(array_unique($emails));

        foreach ($emails as $email) {
            $recipient = User::where('email', $email)->first();
            Mail::to($email)->queue(new TaskStatusChangedMail($task, $event->oldStatus, $event->newStatus, $recipient));
        }
    }
}
