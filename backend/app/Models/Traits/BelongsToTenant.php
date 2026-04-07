<?php

namespace App\Models\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

trait BelongsToTenant
{
    /**
     * Boot the trait — registers the global scope and the creating listener.
     */
    public static function bootBelongsToTenant(): void
    {
        // Global scope: every query on this model is automatically scoped to
        // the authenticated user's tenant_id. Skipped when no user is logged in
        // (e.g. during seeding or console commands).
        static::addGlobalScope('tenant', function (Builder $builder) {
            if (Auth::check()) {
                $builder->where(
                    $builder->getModel()->getTable() . '.tenant_id',
                    Auth::user()->tenant_id
                );
            }
        });

        // Auto-attach tenant_id on creation so service code never has to set it.
        static::creating(function ($model) {
            if (Auth::check() && empty($model->tenant_id)) {
                $model->tenant_id = Auth::user()->tenant_id;
            }
        });
    }

    /**
     * Allow temporarily bypassing the tenant scope (e.g. in console commands).
     * Usage: Customer::withoutTenantScope()->where(...)->get()
     */
    public static function withoutTenantScope(): Builder
    {
        return static::withoutGlobalScope('tenant');
    }
}
