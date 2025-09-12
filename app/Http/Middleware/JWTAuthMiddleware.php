<?php

namespace App\Http\Middleware;

use App\Exceptions\Api\Auth\AuthException;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

class JWTAuthMiddleware
{
    /**
     * @throws JWTException
     * @throws AuthException
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = JWTAuth::parseToken()->authenticate();

        if (! $user) {
            throw new AuthException(__('api.auth.user_not_found'), 404);
        }

        return $next($request);
    }
}
