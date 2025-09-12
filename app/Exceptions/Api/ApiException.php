<?php

namespace App\Exceptions\Api;

use Exception;
use Illuminate\Http\JsonResponse;

class ApiException extends Exception
{
    protected int $statusCode;
    protected string $userMessage;

    public function __construct(
        ?string $message = null,
        int $statusCode = 500,
        ?string $userMessage = null,
        ?Exception $previous = null
    ) {
        $resolvedMessage = $message ?? __('api.error');
        parent::__construct($resolvedMessage, 0, $previous);

        $this->statusCode = $statusCode;
        $this->userMessage = $userMessage ?? $resolvedMessage;
    }

    public function render(): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $this->userMessage,
            'error_code' => $this->statusCode,
        ], $this->statusCode);
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }
}
