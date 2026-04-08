<?php

use App\Http\Controllers\Api\ActivityController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\DealController;
use App\Http\Controllers\Api\HealthController;
use App\Http\Controllers\Api\NotificationController;
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

Route::get('/health', HealthController::class);


/*
|--------------------------------------------------------------------------
| Protected routes — Bearer token + active tenant required
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:sanctum', 'resolve.tenant', 'tenant.context'])
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

        Route::prefix('notifications')->group(function () {
            Route::get('/', [NotificationController::class, 'index']);
            Route::get('/unread-count', [NotificationController::class, 'unreadCount']);
            Route::patch('/read-all', [NotificationController::class, 'markAllRead']);
            Route::patch('/{id}/read', [NotificationController::class, 'markRead']);
        });
    });