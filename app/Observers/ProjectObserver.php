<?php

namespace App\Observers;

use App\Enums\ProjectStatus;
use App\Events\ProjectStatusChanged;
use App\Models\Project;
use App\Traits\WithForgetCache;

class ProjectObserver
{
    use WithForgetCache;

    public function created(Project $project): void
    {
        $this->forgetCache('statistics:summary');
    }

    public function updated(Project $project): void
    {
        if ($project->wasChanged('status')) {
            $oldStatus = $project->getOriginal('status');
            $newStatus = $project->status;

            $oldStatus = $oldStatus instanceof ProjectStatus ? $oldStatus : ProjectStatus::from($oldStatus);

            event(new ProjectStatusChanged($project->fresh(['author']), $oldStatus, $newStatus));
        }

        $this->forgetCache('statistics:summary');
    }

    public function deleted(Project $project): void
    {
        $this->forgetCache('statistics:summary');
    }
}
