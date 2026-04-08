<?php

namespace App\Providers;

use App\Models\User;
use App\Services\ActivityService;
use App\Services\CustomerService;
use App\Services\DealService;
use App\Services\TenantService;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(ActivityService::class);
        $this->app->singleton(NotificationService::class);
        $this->app->singleton(TenantService::class);

        $this->app->singleton(CustomerService::class, function ($app) {
            return new CustomerService(
                $app->make(ActivityService::class),
                $app->make(NotificationService::class),
            );
        });

        $this->app->singleton(DealService::class, function ($app) {
            return new DealService(
                $app->make(ActivityService::class),
                $app->make(NotificationService::class),
            );
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Only owners and admins can manage users
        Gate::define('manage-users', fn(User $user) =>
            in_array($user->role, ['owner', 'admin'])
        );

        // Only owners can delete the tenant or manage billing
        Gate::define('owner-only', fn(User $user) =>
            $user->role === 'owner'
        );

        // Owners, admins, and members can create/update/delete customers and deals
        Gate::define('manage-records', fn(User $user) =>
            in_array($user->role, ['owner', 'admin', 'member'])
        );

        // Force-delete is admin/owner only
        Gate::define('force-delete', fn(User $user) =>
            in_array($user->role, ['owner', 'admin'])
        );
    }
}
