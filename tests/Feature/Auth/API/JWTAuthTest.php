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

test('пользователь может зарегистрироваться с валидными данными', function () {
    $userData = [
        'first_name' => 'New',
        'last_name' => 'User',
        'email' => 'newuser@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ];

    $response = $this->postJson('/api/auth/register', $userData);

    $response->assertStatus(201)
        ->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'user' => ['id', 'first_name', 'last_name', 'email'],
                'token',
                'token_type',
                'expires_in',
            ],
        ])
        ->assertJson([
            'success' => true,
            'message' => __('api.auth.registration_success'),
            'data' => [
                'token_type' => 'bearer',
            ],
        ]);

    $this->assertDatabaseHas('users', [
        'email' => 'newuser@example.com',
        'first_name' => 'New',
        'last_name' => 'User',
    ]);
});

test('регистрация не удается с невалидными данными', function () {
    $response = $this->postJson('/api/auth/register', [
        'first_name' => '',
        'last_name' => '',
        'email' => 'invalid-email',
        'password' => '123',
        'password_confirmation' => '456',
    ]);

    $response->assertStatus(422)
        ->assertJson([
            'success' => false,
            'message' => __('api.http.validation_error'),
        ])
        ->assertJsonValidationErrors(['first_name', 'last_name', 'email', 'password']);
});

test('регистрация не удается с уже существующим email', function () {
    $response = $this->postJson('/api/auth/register', [
        'first_name' => 'Another',
        'last_name' => 'User',
        'email' => 'test@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['email']);
});

test('пользователь может войти с валидными учетными данными', function () {
    $response = $this->postJson('/api/auth/login', [
        'email' => 'test@example.com',
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
        ])
        ->assertJson([
            'success' => true,
            'message' => __('api.auth.login_success'),
            'data' => [
                'token_type' => 'bearer',
            ],
        ]);
});

test('вход не удается с неверными учетными данными', function () {
    $response = $this->postJson('/api/auth/login', [
        'email' => 'test@example.com',
        'password' => 'wrongpassword',
    ]);

    $response->assertStatus(401)
        ->assertJson([
            'success' => false,
            'message' => __('api.auth.invalid_credentials'),
        ]);
});

test('вход не удается с невалидными данными', function () {
    $response = $this->postJson('/api/auth/login', [
        'email' => 'invalid-email',
        'password' => '',
    ]);

    $response->assertStatus(422)
        ->assertJson([
            'success' => false,
            'message' => __('api.http.validation_error'),
        ])
        ->assertJsonValidationErrors(['email', 'password']);
});

test('аутентифицированный пользователь может получить свои данные', function () {
    $token = JWTAuth::fromUser($this->user);

    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $token,
    ])->getJson('/api/auth/me');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'data' => [
                'user' => ['id', 'first_name', 'last_name', 'email'],
            ],
        ])
        ->assertJson([
            'success' => true,
            'data' => [
                'user' => [
                    'id' => $this->user->id,
                    'first_name' => $this->user->first_name,
                    'last_name' => $this->user->last_name,
                    'email' => $this->user->email,
                ],
            ],
        ]);
});

test('неаутентифицированный пользователь не может получить данные', function () {
    $response = $this->getJson('/api/auth/me');

    $response->assertStatus(401)
        ->assertJson([
            'success' => false,
            'message' => __('api.jwt.token_error'),
        ]);
});

test('пользователь с недействительным токеном не может получить данные', function () {
    $response = $this->withHeaders([
        'Authorization' => 'Bearer invalid_token',
    ])->getJson('/api/auth/me');

    $response->assertStatus(401)
        ->assertJson([
            'success' => false,
            'message' => __('api.jwt.token_invalid'),
        ]);
});

test('пользователь может обновить действительный токен', function () {
    $token = JWTAuth::fromUser($this->user);

    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $token,
    ])->postJson('/api/auth/refresh');

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
        ])
        ->assertJson([
            'success' => true,
            'message' => __('api.auth.token_refreshed'),
            'data' => [
                'token_type' => 'bearer',
            ],
        ]);

    expect($response->json('data.token'))->not->toBe($token);
});

test('обновление не удается без токена', function () {
    $response = $this->postJson('/api/auth/refresh');

    $response->assertStatus(401)
        ->assertJson([
            'success' => false,
            'message' => __('api.jwt.token_error'),
        ]);
});

test('аутентифицированный пользователь может выйти из системы', function () {
    $token = JWTAuth::fromUser($this->user);

    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $token,
    ])->postJson('/api/auth/logout');

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'message' => __('api.auth.logout_success'),
        ]);

    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $token,
    ])->getJson('/api/auth/me');

    $response->assertStatus(401);
});

test('выход не удается без токена', function () {
    $response = $this->postJson('/api/auth/logout');

    $response->assertStatus(401)
        ->assertJson([
            'success' => false,
            'message' => __('api.jwt.token_error'),
        ]);
});

test('аутентифицированный пользователь может получить доступ к защищенному маршруту', function () {
    $token = JWTAuth::fromUser($this->user);

    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $token,
    ])->getJson('/api/auth/me');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'data' => [
                'user' => ['id', 'first_name', 'last_name', 'email'],
            ],
        ])
        ->assertJson([
            'success' => true,
            'data' => [
                'user' => [
                    'id' => $this->user->id,
                    'first_name' => $this->user->first_name,
                    'last_name' => $this->user->last_name,
                    'email' => $this->user->email,
                ],
            ],
        ]);
});

test('неаутентифицированный пользователь не может получить доступ к защищенному маршруту', function () {
    $response = $this->getJson('/api/auth/me');

    $response->assertStatus(401)
        ->assertJson([
            'success' => false,
            'message' => __('api.jwt.token_error'),
        ]);
});

test('полный цикл: регистрация -> вход -> получение данных -> обновление токена -> выход', function () {
    $registerResponse = $this->postJson('/api/auth/register', [
        'first_name' => 'Flow',
        'last_name' => 'Test User',
        'email' => 'flowtest@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $registerResponse->assertStatus(201);
    $registerResponse->json('data.token');

    $loginResponse = $this->postJson('/api/auth/login', [
        'email' => 'flowtest@example.com',
        'password' => 'password123',
    ]);

    $loginResponse->assertStatus(200);
    $loginToken = $loginResponse->json('data.token');

    $meResponse = $this->withHeaders([
        'Authorization' => 'Bearer ' . $loginToken,
    ])->getJson('/api/auth/me');

    $meResponse->assertStatus(200)
        ->assertJson([
            'success' => true,
            'data' => [
                'user' => [
                    'first_name' => 'Flow',
                    'last_name' => 'Test User',
                    'email' => 'flowtest@example.com',
                ],
            ],
        ]);

    $refreshResponse = $this->withHeaders([
        'Authorization' => 'Bearer ' . $loginToken,
    ])->postJson('/api/auth/refresh');

    $refreshResponse->assertStatus(200);
    $refreshedToken = $refreshResponse->json('data.token');
    expect($refreshedToken)->not->toBe($loginToken);

    $logoutResponse = $this->withHeaders([
        'Authorization' => 'Bearer ' . $refreshedToken,
    ])->postJson('/api/auth/logout');

    $logoutResponse->assertStatus(200)
        ->assertJson([
            'success' => true,
            'message' => __('api.auth.logout_success'),
        ]);

    $checkResponse = $this->withHeaders([
        'Authorization' => 'Bearer ' . $refreshedToken,
    ])->getJson('/api/auth/me');

    $checkResponse->assertStatus(401);
});
