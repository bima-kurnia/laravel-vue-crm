<?php

namespace App\Services;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class TenantService
{
    /**
     * Create a tenant and its first owner user in a single transaction.
     * Returns the plain-text Sanctum token so the caller can log the user in.
     */
    public function register(array $data): array
    {
        return DB::transaction(function () use ($data) {
            $tenant = Tenant::create([
                'name'      => $data['tenant_name'],
                'slug'      => $data['tenant_slug'],
                'is_active' => true,
            ]);

            $user = User::create([
                'tenant_id' => $tenant->id,
                'name'      => $data['name'],
                'email'     => $data['email'],
                'password'  => Hash::make($data['password']),
                'role'      => 'owner', // first user is always owner
            ]);

            $token = $user->createToken('web')->plainTextToken;

            return compact('tenant', 'user', 'token');
        });
    }
}