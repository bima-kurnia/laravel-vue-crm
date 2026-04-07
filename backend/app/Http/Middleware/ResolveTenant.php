<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ResolveTenant
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        if (! $user->tenant || ! $user->tenant->is_active) {
            return response()->json(['message' => 'Tenant is inactive or not found.'], 403);
        }

        // Bind tenant to the request for optional use downstream,
        // but services will always derive tenant_id from auth()->user()
        $request->attributes->set('tenant', $user->tenant);
        
        return $next($request);
    }
}
