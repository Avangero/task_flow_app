<?php

use App\Enums\ProjectStatus;
use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\Project;
use App\Models\Role;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tymon\JWTAuth\Facades\JWTAuth;

uses(RefreshDatabase::class);

function seedRolesForStatisticsCache(): array
{
    $admin = Role::create(['slug' => 'admin', 'name' => 'Admin', 'is_active' => true]);
    $manager = Role::create(['slug' => 'manager', 'name' => 'Manager', 'is_active' => true]);
    $user = Role::create(['slug' => 'user', 'name' => 'User', 'is_active' => true]);

    return [$admin, $manager, $user];
}

test('кэш статистики: запись, чтение и инвалидация через observers', function () {
    Cache::flush();

    [, $managerRole] = seedRolesForStatisticsCache();
    $userA = User::factory()->create([
        'email' => 'cache@example.com',
        'password' => bcrypt('password123'),
        'role_id' => $managerRole->id,
    ]);

    $token = JWTAuth::fromUser($userA);

    $project = Project::create([
        'name' => 'P-Cache',
        'status' => ProjectStatus::ACTIVE->value,
        'created_by' => $userA->id,
    ]);

    Task::create([
        'title' => 'T1',
        'status' => TaskStatus::PENDING->value,
        'priority' => TaskPriority::LOW->value,
        'project_id' => $project->id,
        'created_by' => $userA->id,
    ]);
    Task::create([
        'title' => 'T2',
        'status' => TaskStatus::IN_PROGRESS->value,
        'priority' => TaskPriority::MEDIUM->value,
        'project_id' => $project->id,
        'created_by' => $userA->id,
    ]);

    $this->withHeaders(['Authorization' => 'Bearer ' . $token])
        ->getJson('/api/statistics')
        ->assertStatus(200)
        ->assertJsonPath('data.statistics.total_tasks', 2)
        ->assertJsonPath('data.statistics.tasks_by_status.' . TaskStatus::PENDING->value, 1);

    expect(Cache::has('statistics:summary'))->toBeTrue();

    Task::withoutEvents(function () use ($project, $userA) {
        Task::create([
            'title' => 'T3',
            'status' => TaskStatus::PENDING->value,
            'priority' => TaskPriority::LOW->value,
            'project_id' => $project->id,
            'created_by' => $userA->id,
        ]);
        Task::create([
            'title' => 'T4',
            'status' => TaskStatus::PENDING->value,
            'priority' => TaskPriority::LOW->value,
            'project_id' => $project->id,
            'created_by' => $userA->id,
        ]);
    });

    $this->withHeaders(['Authorization' => 'Bearer ' . $token])
        ->getJson('/api/statistics')
        ->assertStatus(200)
        ->assertJsonPath('data.statistics.total_tasks', 2)
        ->assertJsonPath('data.statistics.tasks_by_status.' . TaskStatus::PENDING->value, 1);

    Task::create([
        'title' => 'T5',
        'status' => TaskStatus::PENDING->value,
        'priority' => TaskPriority::LOW->value,
        'project_id' => $project->id,
        'created_by' => $userA->id,
    ]);

    $this->withHeaders(['Authorization' => 'Bearer ' . $token])
        ->getJson('/api/statistics')
        ->assertStatus(200)
        ->assertJsonPath('data.statistics.total_tasks', 5)
        ->assertJsonPath('data.statistics.tasks_by_status.' . TaskStatus::PENDING->value, 4);
});
