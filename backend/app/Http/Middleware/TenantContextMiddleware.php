<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class TenantContextMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Generate a unique ID for this request — ties all log lines
        // from a single request together even across async log writers
        $requestId = (string) Str::uuid();

        // Share the request ID downstream (useful in responses/debugging)
        $request->headers->set('X-Request-Id', $requestId);

        // Build context from whatever is available at this point.
        // Auth may not be resolved yet if this runs before auth:sanctum,
        // so we defer user resolution to a lazy closure.
        $context = [
            'request_id' => $requestId,
            'method'     => $request->method(),
            'path'       => $request->path(),
            'ip'         => $request->ip(),
        ];

        // Attach tenant/user context — only available on authenticated requests.
        // withContext is additive: unauthenticated requests log without tenant fields
        // and authenticated ones get the full context injected below.
        if ($user = $request->user()) {
            $context['tenant_id'] = $user->tenant_id;
            $context['user_id']   = $user->id;
            $context['user_role'] = $user->role;
        }

        Log::withContext($context);

        $response = $next($request);

        // Append response status so every request has a complete audit trail
        Log::withContext([
            'response_status' => $response->getStatusCode(),
        ]);

        return $response;
    }
}
