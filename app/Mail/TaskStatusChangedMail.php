<?php

namespace App\Mail;

use App\Enums\TaskStatus;
use App\Models\Task;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TaskStatusChangedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public readonly Task $task, public readonly TaskStatus $oldStatus, public readonly TaskStatus $newStatus, public readonly ?User $recipient = null) {}

    public function build(): self
    {
        return $this->subject(__('mail.task_status_changed.subject', ['title' => $this->task->title]))
            ->view('emails.task_status_changed')
            ->with([
                'task' => $this->task,
                'oldStatus' => $this->oldStatus,
                'newStatus' => $this->newStatus,
                'recipient' => $this->recipient,
            ]);
    }
}
