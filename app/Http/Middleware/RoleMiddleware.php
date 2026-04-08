<?php

namespace App\Http\Middleware;

use App\Helpers\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     * Checks roles via the pivot table (user_roles), NOT the deprecated users.role column.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle($request, Closure $next, ...$roles)
    {
        $user = $request->user();

        if (!$user) {
            return ApiResponse::error('Unauthenticated', null, 401);
        }

        // Eager-load roles once for this request
        $user->loadMissing('roles');

        if (!$user->hasAnyRole($roles)) {
            return ApiResponse::error('Forbidden', 'Insufficient role', 403);
        }

        return $next($request);
    }
}
