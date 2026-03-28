<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Permissions
        $permissions = [
            'admin-access',
            'view-dashboard',
            'view-users',
            'create-users',
            'edit-users',
            'delete-users',
            'view-roles',
            'create-roles',
            'edit-roles',
            'delete-roles',
            'view-permissions',
            'create-permissions',
            'edit-permissions',
            'delete-permissions',
            'scan-permissions',
            'view-profile',
            'edit-profile',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Roles
        $superAdmin = Role::firstOrCreate(['name' => 'Super Admin']);
        $superAdmin->givePermissionTo(Permission::all());

        $admin = Role::firstOrCreate(['name' => 'Admin']);
        $admin->givePermissionTo([
            'admin-access',
            'view-dashboard',
            'view-users',
            'create-users',
            'edit-users',
            'view-roles',
            'view-permissions',
            'view-profile',
            'edit-profile',
        ]);

        $operator = Role::firstOrCreate(['name' => 'Operator']);
        $operator->givePermissionTo([
            'admin-access',
            'view-dashboard',
            'view-profile',
            'edit-profile',
        ]);

        // Create Super Admin user
        $user = User::firstOrCreate(
            ['username' => 'superadmin'],
            [
                'name' => 'Super Admin',
                'email' => 'superadmin@devman.local',
                'password' => bcrypt('password'),
                'is_active' => true,
            ]
        );
        $user->assignRole('Super Admin');
    }
}
