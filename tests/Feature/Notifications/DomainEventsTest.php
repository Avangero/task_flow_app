<?php

use App\Enums\ProjectStatus;
use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Events\ProjectStatusChanged;
use App\Events\TaskAssigned;
use App\Events\TaskStatusChanged;
use App\Models\Project;
use App\Models\Role;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;

uses(RefreshDatabase::class);

function seedRolesDomainEvents(): array
{
    $admin = Role::create(['slug' => 'admin', 'name' => 'Admin', 'is_active' => true]);
    $manager = Role::create(['slug' => 'manager', 'name' => 'Manager', 'is_active' => true]);
    $user = Role::create(['slug' => 'user', 'name' => 'User', 'is_active' => true]);

    return [$admin, $manager, $user];
}

test('событие TaskAssigned диспатчится при смене исполнителя', function () {
    [, $managerRole, $userRole] = seedRolesDomainEvents();
    $author = User::factory()->create(['email' => 'author@example.com', 'role_id' => $managerRole->id]);
    $assignee = User::factory()->create(['email' => 'assignee@example.com', 'role_id' => $userRole->id]);

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
        'created_by' => $author->id,
    ]);

    Event::fake([TaskAssigned::class]);

    $task->update(['assigned_to' => $assignee->id]);

    Event::assertDispatched(TaskAssigned::class, function (TaskAssigned $event) use ($task, $assignee) {
        expect($event->task->id)->toBe($task->id)
            ->and($event->previousAssigneeId)->toBeNull()
            ->and($event->task->assignee?->id)->toBe($assignee->id);

        return true;
    });
});

test('событие TaskStatusChanged диспатчится при смене статуса задачи', function () {
    [, $managerRole, $userRole] = seedRolesDomainEvents();
    $author = User::factory()->create(['email' => 'author2@example.com', 'role_id' => $managerRole->id]);
    $assignee = User::factory()->create(['email' => 'assignee2@example.com', 'role_id' => $userRole->id]);

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

    Event::fake([TaskStatusChanged::class]);

    $task->update(['status' => TaskStatus::IN_PROGRESS->value]);

    Event::assertDispatched(TaskStatusChanged::class, function (TaskStatusChanged $event) use ($task) {
        expect($event->task->id)->toBe($task->id)
            ->and($event->oldStatus)->toBe(TaskStatus::PENDING)
            ->and($event->newStatus)->toBe(TaskStatus::IN_PROGRESS);

        return true;
    });
});

test('событие ProjectStatusChanged диспатчится при смене статуса проекта', function () {
    [, $managerRole] = seedRolesDomainEvents();
    $author = User::factory()->create(['email' => 'author3@example.com', 'role_id' => $managerRole->id]);

    $project = Project::create([
        'name' => 'P3',
        'status' => ProjectStatus::ACTIVE->value,
        'created_by' => $author->id,
    ]);

    Event::fake([ProjectStatusChanged::class]);

    $project->update(['status' => ProjectStatus::COMPLETED->value]);

    Event::assertDispatched(ProjectStatusChanged::class, function (ProjectStatusChanged $event) use ($project) {
        expect($event->project->id)->toBe($project->id)
            ->and($event->oldStatus)->toBe(ProjectStatus::ACTIVE)
            ->and($event->newStatus)->toBe(ProjectStatus::COMPLETED);

        return true;
    });
});
