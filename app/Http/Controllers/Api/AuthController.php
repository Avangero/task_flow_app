<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\BaseApiResource;
use App\Http\Resources\UserResource;
use App\Services\Auth\AuthServiceInterface;
use Illuminate\Http\JsonResponse;

class AuthController extends Controller
{
    public function __construct(
        protected readonly AuthServiceInterface $authService
    ) {}

    public function register(RegisterRequest $request): JsonResponse
    {
        $result = $this->authService->register($request);

        return BaseApiResource::auth(
            user: new UserResource($result['user']),
            token: $result['token'],
            message: __('api.auth.registration_success'),
            statusCode: 201
        );
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $result = $this->authService->login($request);

        return BaseApiResource::auth(
            user: new UserResource($result['user']),
            token: $result['token'],
            message: __('api.auth.login_success'),
            statusCode: 200
        );
    }

    public function me(): JsonResponse
    {
        $user = $this->authService->me();

        return BaseApiResource::success(
            message: __('api.auth.profile_retrieved'),
            statusCode: 200,
            data: ['user' => new UserResource($user)]
        );
    }

    public function logout(): JsonResponse
    {
        $this->authService->logout();

        return BaseApiResource::success(message: __('api.auth.logout_success'), statusCode: 200);
    }

    public function refresh(): JsonResponse
    {
        $result = $this->authService->refresh();

        return BaseApiResource::auth(
            user: new UserResource($result['user']),
            token: $result['token'],
            message: __('api.auth.token_refreshed'),
            statusCode: 200
        );
    }
}
