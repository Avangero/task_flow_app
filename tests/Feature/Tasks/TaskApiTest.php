<?php

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\Project;
use App\Models\Role;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tymon\JWTAuth\Facades\JWTAuth;

uses(RefreshDatabase::class);

function seedRolesTasks(): array
{
    $admin = Role::create(['slug' => 'admin', 'name' => 'Admin', 'is_active' => true]);
    $manager = Role::create(['slug' => 'manager', 'name' => 'Manager', 'is_active' => true]);
    $user = Role::create(['slug' => 'user', 'name' => 'User', 'is_active' => true]);

    return [$admin, $manager, $user];
}

function createTaskUserWithRole(Role $role, string $email): User
{
    return User::factory()->create([
        'email' => $email,
        'password' => bcrypt('password123'),
        'role_id' => $role->id,
    ]);
}

test('список задач с фильтрами доступен всем авторизованным', function () {
    [$adminRole, $managerRole, $userRole] = seedRolesTasks();
    $user = createTaskUserWithRole($userRole, 'user@example.com');
    $token = JWTAuth::fromUser($user);

    $project = Project::create([
        'name' => 'P1',
        'status' => 'active',
        'created_by' => $user->id,
    ]);

    Task::create([
        'title' => 'T1',
        'status' => TaskStatus::PENDING->value,
        'priority' => TaskPriority::MEDIUM->value,
        'project_id' => $project->id,
        'created_by' => $user->id,
    ]);

    $this->withHeaders(['Authorization' => 'Bearer ' . $token])
        ->getJson('/api/tasks?status=pending&sort_by=created_at&sort_direction=desc')
        ->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'data' => [
                'tasks' => [
                    '*' => ['id', 'title', 'status', 'priority'],
                ],
                'meta' => ['current_page', 'last_page', 'per_page', 'total'],
            ],
        ]);
});

test('manager может создать задачу, user не может', function () {
    [$adminRole, $managerRole, $userRole] = seedRolesTasks();
    $manager = createTaskUserWithRole($managerRole, 'manager@example.com');
    $user = createTaskUserWithRole($userRole, 'user@example.com');
    $tokenManager = JWTAuth::fromUser($manager);
    $tokenUser = JWTAuth::fromUser($user);

    $project = Project::create([
        'name' => 'ForTask',
        'status' => 'active',
        'created_by' => $manager->id,
    ]);

    $payload = [
        'title' => 'New Task',
        'project_id' => $project->id,
    ];

    $this->withHeaders(['Authorization' => 'Bearer ' . $tokenManager])
        ->postJson('/api/tasks', $payload)
        ->assertStatus(201)
        ->assertJson(['success' => true, 'data' => ['task' => ['title' => 'New Task']]]);

    $this->withHeaders(['Authorization' => 'Bearer ' . $tokenUser])
        ->postJson('/api/tasks', $payload)
        ->assertStatus(403);
});

test('назначенный исполнитель может обновить статус своей задачи; чужой - нет', function () {
    [$adminRole, $managerRole, $userRole] = seedRolesTasks();
    $author = createTaskUserWithRole($managerRole, 'author@example.com');
    $assignee = createTaskUserWithRole($userRole, 'assignee@example.com');
    $other = createTaskUserWithRole($userRole, 'other@example.com');

    $project = Project::create([
        'name' => 'P2',
        'status' => 'active',
        'created_by' => $author->id,
    ]);

    $task = Task::create([
        'title' => 'Mine',
        'status' => TaskStatus::PENDING->value,
        'priority' => TaskPriority::LOW->value,
        'project_id' => $project->id,
        'assigned_to' => $assignee->id,
        'created_by' => $author->id,
    ]);

    $tokenAssignee = JWTAuth::fromUser($assignee);
    $tokenOther = JWTAuth::fromUser($other);

    $this->withHeaders(['Authorization' => 'Bearer ' . $tokenAssignee])
        ->putJson('/api/tasks/' . $task->id, ['status' => TaskStatus::IN_PROGRESS->value])
        ->assertStatus(200)
        ->assertJson(['success' => true, 'data' => ['task' => ['status' => TaskStatus::IN_PROGRESS->value]]]);

    $this->withHeaders(['Authorization' => 'Bearer ' . $tokenOther])
        ->putJson('/api/tasks/' . $task->id, ['status' => TaskStatus::COMPLETED->value])
        ->assertStatus(403);
});

test('автор может просмотреть задачу', function () {
    [$adminRole, $managerRole, $userRole] = seedRolesTasks();
    $author = createTaskUserWithRole($userRole, 'author-view@example.com');
    $token = JWTAuth::fromUser($author);

    $project = Project::create([
        'name' => 'P-view',
        'status' => 'active',
        'created_by' => $author->id,
    ]);

    $task = Task::create([
        'title' => 'See me',
        'status' => TaskStatus::PENDING->value,
        'priority' => TaskPriority::MEDIUM->value,
        'project_id' => $project->id,
        'created_by' => $author->id,
    ]);

    $this->getJson('/api/tasks/' . $task->id, ['Authorization' => 'Bearer ' . $token])
        ->assertStatus(200)
        ->assertJson(['success' => true, 'data' => ['task' => ['id' => $task->id]]]);
});

test('автор может удалить задачу, admin может удалить любую', function () {
    [$adminRole, $managerRole, $userRole] = seedRolesTasks();
    $author = createTaskUserWithRole($userRole, 'author3@example.com');
    $admin = createTaskUserWithRole($adminRole, 'admin3@example.com');

    $project = Project::create([
        'name' => 'P3',
        'status' => 'active',
        'created_by' => $author->id,
    ]);

    $task = Task::create([
        'title' => 'ToDelete',
        'status' => TaskStatus::PENDING->value,
        'priority' => TaskPriority::MEDIUM->value,
        'project_id' => $project->id,
        'created_by' => $author->id,
    ]);

    $tokenAuthor = JWTAuth::fromUser($author);
    $tokenAdmin = JWTAuth::fromUser($admin);

    $this->withHeaders(['Authorization' => 'Bearer ' . $tokenAuthor])
        ->deleteJson('/api/tasks/' . $task->id)
        ->assertStatus(200);

    $task2 = Task::create([
        'title' => 'Another',
        'status' => TaskStatus::PENDING->value,
        'priority' => TaskPriority::LOW->value,
        'project_id' => $project->id,
        'created_by' => $admin->id,
    ]);

    $this->withHeaders(['Authorization' => 'Bearer ' . $tokenAdmin])
        ->deleteJson('/api/tasks/' . $task2->id)
        ->assertStatus(200);
});

test('валидация при создании/обновлении задачи', function () {
    [$adminRole, $managerRole, $userRole] = seedRolesTasks();
    $manager = createTaskUserWithRole($managerRole, 'manager4@example.com');
    $token = JWTAuth::fromUser($manager);

    $this->withHeaders(['Authorization' => 'Bearer ' . $token])
        ->postJson('/api/tasks', ['title' => ''])
        ->assertStatus(422)
        ->assertJson(['success' => false]);

    $project = Project::create([
        'name' => 'P4',
        'status' => 'active',
        'created_by' => $manager->id,
    ]);

    $task = Task::create([
        'title' => 'Valid',
        'status' => TaskStatus::PENDING->value,
        'priority' => TaskPriority::LOW->value,
        'project_id' => $project->id,
        'created_by' => $manager->id,
    ]);

    $this->withHeaders(['Authorization' => 'Bearer ' . $token])
        ->putJson('/api/tasks/' . $task->id, ['status' => 'wrong'])
        ->assertStatus(422)
        ->assertJson(['success' => false]);
});

test('сортировка задач работает корректно', function () {
    [$adminRole, $managerRole, $userRole] = seedRolesTasks();
    $user = createTaskUserWithRole($userRole, 'user-sort@example.com');
    $token = JWTAuth::fromUser($user);

    $project = Project::create([
        'name' => 'SortProject',
        'status' => 'active',
        'created_by' => $user->id,
    ]);

    Task::create([
        'title' => 'Task A',
        'status' => TaskStatus::PENDING->value,
        'priority' => TaskPriority::HIGH->value,
        'project_id' => $project->id,
        'created_by' => $user->id,
        'due_date' => now()->addDays(3),
    ]);

    Task::create([
        'title' => 'Task B',
        'status' => TaskStatus::PENDING->value,
        'priority' => TaskPriority::LOW->value,
        'project_id' => $project->id,
        'created_by' => $user->id,
        'due_date' => now()->addDays(1),
    ]);

    $this->withHeaders(['Authorization' => 'Bearer ' . $token])
        ->getJson('/api/tasks?sort_by=priority&sort_direction=asc')
        ->assertStatus(200)
        ->assertJsonPath('data.tasks.0.priority', 'high');

    $this->withHeaders(['Authorization' => 'Bearer ' . $token])
        ->getJson('/api/tasks?sort_by=due_date&sort_direction=desc')
        ->assertStatus(200)
        ->assertJsonPath('data.tasks.0.title', 'Task A');
});
