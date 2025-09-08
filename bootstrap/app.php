<?php

use App\Exceptions\ApiException;
use App\Http\Handlers\ApiExceptionHandler;
use App\Http\Middleware\JWTAuthMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Tymon\JWTAuth\Exceptions\JWTException;

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

        $exceptions->render(fn (JWTException $e, Request $request) => $handler->handleJWTException($e, $request)
        );

        $exceptions->render(fn (ValidationException $e, Request $request) => $handler->handleValidationException($e, $request)
        );

        $exceptions->render(fn (NotFoundHttpException $e, Request $request) => $handler->handleNotFoundException($e, $request)
        );

        $exceptions->render(fn (MethodNotAllowedHttpException $e, Request $request) => $handler->handleMethodNotAllowedException($e, $request)
        );

        $exceptions->render(fn (\Throwable $e, Request $request) => $handler->handleGenericException($e, $request)
        );

    })->create();
