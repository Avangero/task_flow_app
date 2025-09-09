<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tymon\JWTAuth\Facades\JWTAuth;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create([
        'first_name' => 'Test',
        'last_name' => 'User',
        'email' => 'test@example.com',
        'password' => bcrypt('password123'),
    ]);
});

test('middleware пропускает запросы с действительным токеном', function () {
    $token = JWTAuth::fromUser($this->user);

    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $token,
    ])->getJson('/api/auth/me');

    $response->assertStatus(200);
});

test('middleware блокирует запросы без токена', function () {
    $response = $this->getJson('/api/auth/me');

    $response->assertStatus(401)
        ->assertJson([
            'success' => false,
            'message' => __('api.jwt.token_error'),
        ]);
});

test('middleware блокирует запросы с недействительным токеном', function () {
    $response = $this->withHeaders([
        'Authorization' => 'Bearer invalid_token_here',
    ])->getJson('/api/auth/me');

    $response->assertStatus(401)
        ->assertJson([
            'success' => false,
            'message' => __('api.jwt.token_invalid'),
        ]);
});

test('middleware блокирует запросы с истекшим токеном', function () {
    $token = JWTAuth::fromUser($this->user);

    JWTAuth::setToken($token)->invalidate();

    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $token,
    ])->getJson('/api/auth/me');

    $response->assertStatus(401);
});

test('middleware правильно обрабатывает токен без Bearer префикса', function () {
    $token = JWTAuth::fromUser($this->user);

    $response = $this->withHeaders([
        'Authorization' => $token,
    ])->getJson('/api/auth/me');

    $response->assertStatus(401)
        ->assertJson([
            'success' => false,
            'message' => __('api.jwt.token_error'),
        ]);
});

test('middleware правильно обрабатывает пустой Authorization header', function () {
    $response = $this->withHeaders([
        'Authorization' => '',
    ])->getJson('/api/auth/me');

    $response->assertStatus(401)
        ->assertJson([
            'success' => false,
            'message' => __('api.jwt.token_error'),
        ]);
});

test('middleware правильно обрабатывает токен с неправильным форматом', function () {
    $response = $this->withHeaders([
        'Authorization' => 'Basic some_token_here',
    ])->getJson('/api/auth/me');

    $response->assertStatus(401)
        ->assertJson([
            'success' => false,
            'message' => __('api.jwt.token_error'),
        ]);
});

test('middleware возвращает 404 если пользователь не найден', function () {
    $tempUser = User::factory()->create();
    $token = JWTAuth::fromUser($tempUser);

    $tempUser->delete();

    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $token,
    ])->getJson('/api/auth/me');

    $response->assertStatus(404)
        ->assertJson([
            'success' => false,
            'message' => __('api.auth.user_not_found'),
        ]);
});
