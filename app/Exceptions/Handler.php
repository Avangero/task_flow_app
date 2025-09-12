<?php

namespace App\Exceptions;

use App\Exceptions\Api\ApiException;
use App\Http\Resources\BaseApiResource;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;

class Handler extends ExceptionHandler
{
    public function register(): void
    {
        $this->renderable(function (ApiException $e, Request $request) {
            if (! $this->isApiRequest($request)) {
                return null;
            }

            return $e->render();
        });

        $this->renderable(function (ValidationException $e, Request $request) {
            if (! $this->isApiRequest($request)) {
                return null;
            }

            return BaseApiResource::error(__('api.http.validation_error'), 422, ['errors' => $e->errors()]);
        });

        $this->renderable(function (Throwable $e, Request $request) {
            if (! $this->isApiRequest($request)) {
                return null;
            }

            return match (true) {
                $e instanceof NotFoundHttpException => BaseApiResource::error(__('api.http.not_found'), 404),

                $e instanceof MethodNotAllowedHttpException => BaseApiResource::error(__('api.http.method_not_allowed'), 405),

                $e instanceof AuthorizationException,
                $e instanceof AccessDeniedHttpException => BaseApiResource::error(__('api.http.forbidden'), 403),

                $e instanceof TokenExpiredException => BaseApiResource::error(__('api.jwt.token_expired'), 401),

                $e instanceof TokenInvalidException => BaseApiResource::error(__('api.jwt.token_invalid'), 401),

                $e instanceof JWTException => BaseApiResource::error(
                    $request->bearerToken() === null
                        ? __('api.jwt.token_missing')
                        : ($request->is('api/auth/refresh') ? __('api.jwt.token_refresh_failed') : __('api.jwt.token_error')),
                    401
                ),

                $e instanceof ThrottleRequestsException => BaseApiResource::error(__('api.http.too_many_requests'), 429),

                default => BaseApiResource::error(config('app.debug') ? $e->getMessage() : __('api.http.internal_server_error'), 500),
            };
        });
    }

    protected function isApiRequest(Request $request): bool
    {
        return $request->expectsJson() || $request->is('api/*');
    }
}
