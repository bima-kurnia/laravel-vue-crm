<?php

use App\Http\Controllers\Api\ActivityController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\DealController;
use App\Http\Controllers\Api\ExportController;
use App\Http\Controllers\Api\HealthController;
use App\Http\Controllers\Api\InvitationController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\TenantController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public routes — no auth required
|--------------------------------------------------------------------------
*/

// Authentication & Account
Route::post('auth/login', [AuthController::class, 'login']);
Route::post('/register', [TenantController::class, 'register']);

// API health check
Route::get('/health', HealthController::class);

// Invitations
Route::get('/invitations/validate/{token}', [InvitationController::class, 'validate']);
Route::post('/invitations/accept/{token}',  [InvitationController::class, 'accept']);

/*
|--------------------------------------------------------------------------
| Protected routes — Bearer token + active tenant required
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:sanctum', 'resolve.tenant', 'tenant.context'])
    ->group(function () {

        // Protected — owner/admin only
        Route::middleware('role:owner,admin')->group(function () {

            // Users
            Route::get('/users', [UserController::class, 'index']);

            // Force-delete route
            Route::delete('/customers/{id}/force', [CustomerController::class, 'forceDelete']);
            Route::delete('/deals/{id}/force', [DealController::class, 'forceDelete']);

            // Restore route
            Route::patch('/customers/{id}/restore', [CustomerController::class, 'restore']);
            Route::patch('/deals/{id}/restore', [DealController::class, 'restore']);

            // Invitations
            Route::get('/invitations', [InvitationController::class, 'index']);
            Route::post('/invitations', [InvitationController::class, 'send']);
            Route::delete('/invitations/{id}',[InvitationController::class, 'revoke']);
        });

        // Auth
        Route::prefix('auth')->group(function () {
            Route::get('/me', [AuthController::class, 'me']);
            Route::post('/logout', [AuthController::class, 'logout']);
            Route::post('/logout-all', [AuthController::class, 'logoutAll']);
        });

        // Customers
        Route::prefix('customers')->group(function () {
            Route::get('/export', [ExportController::class, 'customers']);
            Route::get('/', [CustomerController::class, 'index']);
            Route::post('/', [CustomerController::class, 'store']);
            Route::get('/{id}', [CustomerController::class, 'show']);
            Route::patch('/{id}', [CustomerController::class, 'update']);
            Route::delete('/{id}', [CustomerController::class, 'destroy']);
            Route::patch('/{id}/restore', [CustomerController::class, 'restore']);
            Route::delete('/{id}/force',  [CustomerController::class, 'forceDelete']);
        });

        // Deals
        Route::prefix('deals')->group(function () {
            Route::get('/export', [ExportController::class, 'deals']);
            Route::get('/pipeline', [DealController::class, 'pipeline']);
            Route::get('/', [DealController::class, 'index']);
            Route::post('/', [DealController::class, 'store']);
            Route::get('/{id}', [DealController::class, 'show']);
            Route::patch('/{id}', [DealController::class, 'update']);
            Route::patch('/{id}/stage', [DealController::class, 'moveStage']);
            Route::delete('/{id}', [DealController::class, 'destroy']);
            Route::patch('/{id}/restore', [DealController::class, 'restore']);
            Route::delete('/{id}/force',  [DealController::class, 'forceDelete']);
        });

        // Activities (read-only)
        Route::prefix('activities')->group(function () {
            Route::get('/', [ActivityController::class, 'index']);
            Route::get('/subject/{type}/{id}', [ActivityController::class, 'subjectFeed']);
            Route::get('/deals/{id}/stage-history', [ActivityController::class, 'stageHistory']);
            Route::get('/{id}', [ActivityController::class, 'show']);
        });

        // Notifications
        Route::prefix('notifications')->group(function () {
            Route::get('/', [NotificationController::class, 'index']);
            Route::get('/unread-count', [NotificationController::class, 'unreadCount']);
            Route::patch('/read-all', [NotificationController::class, 'markAllRead']);
            Route::patch('/{id}/read', [NotificationController::class, 'markRead']);
        });
    });