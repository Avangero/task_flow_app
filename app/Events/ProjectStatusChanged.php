<?php

namespace App\Events;

use App\Enums\ProjectStatus;
use App\Models\Project;

readonly class ProjectStatusChanged
{
    public function __construct(
        public Project $project,
        public ProjectStatus $oldStatus,
        public ProjectStatus $newStatus
    ) {}
}
