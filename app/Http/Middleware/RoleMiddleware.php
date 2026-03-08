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
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!auth()->check()) {
            return response()->json(['message' => 'Non authentifié'], 401);
        }

        $userRole = auth()->user()->role;

        // superadmin bypasses all role checks
        if ($userRole === 'superadmin') {
            return $next($request);
        }

        if (!in_array($userRole, $roles)) {
            return response()->json(['message' => 'Non autorisé'], 403);
        }

        return $next($request);
    }
}
