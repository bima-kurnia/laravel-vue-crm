<?php

namespace App\Services;

use App\Models\Customer;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
<<<<<<< HEAD
=======
use Illuminate\Support\Facades\Log;
>>>>>>> 0bf0120 (feat: initial CRM applicationBackend (Laravel):- Multi-tenant schema: tenants, users, customers, deals, activities- BelongsToTenant trait with global Eloquent scope- Sanctum Bearer token authentication (login, logout, logout-all, me)- ResolveTenant middleware with active tenant check- Customers API: CRUD, soft delete, restore, force delete, JSONB custom fields- Deals API: CRUD, pipeline stages, stage history, pipeline summary endpoint- Activities API: immutable audit log, polymorphic subject feed, stage history- Role-based access: owner / admin / member gates and RequireRole middleware- Tenant registration endpoint with DB transaction- Users search endpoint for owner autocomplete- Form Request validation on all endpoints- Service layer architecture (no repositories)- CustomerService, DealService, ActivityService, AuthService, TenantServiceFrontend (Vue 3):- Vite + Vue Router + Pinia + PrimeVue (Aura preset)- Bearer token auth with localStorage persistence and auto-rehydration- useApi composable: fetch wrapper with global 401 handler- Auth store, Customer store, Deal store- Login and registration views- Customers: list with search/filter/pagination, detail view, create/edit dialogs- Deals: list with filters, detail view, pipeline stage mover with confirmation modal- Activities: global log view with subject and event filters- Dashboard: pipeline summary cards- CustomFieldsEditor: dynamic JSONB key/value editor with type inference- DealForm: currency dropdown (15 currencies), customer/owner autocomplete- Role-aware UI via useRole composable- AppShell layout with sidebar navigation and topbar)

class CustomerService
{
    public function __construct(private readonly ActivityService $activityService) {}

    // -------------------------------------------------------------------------
    // List
    // -------------------------------------------------------------------------

    public function list(array $filters): LengthAwarePaginator
    {
        $query = Customer::query();

        // Include soft-deleted records only if explicitly requested
        if (! empty($filters['with_trashed'])) {
            $query->withTrashed();
        }

        // Full-text search across name, email, company
        if (! empty($filters['search'])) {
            $term = '%' . $filters['search'] . '%';
            
            $query->where(function ($q) use ($term) {
                $q->where('name', 'ilike', $term)
                  ->orWhere('email', 'ilike', $term)
                  ->orWhere('company', 'ilike', $term);
            });
        }

        // Exact filters
        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['company'])) {
            $query->where('company', 'ilike', '%' . $filters['company'] . '%');
        }

        // Sorting — column already validated by Form Request allowlist
        $sortBy  = $filters['sort_by']  ?? 'created_at';
        $sortDir = $filters['sort_dir'] ?? 'desc';
        $query->orderBy($sortBy, $sortDir);

        $perPage = $filters['per_page'] ?? 15;

        return $query->paginate($perPage);
    }

    // -------------------------------------------------------------------------
    // Show
    // -------------------------------------------------------------------------

    public function findOrFail(string $id): Customer
    {
        // BelongsToTenant global scope ensures this 404s across tenants
        return Customer::findOrFail($id);
    }

    // -------------------------------------------------------------------------
    // Create
    // -------------------------------------------------------------------------

    public function create(array $data): Customer
    {
        // All roles can create — gate defined for completeness
        Gate::authorize('manage-records');

        // tenant_id is auto-attached by BelongsToTenant::creating()
        $customer = Customer::create($data);

<<<<<<< HEAD
=======
        // tenant_id, user_id, request_id are injected automatically
        Log::info('customer.created', [
            'customer_id' => $customer->id,
            'name'        => $customer->name,
        ]);

>>>>>>> 0bf0120 (feat: initial CRM applicationBackend (Laravel):- Multi-tenant schema: tenants, users, customers, deals, activities- BelongsToTenant trait with global Eloquent scope- Sanctum Bearer token authentication (login, logout, logout-all, me)- ResolveTenant middleware with active tenant check- Customers API: CRUD, soft delete, restore, force delete, JSONB custom fields- Deals API: CRUD, pipeline stages, stage history, pipeline summary endpoint- Activities API: immutable audit log, polymorphic subject feed, stage history- Role-based access: owner / admin / member gates and RequireRole middleware- Tenant registration endpoint with DB transaction- Users search endpoint for owner autocomplete- Form Request validation on all endpoints- Service layer architecture (no repositories)- CustomerService, DealService, ActivityService, AuthService, TenantServiceFrontend (Vue 3):- Vite + Vue Router + Pinia + PrimeVue (Aura preset)- Bearer token auth with localStorage persistence and auto-rehydration- useApi composable: fetch wrapper with global 401 handler- Auth store, Customer store, Deal store- Login and registration views- Customers: list with search/filter/pagination, detail view, create/edit dialogs- Deals: list with filters, detail view, pipeline stage mover with confirmation modal- Activities: global log view with subject and event filters- Dashboard: pipeline summary cards- CustomFieldsEditor: dynamic JSONB key/value editor with type inference- DealForm: currency dropdown (15 currencies), customer/owner autocomplete- Role-aware UI via useRole composable- AppShell layout with sidebar navigation and topbar)
        $this->activityService->log($customer, 'created', [
            'after' => $data,
        ]);

        return $customer;
    }

    // -------------------------------------------------------------------------
    // Update
    // -------------------------------------------------------------------------

    public function update(string $id, array $data): Customer
    {
        Gate::authorize('manage-records');

        $customer = $this->findOrFail($id);

        $before = $customer->only(array_keys($data));
                
        // No merge — CustomFieldsEditor always sends the complete desired state.
        // A full replace is correct. Merging caused key duplication on edits.

        $customer->update($data);
        $fresh = $customer->fresh();

        $this->activityService->log(
            $customer,
            'updated',
            ActivityService::diff($before, $fresh->only(array_keys($data)))
        );

        return $fresh;
    }

    // -------------------------------------------------------------------------
    // Soft Delete
    // -------------------------------------------------------------------------

    public function delete(string $id): void
    {
        Gate::authorize('manage-records');

        $customer = $this->findOrFail($id);
        $customer->delete();

<<<<<<< HEAD
=======
        Log::info('customer.deleted', [
            'customer_id' => $customer->id,
            'name'        => $customer->name,
        ]);

>>>>>>> 0bf0120 (feat: initial CRM applicationBackend (Laravel):- Multi-tenant schema: tenants, users, customers, deals, activities- BelongsToTenant trait with global Eloquent scope- Sanctum Bearer token authentication (login, logout, logout-all, me)- ResolveTenant middleware with active tenant check- Customers API: CRUD, soft delete, restore, force delete, JSONB custom fields- Deals API: CRUD, pipeline stages, stage history, pipeline summary endpoint- Activities API: immutable audit log, polymorphic subject feed, stage history- Role-based access: owner / admin / member gates and RequireRole middleware- Tenant registration endpoint with DB transaction- Users search endpoint for owner autocomplete- Form Request validation on all endpoints- Service layer architecture (no repositories)- CustomerService, DealService, ActivityService, AuthService, TenantServiceFrontend (Vue 3):- Vite + Vue Router + Pinia + PrimeVue (Aura preset)- Bearer token auth with localStorage persistence and auto-rehydration- useApi composable: fetch wrapper with global 401 handler- Auth store, Customer store, Deal store- Login and registration views- Customers: list with search/filter/pagination, detail view, create/edit dialogs- Deals: list with filters, detail view, pipeline stage mover with confirmation modal- Activities: global log view with subject and event filters- Dashboard: pipeline summary cards- CustomFieldsEditor: dynamic JSONB key/value editor with type inference- DealForm: currency dropdown (15 currencies), customer/owner autocomplete- Role-aware UI via useRole composable- AppShell layout with sidebar navigation and topbar)
        $this->activityService->log($customer, 'deleted', [
            'name' => $customer->name,
        ]);
    }

    // -------------------------------------------------------------------------
    // Restore
    // -------------------------------------------------------------------------

    public function restore(string $id): Customer
    {
        Gate::authorize('force-delete'); // restore is privileged

        // Must include trashed records to find it
        $customer = Customer::withTrashed()->findOrFail($id);

        // Explicit tenant ownership check — global scope does not cover withTrashed
        // queries when the record is soft-deleted on some driver configurations
        $this->assertTenantOwnership($customer);

        $customer->restore();

        $this->activityService->log($customer, 'restored', [
            'name' => $customer->name,
        ]);

        return $customer;
    }

    // -------------------------------------------------------------------------
    // Hard Delete
    // -------------------------------------------------------------------------

    public function forceDelete(string $id): void
    {
        Gate::authorize('force-delete');
        
        $customer = Customer::withTrashed()->findOrFail($id);

        $this->assertTenantOwnership($customer);

<<<<<<< HEAD
=======
        // Elevated action — use warning level so it surfaces in alerting
        Log::warning('customer.force_deleted', [
            'customer_id' => $customer->id,
            'name'        => $customer->name,
        ]);

>>>>>>> 0bf0120 (feat: initial CRM applicationBackend (Laravel):- Multi-tenant schema: tenants, users, customers, deals, activities- BelongsToTenant trait with global Eloquent scope- Sanctum Bearer token authentication (login, logout, logout-all, me)- ResolveTenant middleware with active tenant check- Customers API: CRUD, soft delete, restore, force delete, JSONB custom fields- Deals API: CRUD, pipeline stages, stage history, pipeline summary endpoint- Activities API: immutable audit log, polymorphic subject feed, stage history- Role-based access: owner / admin / member gates and RequireRole middleware- Tenant registration endpoint with DB transaction- Users search endpoint for owner autocomplete- Form Request validation on all endpoints- Service layer architecture (no repositories)- CustomerService, DealService, ActivityService, AuthService, TenantServiceFrontend (Vue 3):- Vite + Vue Router + Pinia + PrimeVue (Aura preset)- Bearer token auth with localStorage persistence and auto-rehydration- useApi composable: fetch wrapper with global 401 handler- Auth store, Customer store, Deal store- Login and registration views- Customers: list with search/filter/pagination, detail view, create/edit dialogs- Deals: list with filters, detail view, pipeline stage mover with confirmation modal- Activities: global log view with subject and event filters- Dashboard: pipeline summary cards- CustomFieldsEditor: dynamic JSONB key/value editor with type inference- DealForm: currency dropdown (15 currencies), customer/owner autocomplete- Role-aware UI via useRole composable- AppShell layout with sidebar navigation and topbar)
        $this->activityService->log($customer, 'force_deleted', [
            'name' => $customer->name,
        ]);

        $customer->forceDelete();
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /**
     * Secondary tenant ownership assertion for operations that bypass
     * the global scope (withTrashed). Defense-in-depth.
     *
     * @throws ModelNotFoundException
     */
    private function assertTenantOwnership(Customer $customer): void
    {
        if ($customer->tenant_id !== Auth::user()->tenant_id) {
            // Deliberately throw 404, not 403 — do not confirm the record exists
            throw new ModelNotFoundException();
        }
    }
}