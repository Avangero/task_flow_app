<?php

namespace App\Services\Auth;

use App\Enums\UserStatus;
use App\Exceptions\Api\Auth\AuthException;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

readonly class AuthService implements AuthServiceInterface
{
    public function register(RegisterRequest $request): array
    {
        $defaultRoleId = Role::query()->where('slug', 'user')->value('id');

        $user = User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role_id' => $defaultRoleId,
            'status' => UserStatus::ACTIVE->value,
        ]);

        $token = JWTAuth::fromUser($user);

        return [
            'user' => $user,
            'token' => $token,
        ];
    }

    /**
     * @throws AuthException
     */
    public function login(LoginRequest $request): array
    {
        $credentials = $request->only('email', 'password');

        if (! $token = JWTAuth::attempt($credentials)) {
            throw new AuthException(__('api.auth.invalid_credentials'), 401);
        }

        $user = JWTAuth::user();

        return [
            'user' => $user,
            'token' => $token,
        ];
    }

    /**
     * @throws JWTException
     * @throws AuthException
     */
    public function me(): User
    {
        $user = JWTAuth::parseToken()->authenticate();

        if (! $user) {
            throw new AuthException(__('api.auth.user_not_found'), 404);
        }

        return $user;
    }

    public function logout(): bool
    {
        JWTAuth::invalidate(JWTAuth::getToken());

        return true;
    }

    public function refresh(): array
    {
        $token = JWTAuth::refresh(JWTAuth::getToken());
        $user = JWTAuth::setToken($token)->toUser();

        return [
            'user' => $user,
            'token' => $token,
        ];
    }
}
