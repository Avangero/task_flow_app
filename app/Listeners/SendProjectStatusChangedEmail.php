<?php

namespace App\Listeners;

use App\Events\ProjectStatusChanged;
use App\Mail\ProjectStatusChangedMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;

class SendProjectStatusChangedEmail implements ShouldQueue
{
    use InteractsWithQueue;

    public bool $afterCommit = true;

    public function handle(ProjectStatusChanged $event): void
    {
        $project = $event->project;
        if (! ($project->author && $project->author->email)) {
            return;
        }

        Mail::to($project->author->email)->queue(
            new ProjectStatusChangedMail($project, $event->oldStatus, $event->newStatus)
        );
    }
}
