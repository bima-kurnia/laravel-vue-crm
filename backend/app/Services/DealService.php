<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Deal;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;
<<<<<<< HEAD
=======
use Illuminate\Support\Facades\Log;
>>>>>>> 0bf0120 (feat: initial CRM applicationBackend (Laravel):- Multi-tenant schema: tenants, users, customers, deals, activities- BelongsToTenant trait with global Eloquent scope- Sanctum Bearer token authentication (login, logout, logout-all, me)- ResolveTenant middleware with active tenant check- Customers API: CRUD, soft delete, restore, force delete, JSONB custom fields- Deals API: CRUD, pipeline stages, stage history, pipeline summary endpoint- Activities API: immutable audit log, polymorphic subject feed, stage history- Role-based access: owner / admin / member gates and RequireRole middleware- Tenant registration endpoint with DB transaction- Users search endpoint for owner autocomplete- Form Request validation on all endpoints- Service layer architecture (no repositories)- CustomerService, DealService, ActivityService, AuthService, TenantServiceFrontend (Vue 3):- Vite + Vue Router + Pinia + PrimeVue (Aura preset)- Bearer token auth with localStorage persistence and auto-rehydration- useApi composable: fetch wrapper with global 401 handler- Auth store, Customer store, Deal store- Login and registration views- Customers: list with search/filter/pagination, detail view, create/edit dialogs- Deals: list with filters, detail view, pipeline stage mover with confirmation modal- Activities: global log view with subject and event filters- Dashboard: pipeline summary cards- CustomFieldsEditor: dynamic JSONB key/value editor with type inference- DealForm: currency dropdown (15 currencies), customer/owner autocomplete- Role-aware UI via useRole composable- AppShell layout with sidebar navigation and topbar)
use Illuminate\Validation\ValidationException;

class DealService
{
    public function __construct(private readonly ActivityService $activityService) {}

    // -------------------------------------------------------------------------
    // List
    // -------------------------------------------------------------------------

    public function list(array $filters): LengthAwarePaginator
    {
        $query = Deal::query()->with(['customer', 'owner']);

        if (! empty($filters['with_trashed'])) {
            $query->withTrashed();
        }

        if (! empty($filters['search'])) {
            $term = '%' . $filters['search'] . '%';
            $query->where('title', 'ilike', $term);
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['stage'])) {
            $query->where('stage', $filters['stage']);
        }

        if (! empty($filters['customer_id'])) {
            $query->where('customer_id', $filters['customer_id']);
        }

        if (! empty($filters['owner_id'])) {
            $query->where('owner_id', $filters['owner_id']);
        }

        if (! empty($filters['currency'])) {
            $query->where('currency', strtoupper($filters['currency']));
        }

        if (isset($filters['value_min'])) {
            $query->where('value', '>=', $filters['value_min']);
        }

        if (isset($filters['value_max'])) {
            $query->where('value', '<=', $filters['value_max']);
        }

        if (! empty($filters['close_date_from'])) {
            $query->whereDate('expected_close_date', '>=', $filters['close_date_from']);
        }

        if (! empty($filters['close_date_to'])) {
            $query->whereDate('expected_close_date', '<=', $filters['close_date_to']);
        }

        $sortBy  = $filters['sort_by']  ?? 'created_at';
        $sortDir = $filters['sort_dir'] ?? 'desc';
        $query->orderBy($sortBy, $sortDir);

        $perPage = $filters['per_page'] ?? 15;

        return $query->paginate($perPage);
    }

    // -------------------------------------------------------------------------
    // Pipeline summary (for dashboard aggregation)
    // -------------------------------------------------------------------------

    public function pipelineSummary(): array
    {
        $stages = ['prospecting', 'qualification', 'proposal', 'negotiation', 'closed'];

        $rows = Deal::query()
            ->selectRaw('stage, status, COUNT(*) as count, COALESCE(SUM(value), 0) as total_value')
            ->whereIn('stage', $stages)
            ->groupBy('stage', 'status')
            ->get();

        // Build a keyed structure: stage → status → { count, total_value }
        $summary = [];

        foreach ($stages as $stage) {
            $summary[$stage] = [
                'total_count' => 0,
                'total_value' => 0,
                'by_status'   => [],
            ];
        }

        foreach ($rows as $row) {
            $summary[$row->stage]['total_count'] += $row->count;
            $summary[$row->stage]['total_value'] += (float) $row->total_value;
            $summary[$row->stage]['by_status'][$row->status] = [
                'count'       => (int) $row->count,
                'total_value' => (float) $row->total_value,
            ];
        }

        return $summary;
    }

    // -------------------------------------------------------------------------
    // Show
    // -------------------------------------------------------------------------

    public function findOrFail(string $id): Deal
    {
        return Deal::with(['customer', 'owner'])->findOrFail($id);
    }

    // -------------------------------------------------------------------------
    // Create
    // -------------------------------------------------------------------------

    public function create(array $data): Deal
    {
<<<<<<< HEAD
=======
        Gate::authorize('manage-records');

>>>>>>> 0bf0120 (feat: initial CRM applicationBackend (Laravel):- Multi-tenant schema: tenants, users, customers, deals, activities- BelongsToTenant trait with global Eloquent scope- Sanctum Bearer token authentication (login, logout, logout-all, me)- ResolveTenant middleware with active tenant check- Customers API: CRUD, soft delete, restore, force delete, JSONB custom fields- Deals API: CRUD, pipeline stages, stage history, pipeline summary endpoint- Activities API: immutable audit log, polymorphic subject feed, stage history- Role-based access: owner / admin / member gates and RequireRole middleware- Tenant registration endpoint with DB transaction- Users search endpoint for owner autocomplete- Form Request validation on all endpoints- Service layer architecture (no repositories)- CustomerService, DealService, ActivityService, AuthService, TenantServiceFrontend (Vue 3):- Vite + Vue Router + Pinia + PrimeVue (Aura preset)- Bearer token auth with localStorage persistence and auto-rehydration- useApi composable: fetch wrapper with global 401 handler- Auth store, Customer store, Deal store- Login and registration views- Customers: list with search/filter/pagination, detail view, create/edit dialogs- Deals: list with filters, detail view, pipeline stage mover with confirmation modal- Activities: global log view with subject and event filters- Dashboard: pipeline summary cards- CustomFieldsEditor: dynamic JSONB key/value editor with type inference- DealForm: currency dropdown (15 currencies), customer/owner autocomplete- Role-aware UI via useRole composable- AppShell layout with sidebar navigation and topbar)
        $this->assertCustomerBelongsToTenant($data['customer_id']);
        $this->assertOwnerBelongsToTenant($data['owner_id']);

        $deal = Deal::create($data);
        $deal->load(['customer', 'owner']);

<<<<<<< HEAD
=======
        Log::info('deal.created', [
            'deal_id' => $deal->id,
            'name'    => $deal->title,
        ]);

>>>>>>> 0bf0120 (feat: initial CRM applicationBackend (Laravel):- Multi-tenant schema: tenants, users, customers, deals, activities- BelongsToTenant trait with global Eloquent scope- Sanctum Bearer token authentication (login, logout, logout-all, me)- ResolveTenant middleware with active tenant check- Customers API: CRUD, soft delete, restore, force delete, JSONB custom fields- Deals API: CRUD, pipeline stages, stage history, pipeline summary endpoint- Activities API: immutable audit log, polymorphic subject feed, stage history- Role-based access: owner / admin / member gates and RequireRole middleware- Tenant registration endpoint with DB transaction- Users search endpoint for owner autocomplete- Form Request validation on all endpoints- Service layer architecture (no repositories)- CustomerService, DealService, ActivityService, AuthService, TenantServiceFrontend (Vue 3):- Vite + Vue Router + Pinia + PrimeVue (Aura preset)- Bearer token auth with localStorage persistence and auto-rehydration- useApi composable: fetch wrapper with global 401 handler- Auth store, Customer store, Deal store- Login and registration views- Customers: list with search/filter/pagination, detail view, create/edit dialogs- Deals: list with filters, detail view, pipeline stage mover with confirmation modal- Activities: global log view with subject and event filters- Dashboard: pipeline summary cards- CustomFieldsEditor: dynamic JSONB key/value editor with type inference- DealForm: currency dropdown (15 currencies), customer/owner autocomplete- Role-aware UI via useRole composable- AppShell layout with sidebar navigation and topbar)
        $this->activityService->log($deal, 'created', [
            'after' => $data,
        ]);

        return $deal;
    }

    // -------------------------------------------------------------------------
    // Update
    // -------------------------------------------------------------------------

    public function update(string $id, array $data): Deal
    {
<<<<<<< HEAD
=======
        Gate::authorize('manage-records');

>>>>>>> 0bf0120 (feat: initial CRM applicationBackend (Laravel):- Multi-tenant schema: tenants, users, customers, deals, activities- BelongsToTenant trait with global Eloquent scope- Sanctum Bearer token authentication (login, logout, logout-all, me)- ResolveTenant middleware with active tenant check- Customers API: CRUD, soft delete, restore, force delete, JSONB custom fields- Deals API: CRUD, pipeline stages, stage history, pipeline summary endpoint- Activities API: immutable audit log, polymorphic subject feed, stage history- Role-based access: owner / admin / member gates and RequireRole middleware- Tenant registration endpoint with DB transaction- Users search endpoint for owner autocomplete- Form Request validation on all endpoints- Service layer architecture (no repositories)- CustomerService, DealService, ActivityService, AuthService, TenantServiceFrontend (Vue 3):- Vite + Vue Router + Pinia + PrimeVue (Aura preset)- Bearer token auth with localStorage persistence and auto-rehydration- useApi composable: fetch wrapper with global 401 handler- Auth store, Customer store, Deal store- Login and registration views- Customers: list with search/filter/pagination, detail view, create/edit dialogs- Deals: list with filters, detail view, pipeline stage mover with confirmation modal- Activities: global log view with subject and event filters- Dashboard: pipeline summary cards- CustomFieldsEditor: dynamic JSONB key/value editor with type inference- DealForm: currency dropdown (15 currencies), customer/owner autocomplete- Role-aware UI via useRole composable- AppShell layout with sidebar navigation and topbar)
        $deal = $this->findOrFail($id);

        if (isset($data['customer_id'])) {
            $this->assertCustomerBelongsToTenant($data['customer_id']);
        }

        if (isset($data['owner_id'])) {
            $this->assertOwnerBelongsToTenant($data['owner_id']);
        }

        $before = $deal->only(array_keys($data));

        // Track stage change as a dedicated event before applying update
        $stageChanged = isset($data['stage']) && $data['stage'] !== $deal->stage;
        $previousStage = $deal->stage;

        // No merge — CustomFieldsEditor always sends the complete desired state.
        // A full replace is correct. Merging caused key duplication on edits.
        
        $deal->update($data);
        $fresh = $deal->fresh();
        $fresh->load(['customer', 'owner']);

        // Log generic update
        $this->activityService->log(
            $deal,
            'updated',
            ActivityService::diff($before, $fresh->only(array_keys($data)))
        );

        // Log dedicated stage transition
        if ($stageChanged) {
            $this->activityService->log($deal, 'stage_changed', [
                'from' => $previousStage,
                'to'   => $fresh->stage,
            ]);
        }

        return $fresh;
    }

    // -------------------------------------------------------------------------
    // Move stage (dedicated single-purpose endpoint)
    // -------------------------------------------------------------------------

    public function moveStage(string $id, string $newStage): Deal
    {
<<<<<<< HEAD
=======
        Gate::authorize('manage-records');

>>>>>>> 0bf0120 (feat: initial CRM applicationBackend (Laravel):- Multi-tenant schema: tenants, users, customers, deals, activities- BelongsToTenant trait with global Eloquent scope- Sanctum Bearer token authentication (login, logout, logout-all, me)- ResolveTenant middleware with active tenant check- Customers API: CRUD, soft delete, restore, force delete, JSONB custom fields- Deals API: CRUD, pipeline stages, stage history, pipeline summary endpoint- Activities API: immutable audit log, polymorphic subject feed, stage history- Role-based access: owner / admin / member gates and RequireRole middleware- Tenant registration endpoint with DB transaction- Users search endpoint for owner autocomplete- Form Request validation on all endpoints- Service layer architecture (no repositories)- CustomerService, DealService, ActivityService, AuthService, TenantServiceFrontend (Vue 3):- Vite + Vue Router + Pinia + PrimeVue (Aura preset)- Bearer token auth with localStorage persistence and auto-rehydration- useApi composable: fetch wrapper with global 401 handler- Auth store, Customer store, Deal store- Login and registration views- Customers: list with search/filter/pagination, detail view, create/edit dialogs- Deals: list with filters, detail view, pipeline stage mover with confirmation modal- Activities: global log view with subject and event filters- Dashboard: pipeline summary cards- CustomFieldsEditor: dynamic JSONB key/value editor with type inference- DealForm: currency dropdown (15 currencies), customer/owner autocomplete- Role-aware UI via useRole composable- AppShell layout with sidebar navigation and topbar)
        $deal = $this->findOrFail($id);

        if ($deal->stage === $newStage) {
            return $deal; // idempotent — no-op if already at stage
        }

        $previousStage = $deal->stage;

        $deal->update(['stage' => $newStage]);
        $deal->refresh();
        $deal->load(['customer', 'owner']);

<<<<<<< HEAD
=======
        Log::info('deal.stage_changed', [
            'deal_id' => $deal->id,
            'name'    => $deal->title,
        ]);

>>>>>>> 0bf0120 (feat: initial CRM applicationBackend (Laravel):- Multi-tenant schema: tenants, users, customers, deals, activities- BelongsToTenant trait with global Eloquent scope- Sanctum Bearer token authentication (login, logout, logout-all, me)- ResolveTenant middleware with active tenant check- Customers API: CRUD, soft delete, restore, force delete, JSONB custom fields- Deals API: CRUD, pipeline stages, stage history, pipeline summary endpoint- Activities API: immutable audit log, polymorphic subject feed, stage history- Role-based access: owner / admin / member gates and RequireRole middleware- Tenant registration endpoint with DB transaction- Users search endpoint for owner autocomplete- Form Request validation on all endpoints- Service layer architecture (no repositories)- CustomerService, DealService, ActivityService, AuthService, TenantServiceFrontend (Vue 3):- Vite + Vue Router + Pinia + PrimeVue (Aura preset)- Bearer token auth with localStorage persistence and auto-rehydration- useApi composable: fetch wrapper with global 401 handler- Auth store, Customer store, Deal store- Login and registration views- Customers: list with search/filter/pagination, detail view, create/edit dialogs- Deals: list with filters, detail view, pipeline stage mover with confirmation modal- Activities: global log view with subject and event filters- Dashboard: pipeline summary cards- CustomFieldsEditor: dynamic JSONB key/value editor with type inference- DealForm: currency dropdown (15 currencies), customer/owner autocomplete- Role-aware UI via useRole composable- AppShell layout with sidebar navigation and topbar)
        $this->activityService->log($deal, 'stage_changed', [
            'from' => $previousStage,
            'to'   => $newStage,
        ]);

        return $deal;
    }

    // -------------------------------------------------------------------------
    // Soft Delete
    // -------------------------------------------------------------------------

    public function delete(string $id): void
    {
<<<<<<< HEAD
        $deal = $this->findOrFail($id);
        $deal->delete();

=======
        Gate::authorize('manage-records');

        $deal = $this->findOrFail($id);
        $deal->delete();

        Log::info('deal.deleted', [
            'deal_id' => $deal->id,
            'name'    => $deal->title,
        ]);

>>>>>>> 0bf0120 (feat: initial CRM applicationBackend (Laravel):- Multi-tenant schema: tenants, users, customers, deals, activities- BelongsToTenant trait with global Eloquent scope- Sanctum Bearer token authentication (login, logout, logout-all, me)- ResolveTenant middleware with active tenant check- Customers API: CRUD, soft delete, restore, force delete, JSONB custom fields- Deals API: CRUD, pipeline stages, stage history, pipeline summary endpoint- Activities API: immutable audit log, polymorphic subject feed, stage history- Role-based access: owner / admin / member gates and RequireRole middleware- Tenant registration endpoint with DB transaction- Users search endpoint for owner autocomplete- Form Request validation on all endpoints- Service layer architecture (no repositories)- CustomerService, DealService, ActivityService, AuthService, TenantServiceFrontend (Vue 3):- Vite + Vue Router + Pinia + PrimeVue (Aura preset)- Bearer token auth with localStorage persistence and auto-rehydration- useApi composable: fetch wrapper with global 401 handler- Auth store, Customer store, Deal store- Login and registration views- Customers: list with search/filter/pagination, detail view, create/edit dialogs- Deals: list with filters, detail view, pipeline stage mover with confirmation modal- Activities: global log view with subject and event filters- Dashboard: pipeline summary cards- CustomFieldsEditor: dynamic JSONB key/value editor with type inference- DealForm: currency dropdown (15 currencies), customer/owner autocomplete- Role-aware UI via useRole composable- AppShell layout with sidebar navigation and topbar)
        $this->activityService->log($deal, 'deleted', [
            'title' => $deal->title,
        ]);
    }

    // -------------------------------------------------------------------------
    // Restore
    // -------------------------------------------------------------------------

    public function restore(string $id): Deal
    {
<<<<<<< HEAD
=======
        Gate::authorize('force-delete'); 

>>>>>>> 0bf0120 (feat: initial CRM applicationBackend (Laravel):- Multi-tenant schema: tenants, users, customers, deals, activities- BelongsToTenant trait with global Eloquent scope- Sanctum Bearer token authentication (login, logout, logout-all, me)- ResolveTenant middleware with active tenant check- Customers API: CRUD, soft delete, restore, force delete, JSONB custom fields- Deals API: CRUD, pipeline stages, stage history, pipeline summary endpoint- Activities API: immutable audit log, polymorphic subject feed, stage history- Role-based access: owner / admin / member gates and RequireRole middleware- Tenant registration endpoint with DB transaction- Users search endpoint for owner autocomplete- Form Request validation on all endpoints- Service layer architecture (no repositories)- CustomerService, DealService, ActivityService, AuthService, TenantServiceFrontend (Vue 3):- Vite + Vue Router + Pinia + PrimeVue (Aura preset)- Bearer token auth with localStorage persistence and auto-rehydration- useApi composable: fetch wrapper with global 401 handler- Auth store, Customer store, Deal store- Login and registration views- Customers: list with search/filter/pagination, detail view, create/edit dialogs- Deals: list with filters, detail view, pipeline stage mover with confirmation modal- Activities: global log view with subject and event filters- Dashboard: pipeline summary cards- CustomFieldsEditor: dynamic JSONB key/value editor with type inference- DealForm: currency dropdown (15 currencies), customer/owner autocomplete- Role-aware UI via useRole composable- AppShell layout with sidebar navigation and topbar)
        $deal = Deal::withTrashed()->findOrFail($id);

        $this->assertTenantOwnership($deal);

        $deal->restore();
        $deal->load(['customer', 'owner']);

        $this->activityService->log($deal, 'restored', [
            'title' => $deal->title,
        ]);

        return $deal;
    }

    // -------------------------------------------------------------------------
    // Hard Delete
    // -------------------------------------------------------------------------

    public function forceDelete(string $id): void
    {
<<<<<<< HEAD
=======
        Gate::authorize('force-delete');

>>>>>>> 0bf0120 (feat: initial CRM applicationBackend (Laravel):- Multi-tenant schema: tenants, users, customers, deals, activities- BelongsToTenant trait with global Eloquent scope- Sanctum Bearer token authentication (login, logout, logout-all, me)- ResolveTenant middleware with active tenant check- Customers API: CRUD, soft delete, restore, force delete, JSONB custom fields- Deals API: CRUD, pipeline stages, stage history, pipeline summary endpoint- Activities API: immutable audit log, polymorphic subject feed, stage history- Role-based access: owner / admin / member gates and RequireRole middleware- Tenant registration endpoint with DB transaction- Users search endpoint for owner autocomplete- Form Request validation on all endpoints- Service layer architecture (no repositories)- CustomerService, DealService, ActivityService, AuthService, TenantServiceFrontend (Vue 3):- Vite + Vue Router + Pinia + PrimeVue (Aura preset)- Bearer token auth with localStorage persistence and auto-rehydration- useApi composable: fetch wrapper with global 401 handler- Auth store, Customer store, Deal store- Login and registration views- Customers: list with search/filter/pagination, detail view, create/edit dialogs- Deals: list with filters, detail view, pipeline stage mover with confirmation modal- Activities: global log view with subject and event filters- Dashboard: pipeline summary cards- CustomFieldsEditor: dynamic JSONB key/value editor with type inference- DealForm: currency dropdown (15 currencies), customer/owner autocomplete- Role-aware UI via useRole composable- AppShell layout with sidebar navigation and topbar)
        $deal = Deal::withTrashed()->findOrFail($id);

        $this->assertTenantOwnership($deal);

<<<<<<< HEAD
=======
        // Elevated action — use warning level so it surfaces in alerting
        Log::warning('deal.force_deleted', [
            'deal_id' => $deal->id,
            'name'    => $deal->title,
        ]);

>>>>>>> 0bf0120 (feat: initial CRM applicationBackend (Laravel):- Multi-tenant schema: tenants, users, customers, deals, activities- BelongsToTenant trait with global Eloquent scope- Sanctum Bearer token authentication (login, logout, logout-all, me)- ResolveTenant middleware with active tenant check- Customers API: CRUD, soft delete, restore, force delete, JSONB custom fields- Deals API: CRUD, pipeline stages, stage history, pipeline summary endpoint- Activities API: immutable audit log, polymorphic subject feed, stage history- Role-based access: owner / admin / member gates and RequireRole middleware- Tenant registration endpoint with DB transaction- Users search endpoint for owner autocomplete- Form Request validation on all endpoints- Service layer architecture (no repositories)- CustomerService, DealService, ActivityService, AuthService, TenantServiceFrontend (Vue 3):- Vite + Vue Router + Pinia + PrimeVue (Aura preset)- Bearer token auth with localStorage persistence and auto-rehydration- useApi composable: fetch wrapper with global 401 handler- Auth store, Customer store, Deal store- Login and registration views- Customers: list with search/filter/pagination, detail view, create/edit dialogs- Deals: list with filters, detail view, pipeline stage mover with confirmation modal- Activities: global log view with subject and event filters- Dashboard: pipeline summary cards- CustomFieldsEditor: dynamic JSONB key/value editor with type inference- DealForm: currency dropdown (15 currencies), customer/owner autocomplete- Role-aware UI via useRole composable- AppShell layout with sidebar navigation and topbar)
        $this->activityService->log($deal, 'force_deleted', [
            'title' => $deal->title,
        ]);

        $deal->forceDelete();
    }

    // -------------------------------------------------------------------------
    // Guards
    // -------------------------------------------------------------------------

    /**
     * Ensure the customer exists and belongs to the current tenant.
     * Uses the BelongsToTenant global scope — if it resolves, it's safe.
     *
     * @throws ValidationException
     */
    private function assertCustomerBelongsToTenant(string $customerId): void
    {
        $exists = Customer::where('id', $customerId)->exists();

        if (! $exists) {
            throw ValidationException::withMessages([
                'customer_id' => 'The selected customer does not exist or does not belong to your account.',
            ]);
        }
    }

    /**
     * Ensure the owner user exists and belongs to the current tenant.
     * User does not have BelongsToTenant, so we scope manually.
     *
     * @throws ValidationException
     */
    private function assertOwnerBelongsToTenant(string $ownerId): void
    {
        $exists = User::where('id', $ownerId)
            ->where('tenant_id', Auth::user()->tenant_id)
            ->exists();

        if (! $exists) {
            throw ValidationException::withMessages([
                'owner_id' => 'The selected owner does not exist or does not belong to your account.',
            ]);
        }
    }

    /**
     * Explicit tenant check for withTrashed operations.
     *
     * @throws ModelNotFoundException
     */
    private function assertTenantOwnership(Deal $deal): void
    {
        if ($deal->tenant_id !== Auth::user()->tenant_id) {
            throw new ModelNotFoundException();
        }
    }
}