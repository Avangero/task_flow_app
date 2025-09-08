<?php

namespace App\Exceptions;

use Exception;

class AuthException extends ApiException
{
    public function __construct(
        string $message = 'Ошибка аутентификации',
        int $statusCode = 401,
        ?string $userMessage = null,
        ?Exception $previous = null
    ) {
        parent::__construct($message, $statusCode, $userMessage, $previous);
    }
}
