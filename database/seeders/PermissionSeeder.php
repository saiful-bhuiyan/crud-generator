<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Menu;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Hash;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        // === 1. Create Roles and Permissions ===
        $modules = ['user', 'role', 'menu'];
        $actions = ['create', 'index', 'edit', 'delete'];

        $adminRole = Role::firstOrCreate(['name' => 'super-admin']);

        foreach ($modules as $module) {
            foreach ($actions as $action) {
                $permissionName = "{$module}-{$action}";
                $permission = Permission::firstOrCreate(['name' => $permissionName]);
                if (!$adminRole->hasPermissionTo($permission)) {
                    $adminRole->givePermissionTo($permission);
                }
            }
        }

        // === 2. Create Admin User ===
        // $adminEmail = 'admin@example.com';
        // if (!User::where('email', $adminEmail)->exists()) {
        //     $admin = User::create([
        //         'name' => 'Admin',
        //         'email' => $adminEmail,
        //         'password' => Hash::make('password'),
        //     ]);
        //     $admin->assignRole($adminRole);
        // }

        
    }
}
