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

    public function handleValidationException(ValidationException $e, Request $request): ?JsonResponse
    {
        if (! $this->isApiRequest($request)) {
            return null;
        }

        return BaseApiResource::error(__('api.http.validation_error'), 422, ['errors' => $e->errors()]);
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
}
