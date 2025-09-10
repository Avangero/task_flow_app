<?php

namespace App\Services\Auth;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;

interface AuthServiceInterface
{
    public function register(RegisterRequest $request): array;

    public function login(LoginRequest $request): array;

    public function me(): User;

    public function logout(): bool;

    public function refresh(): array;
}
