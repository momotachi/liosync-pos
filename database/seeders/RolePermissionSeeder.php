<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            // Superadmin permissions (implicit - has all permissions)
            
            // Company Admin permissions
            'manage company settings',
            'view company reports',
            'manage branches',
            'manage users',
            'manage items',
            'view company sales',
            'view company purchases',

            // Branch Admin permissions
            'manage branch settings',
            'view branch reports',
            'manage branch users',
            'manage branch stock',
            'view branch sales',
            'view branch purchases',

            // Stock Admin permissions
            'manage stock',
            'view stock reports',
            'manage purchases',
            'view purchases',

            // Cashier permissions
            'process sales',
            'view own sales',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }

        // Create roles and assign permissions

        // 1. Superadmin - Has all permissions via wildcard
        $superadmin = Role::firstOrCreate([
            'name' => 'Superadmin',
            'guard_name' => 'web',
        ]);
        $superadmin->givePermissionTo(Permission::all());

        // 2. Company Admin
        $companyAdmin = Role::firstOrCreate([
            'name' => 'Company Admin',
            'guard_name' => 'web',
        ]);
        $companyAdmin->givePermissionTo([
            'manage company settings',
            'view company reports',
            'manage branches',
            'manage users',
            'manage items',
            'view company sales',
            'view company purchases',
        ]);

        // 3. Branch Admin
        $branchAdmin = Role::firstOrCreate([
            'name' => 'Branch Admin',
            'guard_name' => 'web',
        ]);
        $branchAdmin->givePermissionTo([
            'manage branch settings',
            'view branch reports',
            'manage branch users',
            'manage branch stock',
            'view branch sales',
            'view branch purchases',
        ]);

        // 4. Stock Admin
        $stockAdmin = Role::firstOrCreate([
            'name' => 'Stock Admin',
            'guard_name' => 'web',
        ]);
        $stockAdmin->givePermissionTo([
            'manage stock',
            'view stock reports',
            'manage purchases',
            'view purchases',
        ]);

        // 5. Cashier
        $cashier = Role::firstOrCreate([
            'name' => 'Cashier',
            'guard_name' => 'web',
        ]);
        $cashier->givePermissionTo([
            'process sales',
            'view own sales',
        ]);

        $this->command->info('Roles and permissions created successfully.');
    }
}
