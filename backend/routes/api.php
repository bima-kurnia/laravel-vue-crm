<?php

use App\Http\Controllers\Api\ActivityController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\DealController;
use App\Http\Controllers\Api\TenantController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public routes — no auth required
|--------------------------------------------------------------------------
*/
Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
});

Route::post('/register', [TenantController::class, 'register']);


/*
|--------------------------------------------------------------------------
| Protected routes — Bearer token + active tenant required
|--------------------------------------------------------------------------
*/
<<<<<<< HEAD
Route::middleware(['auth:sanctum', \App\Http\Middleware\ResolveTenant::class])
=======
Route::middleware(['auth:sanctum', 'resolve.tenant', 'tenant.context'])
>>>>>>> 0bf0120 (feat: initial CRM applicationBackend (Laravel):- Multi-tenant schema: tenants, users, customers, deals, activities- BelongsToTenant trait with global Eloquent scope- Sanctum Bearer token authentication (login, logout, logout-all, me)- ResolveTenant middleware with active tenant check- Customers API: CRUD, soft delete, restore, force delete, JSONB custom fields- Deals API: CRUD, pipeline stages, stage history, pipeline summary endpoint- Activities API: immutable audit log, polymorphic subject feed, stage history- Role-based access: owner / admin / member gates and RequireRole middleware- Tenant registration endpoint with DB transaction- Users search endpoint for owner autocomplete- Form Request validation on all endpoints- Service layer architecture (no repositories)- CustomerService, DealService, ActivityService, AuthService, TenantServiceFrontend (Vue 3):- Vite + Vue Router + Pinia + PrimeVue (Aura preset)- Bearer token auth with localStorage persistence and auto-rehydration- useApi composable: fetch wrapper with global 401 handler- Auth store, Customer store, Deal store- Login and registration views- Customers: list with search/filter/pagination, detail view, create/edit dialogs- Deals: list with filters, detail view, pipeline stage mover with confirmation modal- Activities: global log view with subject and event filters- Dashboard: pipeline summary cards- CustomFieldsEditor: dynamic JSONB key/value editor with type inference- DealForm: currency dropdown (15 currencies), customer/owner autocomplete- Role-aware UI via useRole composable- AppShell layout with sidebar navigation and topbar)
    ->group(function () {

        // Admin/owner only at the route layer too (defense in depth)
        Route::middleware('role:owner,admin')->group(function () {
            // Force-delete route
            Route::delete('/customers/{id}/force', [CustomerController::class, 'forceDelete']);
            Route::delete('/deals/{id}/force', [DealController::class, 'forceDelete']);

            // Restore route
            Route::patch('/customers/{id}/restore', [CustomerController::class, 'restore']);
            Route::patch('/deals/{id}/restore', [DealController::class, 'restore']);

            // Users
            Route::get('/users', [UserController::class, 'index']);
        });

        // Auth
        Route::prefix('auth')->group(function () {
            Route::get('/me', [AuthController::class, 'me']);
            Route::post('/logout', [AuthController::class, 'logout']);
            Route::post('/logout-all', [AuthController::class, 'logoutAll']);
        });

        // Customers
        Route::prefix('customers')->group(function () {
            Route::get('/', [CustomerController::class, 'index']);
            Route::post('/', [CustomerController::class, 'store']);
            Route::get('/{id}', [CustomerController::class, 'show']);
            Route::patch('/{id}', [CustomerController::class, 'update']);
            Route::delete('/{id}', [CustomerController::class, 'destroy']);
        });

        // Deals
        Route::prefix('deals')->group(function () {
            Route::get('/pipeline', [DealController::class, 'pipeline']);
            Route::get('/', [DealController::class, 'index']);
            Route::post('/', [DealController::class, 'store']);
            Route::get('/{id}', [DealController::class, 'show']);
            Route::patch('/{id}', [DealController::class, 'update']);
            Route::patch('/{id}/stage', [DealController::class, 'moveStage']);
            Route::delete('/{id}', [DealController::class, 'destroy']);
        });

        // Activities (read-only)
        Route::prefix('activities')->group(function () {
            Route::get('/', [ActivityController::class, 'index']);
            Route::get('/subject/{type}/{id}', [ActivityController::class, 'subjectFeed']);
            Route::get('/deals/{id}/stage-history', [ActivityController::class, 'stageHistory']);
            Route::get('/{id}', [ActivityController::class, 'show']);
        });
    });