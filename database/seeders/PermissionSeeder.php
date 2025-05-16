<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Disable foreign key checks to safely truncate tables
        Schema::disableForeignKeyConstraints();

        // Clear permission cache
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Truncate roles and permissions
        Permission::truncate();
        Role::truncate();
        \DB::table('role_has_permissions')->truncate();
        \DB::table('model_has_roles')->truncate();
        \DB::table('model_has_permissions')->truncate();

        Schema::enableForeignKeyConstraints();

        // === 1. Define Modules and Actions ===
        $modules = ['user', 'role', 'menu', 'general-setting'];
        $actions = ['create', 'index', 'update', 'delete'];

        // === 2. Create Super Admin Role ===
        $adminRole = Role::firstOrCreate(['name' => 'super-admin']);

        // === 3. Create Permissions and assign to super-admin ===
        foreach ($modules as $module) {
            foreach ($actions as $action) {
                $permission = Permission::firstOrCreate([
                    'name' => "{$module}-{$action}",
                ]);
                $adminRole->givePermissionTo($permission);
            }
        }

        // === 4. Create Super Admin User if not exists ===
        $adminEmail = 'superadmin@example.com';
        $admin = User::firstOrCreate(
            ['email' => $adminEmail],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password'), // Change in production!
            ]
        );
        $admin->assignRole($adminRole);
    }
}
