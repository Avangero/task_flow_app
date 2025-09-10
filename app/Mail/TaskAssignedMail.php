<?php

namespace App\Mail;

use App\Models\Task;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TaskAssignedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public readonly Task $task, public readonly ?User $previousAssignee) {}

    public function build(): self
    {
        return $this->subject(__('mail.task_assigned.subject', ['title' => $this->task->title]))
            ->view('emails.task_assigned')
            ->with([
                'task' => $this->task,
                'previousAssignee' => $this->previousAssignee,
            ]);
    }
}
