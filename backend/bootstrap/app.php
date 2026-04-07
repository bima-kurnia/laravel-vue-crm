<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',

        // API routes
        api: __DIR__ . '/../routes/api.php',
        apiPrefix: 'api',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'resolve.tenant' => \App\Http\Middleware\ResolveTenant::class,
<<<<<<< HEAD
            'role' => \App\Http\Middleware\RequireRole::class,
=======
            'role'           => \App\Http\Middleware\RequireRole::class,
            'tenant.context' => \App\Http\Middleware\TenantContextMiddleware::class,
        ]);

        // Append globally — runs on every request after auth is resolved
        $middleware->appendToGroup('api', [
            \App\Http\Middleware\TenantContextMiddleware::class,
>>>>>>> 0bf0120 (feat: initial CRM applicationBackend (Laravel):- Multi-tenant schema: tenants, users, customers, deals, activities- BelongsToTenant trait with global Eloquent scope- Sanctum Bearer token authentication (login, logout, logout-all, me)- ResolveTenant middleware with active tenant check- Customers API: CRUD, soft delete, restore, force delete, JSONB custom fields- Deals API: CRUD, pipeline stages, stage history, pipeline summary endpoint- Activities API: immutable audit log, polymorphic subject feed, stage history- Role-based access: owner / admin / member gates and RequireRole middleware- Tenant registration endpoint with DB transaction- Users search endpoint for owner autocomplete- Form Request validation on all endpoints- Service layer architecture (no repositories)- CustomerService, DealService, ActivityService, AuthService, TenantServiceFrontend (Vue 3):- Vite + Vue Router + Pinia + PrimeVue (Aura preset)- Bearer token auth with localStorage persistence and auto-rehydration- useApi composable: fetch wrapper with global 401 handler- Auth store, Customer store, Deal store- Login and registration views- Customers: list with search/filter/pagination, detail view, create/edit dialogs- Deals: list with filters, detail view, pipeline stage mover with confirmation modal- Activities: global log view with subject and event filters- Dashboard: pipeline summary cards- CustomFieldsEditor: dynamic JSONB key/value editor with type inference- DealForm: currency dropdown (15 currencies), customer/owner autocomplete- Role-aware UI via useRole composable- AppShell layout with sidebar navigation and topbar)
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (
            \Illuminate\Auth\AuthenticationException $e,
            \Illuminate\Http\Request $request
        ) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }
        });

        $exceptions->render(function (
            \Illuminate\Auth\Access\AuthorizationException $e,
            \Illuminate\Http\Request $request
        ) {
            if ($request->is('api/*')) {
                return response()->json(['message' => $e->getMessage()], 403);
            }
        });
<<<<<<< HEAD
=======

        // Report all unhandled exceptions with full tenant context
        // Log::withContext() is already set — this just adds exception detail
        $exceptions->report(function (\Throwable $e) {
            Log::error('exception.unhandled', [
                'exception' => get_class($e),
                'message'   => $e->getMessage(),
                'file'      => $e->getFile(),
                'line'      => $e->getLine(),
            ]);
        });
>>>>>>> 0bf0120 (feat: initial CRM applicationBackend (Laravel):- Multi-tenant schema: tenants, users, customers, deals, activities- BelongsToTenant trait with global Eloquent scope- Sanctum Bearer token authentication (login, logout, logout-all, me)- ResolveTenant middleware with active tenant check- Customers API: CRUD, soft delete, restore, force delete, JSONB custom fields- Deals API: CRUD, pipeline stages, stage history, pipeline summary endpoint- Activities API: immutable audit log, polymorphic subject feed, stage history- Role-based access: owner / admin / member gates and RequireRole middleware- Tenant registration endpoint with DB transaction- Users search endpoint for owner autocomplete- Form Request validation on all endpoints- Service layer architecture (no repositories)- CustomerService, DealService, ActivityService, AuthService, TenantServiceFrontend (Vue 3):- Vite + Vue Router + Pinia + PrimeVue (Aura preset)- Bearer token auth with localStorage persistence and auto-rehydration- useApi composable: fetch wrapper with global 401 handler- Auth store, Customer store, Deal store- Login and registration views- Customers: list with search/filter/pagination, detail view, create/edit dialogs- Deals: list with filters, detail view, pipeline stage mover with confirmation modal- Activities: global log view with subject and event filters- Dashboard: pipeline summary cards- CustomFieldsEditor: dynamic JSONB key/value editor with type inference- DealForm: currency dropdown (15 currencies), customer/owner autocomplete- Role-aware UI via useRole composable- AppShell layout with sidebar navigation and topbar)
    })->create();
