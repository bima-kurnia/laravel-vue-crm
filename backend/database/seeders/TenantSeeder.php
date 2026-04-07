<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use App\Models\Tenant;
use App\Models\User;

class TenantSeeder extends Seeder
{
    public function run(): void
    {
        // Create Tenant
        $tenant = Tenant::create([
            'id' => Str::uuid(),
            'name' => 'Acme Inc',
            'slug' => 'acme-inc',
            'is_active' => true,
        ]);

        // Create Users for this Tenant
        User::create([
            'id' => Str::uuid(),
            'tenant_id' => $tenant->id,
            'name' => 'Owner User',
            'email' => 'owner@acme.com',
            'password' => Hash::make('password'),
            'role' => 'owner',
        ]);

        User::create([
            'id' => Str::uuid(),
            'tenant_id' => $tenant->id,
            'name' => 'Admin User',
            'email' => 'admin@acme.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        User::create([
            'id' => Str::uuid(),
            'tenant_id' => $tenant->id,
            'name' => 'Member User',
            'email' => 'member@acme.com',
            'password' => Hash::make('password'),
            'role' => 'member',
        ]);
    }
}
