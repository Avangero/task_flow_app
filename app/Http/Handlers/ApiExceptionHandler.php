<?php

namespace App\Http\Handlers;

use App\Exceptions\ApiException;
use App\Http\Resources\BaseApiResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;
use Tymon\JWTAuth\Exceptions\JWTException;

class ApiExceptionHandler
{
    public function isApiRequest(Request $request): bool
    {
        return $request->expectsJson() || $request->is('api/*');
    }

    public function handleApiException(ApiException $e, Request $request): ?JsonResponse
    {
        if (! $this->isApiRequest($request)) {
            return null;
        }

        return $e->render();
    }

    public function handleJWTException(JWTException $e, Request $request): ?JsonResponse
    {
        if (! $this->isApiRequest($request)) {
            return null;
        }

        $message = $this->getJWTErrorMessage($e);

        return BaseApiResource::error($message, 401);
    }

    public function handleValidationException(ValidationException $e, Request $request): ?JsonResponse
    {
        if (! $this->isApiRequest($request)) {
            return null;
        }

        return BaseApiResource::error(
            __('api.http.validation_error'),
            422,
            ['errors' => $e->errors()]
        );
    }

    public function handleNotFoundException(NotFoundHttpException $e, Request $request): ?JsonResponse
    {
        if (! $this->isApiRequest($request)) {
            return null;
        }

        return BaseApiResource::error(__('api.http.not_found'), 404);
    }

    public function handleMethodNotAllowedException(MethodNotAllowedHttpException $e, Request $request): ?JsonResponse
    {
        if (! $this->isApiRequest($request)) {
            return null;
        }

        return BaseApiResource::error(__('api.http.method_not_allowed'), 405);
    }

    public function handleGenericException(Throwable $e, Request $request): ?JsonResponse
    {
        if (! $this->isApiRequest($request)) {
            return null;
        }

        if ($e instanceof ApiException) {
            return null;
        }

        $message = config('app.debug') ? $e->getMessage() : __('api.http.internal_server_error');

        return BaseApiResource::error($message, 500);
    }

    protected function getJWTErrorMessage(JWTException $e): string
    {
        return match (get_class($e)) {
            'Tymon\JWTAuth\Exceptions\TokenExpiredException' => __('api.jwt.token_expired'),
            'Tymon\JWTAuth\Exceptions\TokenInvalidException' => __('api.jwt.token_invalid'),
            'Tymon\JWTAuth\Exceptions\JWTException' => $this->getBaseJWTExceptionMessage($e),
            default => __('api.jwt.token_error')
        };
    }

    protected function getBaseJWTExceptionMessage(JWTException $e): string
    {
        $message = $e->getMessage();

        return match (true) {
            str_contains($message, 'could not be parsed') => __('api.jwt.token_error'),
            str_contains($message, 'refresh') => __('api.jwt.token_refresh_failed'),
            default => __('api.jwt.token_error')
        };
    }
}
