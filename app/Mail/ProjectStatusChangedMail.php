<?php

namespace App\Mail;

use App\Enums\ProjectStatus;
use App\Models\Project;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ProjectStatusChangedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public readonly Project $project, public readonly ProjectStatus $oldStatus, public readonly ProjectStatus $newStatus) {}

    public function build(): self
    {
        return $this->subject(__('mail.project_status_changed.subject', ['name' => $this->project->name]))
            ->view('emails.project_status_changed')
            ->with([
                'project' => $this->project,
                'oldStatus' => $this->oldStatus,
                'newStatus' => $this->newStatus,
            ]);
    }
}
