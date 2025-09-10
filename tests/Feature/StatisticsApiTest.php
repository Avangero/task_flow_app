<?php

use App\Enums\ProjectStatus;
use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\Project;
use App\Models\Role;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tymon\JWTAuth\Facades\JWTAuth;

uses(RefreshDatabase::class);

function seedRolesStats(): array
{
    $admin = Role::create(['slug' => 'admin', 'name' => 'Admin', 'is_active' => true]);
    $manager = Role::create(['slug' => 'manager', 'name' => 'Manager', 'is_active' => true]);
    $user = Role::create(['slug' => 'user', 'name' => 'User', 'is_active' => true]);

    return [$admin, $manager, $user];
}

test('GET /api/statistics возвращает корректные агрегаты', function () {
    [$adminRole, $managerRole, $userRole] = seedRolesStats();
    $userA = User::factory()->create(['email' => 'a@example.com', 'password' => bcrypt('password123'), 'role_id' => $managerRole->id]);
    $userB = User::factory()->create(['email' => 'b@example.com', 'password' => bcrypt('password123'), 'role_id' => $userRole->id]);
    $token = JWTAuth::fromUser($userA);

    // Projects
    $p1 = Project::create(['name' => 'P1', 'status' => ProjectStatus::ACTIVE->value, 'created_by' => $userA->id]);
    $p2 = Project::create(['name' => 'P2', 'status' => ProjectStatus::COMPLETED->value, 'created_by' => $userB->id]);

    Task::create(['title' => 'T1', 'status' => TaskStatus::PENDING->value, 'priority' => TaskPriority::LOW->value, 'project_id' => $p1->id, 'created_by' => $userA->id]);
    Task::create(['title' => 'T2', 'status' => TaskStatus::IN_PROGRESS->value, 'priority' => TaskPriority::MEDIUM->value, 'project_id' => $p1->id, 'created_by' => $userA->id, 'due_date' => now()->subDay()]); // overdue
    Task::create(['title' => 'T3', 'status' => TaskStatus::COMPLETED->value, 'priority' => TaskPriority::HIGH->value, 'project_id' => $p2->id, 'created_by' => $userB->id]);

    $this->withHeaders(['Authorization' => 'Bearer ' . $token])
        ->getJson('/api/statistics')
        ->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'data' => [
                'statistics' => [
                    'total_projects',
                    'total_tasks',
                    'tasks_by_status' => [
                        TaskStatus::PENDING->value,
                        TaskStatus::IN_PROGRESS->value,
                        TaskStatus::COMPLETED->value,
                    ],
                    'overdue_tasks',
                    'top_users' => [
                        '*' => ['id', 'first_name', 'last_name', 'email', 'tasks_count'],
                    ],
                ],
            ],
        ])
        ->assertJsonPath('data.statistics.total_projects', 2)
        ->assertJsonPath('data.statistics.total_tasks', 3)
        ->assertJsonPath('data.statistics.tasks_by_status.' . TaskStatus::PENDING->value, 1)
        ->assertJsonPath('data.statistics.tasks_by_status.' . TaskStatus::IN_PROGRESS->value, 1)
        ->assertJsonPath('data.statistics.tasks_by_status.' . TaskStatus::COMPLETED->value, 1)
        ->assertJsonPath('data.statistics.overdue_tasks', 1);
});
