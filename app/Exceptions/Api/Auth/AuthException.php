<?php

namespace App\Exceptions\Api\Auth;

use App\Exceptions\Api\ApiException;
use Exception;

class AuthException extends ApiException
{
    public function __construct(
        ?string $message = null,
        int $statusCode = 401,
        ?string $userMessage = null,
        ?Exception $previous = null
    ) {
        parent::__construct($message ?? __('api.http.unauthorized'), $statusCode, $userMessage, $previous);
    }
}
