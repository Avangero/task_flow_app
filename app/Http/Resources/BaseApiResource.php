<?php

namespace App\Http\Resources;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;

class BaseApiResource
{
    public static function success(string $message, int $statusCode, $data = null): JsonResponse
    {
        $response = [
            'success' => true,
            'message' => $message,
        ];

        if ($data !== null) {
            $response['data'] = $data instanceof JsonResource ? $data->resolve() : $data;
        }

        return response()->json($response, $statusCode);
    }

    public static function error(string $message, int $statusCode, ?array $errors = null): JsonResponse
    {
        $response = [
            'success' => false,
            'message' => $message,
        ];

        if ($errors !== null) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $statusCode);
    }

    public static function auth($user, string $token, string $message, int $statusCode): JsonResponse
    {
        return self::success($message, $statusCode, [
            'user' => $user instanceof JsonResource ? $user->resolve() : $user,
            'token' => $token,
            'token_type' => 'bearer',
            'expires_in' => config('jwt.ttl') * 60,
        ]);
    }
}
