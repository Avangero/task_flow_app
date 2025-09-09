<?php

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tymon\JWTAuth\Facades\JWTAuth;

uses(RefreshDatabase::class);

function createRoles(): array
{
    $admin = Role::create(['slug' => 'admin', 'name' => 'Admin', 'is_active' => true]);
    $manager = Role::create(['slug' => 'manager', 'name' => 'Manager', 'is_active' => true]);
    $user = Role::create(['slug' => 'user', 'name' => 'User', 'is_active' => true]);

    return [$admin, $manager, $user];
}

beforeEach(function () {
    [$this->roleAdmin, $this->roleManager, $this->roleUser] = createRoles();

    $this->admin = User::factory()->create([
        'first_name' => 'Admin',
        'last_name' => 'Root',
        'email' => 'admin@example.com',
        'password' => bcrypt('password123'),
        'role_id' => $this->roleAdmin->id,
    ]);
    $this->admin->load('role');

    $this->manager = User::factory()->create([
        'first_name' => 'Jane',
        'last_name' => 'Manager',
        'email' => 'manager@example.com',
        'password' => bcrypt('password123'),
        'role_id' => $this->roleManager->id,
    ]);
    $this->manager->load('role');

    $this->user = User::factory()->create([
        'first_name' => 'John',
        'last_name' => 'User',
        'email' => 'user@example.com',
        'password' => bcrypt('password123'),
        'role_id' => $this->roleUser->id,
    ]);
    $this->user->load('role');
});

test('список пользователей доступен для admin', function () {
    $token = JWTAuth::fromUser($this->admin);

    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $token,
    ])->getJson('/api/users');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'data' => [
                'users' => [
                    '*' => ['id', 'first_name', 'last_name', 'email'],
                ],
                'meta' => ['current_page', 'last_page', 'per_page', 'total'],
            ],
        ]);
});

test('список пользователей доступен для manager', function () {
    $token = JWTAuth::fromUser($this->manager);

    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $token,
    ])->getJson('/api/users');

    $response->assertStatus(200);
});

test('список пользователей запрещен для user', function () {
    $token = JWTAuth::fromUser($this->user);

    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $token,
    ])->getJson('/api/users');

    $response->assertStatus(403);
});

test('пользователь может получить свой профиль', function () {
    $token = JWTAuth::fromUser($this->user);

    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $token,
    ])->getJson('/api/users/' . $this->user->id);

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'data' => [
                'user' => [
                    'id' => $this->user->id,
                    'email' => $this->user->email,
                ],
            ],
        ]);
});

test('admin может получить чужой профиль', function () {
    $token = JWTAuth::fromUser($this->admin);

    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $token,
    ])->getJson('/api/users/' . $this->user->id);

    $response->assertStatus(200);
});

test('user не может получить чужой профиль', function () {
    $token = JWTAuth::fromUser($this->user);

    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $token,
    ])->getJson('/api/users/' . $this->manager->id);

    $response->assertStatus(403);
});

test('пользователь может обновить свой профиль', function () {
    $token = JWTAuth::fromUser($this->user);

    $payload = [
        'first_name' => 'Updated',
        'last_name' => 'User',
        'phone' => '123456',
    ];

    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $token,
    ])->putJson('/api/users/' . $this->user->id, $payload);

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'data' => [
                'user' => [
                    'first_name' => 'Updated',
                ],
            ],
        ]);

    $this->assertDatabaseHas('users', [
        'id' => $this->user->id,
        'first_name' => 'Updated',
    ]);
});

test('admin может обновить чужой профиль', function () {
    $token = JWTAuth::fromUser($this->admin);

    $payload = [
        'first_name' => 'ChangedByAdmin',
    ];

    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $token,
    ])->putJson('/api/users/' . $this->user->id, $payload);

    $response->assertStatus(200);

    $this->assertDatabaseHas('users', [
        'id' => $this->user->id,
        'first_name' => 'ChangedByAdmin',
    ]);
});

test('user не может обновить чужой профиль', function () {
    $token = JWTAuth::fromUser($this->user);

    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $token,
    ])->putJson('/api/users/' . $this->manager->id, [
        'first_name' => 'Hack',
    ]);

    $response->assertStatus(403);
});

test('валидация при обновлении профиля', function () {
    $token = JWTAuth::fromUser($this->user);

    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $token,
    ])->putJson('/api/users/' . $this->user->id, [
        'email' => 'invalid-email',
    ]);

    $response->assertStatus(422)
        ->assertJson([
            'success' => false,
        ]);
});
