<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle($request, Closure $next, ...$roles)
    {
        $user = $request->user();

        // 🔒 Belum login
        if (!$user) {
            return response()->json([
                'message' => 'Unauthenticated'
            ], 401);
        }

        // 🔒 Role tidak sesuai
        if (!in_array($user->role, $roles)) {
            return response()->json([
                'message' => 'Forbidden: insufficient role'
            ], 403);
        }

        return $next($request);
    }
}
