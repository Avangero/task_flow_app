<?php

namespace App\Http\Middleware;

use App\Enums\UserStatus;
use App\Http\Resources\BaseApiResource;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Facades\JWTAuth;

class BlockedUserMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->is('api/auth') || $request->is('api/auth/*')) {
            return $next($request);
        }

        $user = JWTAuth::user();

        if ($user && $user->status === UserStatus::BLOCKED) {
            return BaseApiResource::error(__('api.auth.user_blocked'), 403);
        }

        return $next($request);
    }
}
