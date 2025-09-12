<?php

use App\Enums\ProjectStatus;
use App\Models\Project;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tymon\JWTAuth\Facades\JWTAuth;

uses(RefreshDatabase::class);

function seedRoles(): array
{
    $admin = Role::create(['slug' => 'admin', 'name' => 'Admin', 'is_active' => true]);
    $manager = Role::create(['slug' => 'manager', 'name' => 'Manager', 'is_active' => true]);
    $user = Role::create(['slug' => 'user', 'name' => 'User', 'is_active' => true]);

    return [$admin, $manager, $user];
}

function createUserWithRole(Role $role, string $email): User
{
    return User::factory()->create([
        'email' => $email,
        'password' => bcrypt('password123'),
        'role_id' => $role->id,
    ]);
}

test('список проектов доступен всем авторизованным', function () {
    [$adminRole, $managerRole, $userRole] = seedRoles();
    $user = createUserWithRole($userRole, 'user@example.com');
    $token = JWTAuth::fromUser($user);

    Project::create([
        'name' => 'P1',
        'description' => 'Desc',
        'status' => ProjectStatus::ACTIVE->value,
        'created_by' => $user->id,
    ]);

    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $token,
    ])->getJson('/api/projects');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'data' => [
                'projects' => [
                    '*' => ['id', 'name', 'status'],
                ],
                'meta' => ['current_page', 'last_page', 'per_page', 'total'],
            ],
        ]);
});

test('manager может создать проект, user не может', function () {
    [$adminRole, $managerRole, $userRole] = seedRoles();
    $manager = createUserWithRole($managerRole, 'manager@example.com');
    $user = createUserWithRole($userRole, 'user@example.com');
    $tokenManager = JWTAuth::fromUser($manager);
    $tokenUser = JWTAuth::fromUser($user);

    $payload = [
        'name' => 'New Project',
        'description' => 'Init',
    ];

    $this->withHeaders(['Authorization' => 'Bearer ' . $tokenManager])
        ->postJson('/api/projects', $payload)
        ->assertStatus(201)
        ->assertJson(['success' => true, 'data' => ['project' => ['name' => 'New Project']]]);

    $this->withHeaders(['Authorization' => 'Bearer ' . $tokenUser])
        ->postJson('/api/projects', $payload)
        ->assertStatus(403);
});

test('автор может обновить свой проект, чужой - нет', function () {
    [$adminRole, $managerRole, $userRole] = seedRoles();
    $author = createUserWithRole($userRole, 'author@example.com');
    $other = createUserWithRole($managerRole, 'other@example.com');

    $project = Project::create([
        'name' => 'Mine',
        'description' => null,
        'status' => ProjectStatus::ACTIVE->value,
        'created_by' => $author->id,
    ]);

    $tokenAuthor = JWTAuth::fromUser($author);
    $tokenOther = JWTAuth::fromUser($other);

    $this->withHeaders(['Authorization' => 'Bearer ' . $tokenAuthor])
        ->putJson('/api/projects/' . $project->id, ['name' => 'Updated'])
        ->assertStatus(200)
        ->assertJson(['success' => true, 'data' => ['project' => ['name' => 'Updated']]]);

    $this->withHeaders(['Authorization' => 'Bearer ' . $tokenOther])
        ->putJson('/api/projects/' . $project->id, ['name' => 'Hack'])
        ->assertStatus(403);
});

test('admin может обновить чужой проект', function () {
    [$adminRole, $managerRole, $userRole] = seedRoles();
    $author = createUserWithRole($userRole, 'author2@example.com');
    $admin = createUserWithRole($adminRole, 'admin@example.com');

    $project = Project::create([
        'name' => 'Someone',
        'description' => null,
        'status' => ProjectStatus::ACTIVE->value,
        'created_by' => $author->id,
    ]);

    $tokenAdmin = JWTAuth::fromUser($admin);

    $this->withHeaders(['Authorization' => 'Bearer ' . $tokenAdmin])
        ->putJson('/api/projects/' . $project->id, ['name' => 'ByAdmin'])
        ->assertStatus(200);
});

test('автор может удалить свой проект, чужой - нет; admin может', function () {
    [$adminRole, $managerRole, $userRole] = seedRoles();
    $author = createUserWithRole($userRole, 'author3@example.com');
    $other = createUserWithRole($managerRole, 'other3@example.com');
    $admin = createUserWithRole($adminRole, 'admin3@example.com');

    $project = Project::create([
        'name' => 'ToDelete',
        'description' => null,
        'status' => ProjectStatus::ACTIVE->value,
        'created_by' => $author->id,
    ]);

    $tokenAuthor = JWTAuth::fromUser($author);
    $tokenOther = JWTAuth::fromUser($other);
    $tokenAdmin = JWTAuth::fromUser($admin);

    $this->withHeaders(['Authorization' => 'Bearer ' . $tokenOther])
        ->deleteJson('/api/projects/' . $project->id)
        ->assertStatus(403);

    $this->withHeaders(['Authorization' => 'Bearer ' . $tokenAuthor])
        ->deleteJson('/api/projects/' . $project->id)
        ->assertStatus(200);

    $project2 = Project::create([
        'name' => 'Another',
        'status' => ProjectStatus::ACTIVE->value,
        'created_by' => $other->id,
    ]);

    $this->withHeaders(['Authorization' => 'Bearer ' . $tokenAdmin])
        ->deleteJson('/api/projects/' . $project2->id)
        ->assertStatus(200);
});

test('валидация при создании/обновлении проекта', function () {
    [$adminRole, $managerRole, $userRole] = seedRoles();
    $manager = createUserWithRole($managerRole, 'manager4@example.com');
    $token = JWTAuth::fromUser($manager);

    $this->withHeaders(['Authorization' => 'Bearer ' . $token])
        ->postJson('/api/projects', ['name' => ''])
        ->assertStatus(422)
        ->assertJson(['success' => false]);

    $project = Project::create([
        'name' => 'Valid',
        'status' => ProjectStatus::ACTIVE->value,
        'created_by' => $manager->id,
    ]);

    $this->withHeaders(['Authorization' => 'Bearer ' . $token])
        ->putJson('/api/projects/' . $project->id, ['status' => 'wrong'])
        ->assertStatus(422)
        ->assertJson(['success' => false]);
});
