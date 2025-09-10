<?php

use App\Enums\ProjectStatus;
use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Events\ProjectStatusChanged;
use App\Events\TaskAssigned;
use App\Events\TaskStatusChanged;
use App\Listeners\SendProjectStatusChangedEmail;
use App\Listeners\SendTaskAssignedEmail;
use App\Listeners\SendTaskStatusChangedEmail;
use App\Mail\ProjectStatusChangedMail;
use App\Mail\TaskAssignedMail;
use App\Mail\TaskStatusChangedMail;
use App\Models\Project;
use App\Models\Role;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;

uses(Tests\TestCase::class, RefreshDatabase::class);

function seedRolesListeners(): array
{
    $admin = Role::create(['slug' => 'admin', 'name' => 'Admin', 'is_active' => true]);
    $manager = Role::create(['slug' => 'manager', 'name' => 'Manager', 'is_active' => true]);
    $user = Role::create(['slug' => 'user', 'name' => 'User', 'is_active' => true]);

    return [$admin, $manager, $user];
}

test('SendTaskAssignedEmail отправляет письмо назначенному пользователю', function () {
    [, $managerRole, $userRole] = seedRolesListeners();
    $author = User::factory()->create(['email' => 'authorL1@example.com', 'role_id' => $managerRole->id]);
    $assignee = User::factory()->create(['email' => 'assigneeL1@example.com', 'role_id' => $userRole->id]);

    $project = Project::create([
        'name' => 'P',
        'status' => ProjectStatus::ACTIVE->value,
        'created_by' => $author->id,
    ]);

    $task = Task::create([
        'title' => 'T',
        'status' => TaskStatus::PENDING->value,
        'priority' => TaskPriority::LOW->value,
        'project_id' => $project->id,
        'assigned_to' => $assignee->id,
        'created_by' => $author->id,
    ]);

    Mail::fake();

    $listener = new SendTaskAssignedEmail;
    $listener->handle(new TaskAssigned($task->fresh(['assignee', 'author', 'project']), null));

    Mail::assertQueued(TaskAssignedMail::class, function (TaskAssignedMail $mailable) use ($assignee) {
        return $mailable->hasTo($assignee->email);
    });
});

test('SendTaskStatusChangedEmail отправляет письма автору и исполнителю', function () {
    [, $managerRole, $userRole] = seedRolesListeners();
    $author = User::factory()->create(['email' => 'authorL2@example.com', 'role_id' => $managerRole->id]);
    $assignee = User::factory()->create(['email' => 'assigneeL2@example.com', 'role_id' => $userRole->id]);

    $project = Project::create([
        'name' => 'P2',
        'status' => ProjectStatus::ACTIVE->value,
        'created_by' => $author->id,
    ]);

    $task = Task::create([
        'title' => 'T2',
        'status' => TaskStatus::PENDING->value,
        'priority' => TaskPriority::LOW->value,
        'project_id' => $project->id,
        'assigned_to' => $assignee->id,
        'created_by' => $author->id,
    ]);

    Mail::fake();

    $listener = new SendTaskStatusChangedEmail;
    $listener->handle(new TaskStatusChanged($task->fresh(['assignee', 'author', 'project']), TaskStatus::PENDING, TaskStatus::IN_PROGRESS));

    Mail::assertQueued(TaskStatusChangedMail::class, function (TaskStatusChangedMail $mailable) use ($assignee, $author) {
        return $mailable->hasTo($assignee->email) || $mailable->hasTo($author->email);
    });
});

test('SendProjectStatusChangedEmail отправляет письмо автору проекта', function () {
    [, $managerRole] = seedRolesListeners();
    $author = User::factory()->create(['email' => 'authorL3@example.com', 'role_id' => $managerRole->id]);

    $project = Project::create([
        'name' => 'P3',
        'status' => ProjectStatus::ACTIVE->value,
        'created_by' => $author->id,
    ]);

    Mail::fake();

    $listener = new SendProjectStatusChangedEmail;
    $listener->handle(new ProjectStatusChanged($project->fresh(['author']), ProjectStatus::ACTIVE, ProjectStatus::COMPLETED));

    Mail::assertQueued(ProjectStatusChangedMail::class, function (ProjectStatusChangedMail $mailable) use ($author) {
        return $mailable->hasTo($author->email);
    });
});
