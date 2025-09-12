<?php

use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tymon\JWTAuth\Facades\JWTAuth;

uses(RefreshDatabase::class);

test('заблокированный пользователь может войти', function () {
    $blockedUser = User::factory()->create([
        'email' => 'blocked@example.com',
        'password' => bcrypt('password123'),
        'status' => UserStatus::BLOCKED,
    ]);
    $response = $this->postJson('/api/auth/login', [
        'email' => 'blocked@example.com',
        'password' => 'password123',
    ]);

    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'user' => ['id', 'first_name', 'last_name', 'email'],
                'token',
                'token_type',
                'expires_in',
            ],
        ]);
});

test('заблокированный пользователь не может вызывать неавторизационные эндпоинты', function () {
    $blockedUser = User::factory()->create([
        'status' => UserStatus::BLOCKED,
    ]);
    $token = JWTAuth::fromUser($blockedUser);

    $me = $this->withHeaders([
        'Authorization' => 'Bearer ' . $token,
    ])->getJson('/api/auth/me');

    $me->assertStatus(200);

    $projectsIndex = $this->withHeaders([
        'Authorization' => 'Bearer ' . $token,
    ])->getJson('/api/projects');

    $projectsIndex->assertStatus(403)
        ->assertJson([
            'success' => false,
            'message' => __('api.auth.user_blocked'),
        ]);

    $usersIndex = $this->withHeaders([
        'Authorization' => 'Bearer ' . $token,
    ])->getJson('/api/users');

    $usersIndex->assertStatus(403);
});
