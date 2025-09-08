<?php

use App\Exceptions\ApiException;
use App\Http\Handlers\ApiExceptionHandler;
use App\Http\Middleware\JWTAuthMiddleware;
use App\Http\Resources\BaseApiResource;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'jwt.auth' => JWTAuthMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $handler = new ApiExceptionHandler;

        $exceptions->render(fn (ApiException $e, Request $request) => $handler->handleApiException($e, $request)
        );

        $exceptions->render(fn (TokenExpiredException $e, Request $request) => BaseApiResource::error(__('api.jwt.token_expired'), 401)
        );

        $exceptions->render(fn (TokenInvalidException $e, Request $request) => BaseApiResource::error(__('api.jwt.token_invalid'), 401)
        );

        $exceptions->render(fn (JWTException $e, Request $request) => BaseApiResource::error($request->bearerToken() === null ? __('api.jwt.token_missing') : ($request->is('api/auth/refresh') ? __('api.jwt.token_refresh_failed') : __('api.jwt.token_error')), 401)
        );

        $exceptions->render(fn (ValidationException $e, Request $request) => $handler->handleValidationException($e, $request)
        );

        $exceptions->render(fn (NotFoundHttpException $e, Request $request) => $handler->handleNotFoundException($e, $request)
        );

        $exceptions->render(fn (MethodNotAllowedHttpException $e, Request $request) => $handler->handleMethodNotAllowedException($e, $request)
        );

        $exceptions->render(function (ThrottleRequestsException $e, Request $request) {
            return BaseApiResource::error(__('api.http.too_many_requests'), 429);
        });

        $exceptions->render(fn (\Throwable $e, Request $request) => $handler->handleGenericException($e, $request)
        );

    })->create();
