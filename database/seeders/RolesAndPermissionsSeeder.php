<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Permission::create(['name' => 'manage users']);
        // Permission::create(['name' => 'manage roles']);
        
        // $admin = Role::create(['name' => 'admin']);
        // $admin->givePermissionTo(Permission::all());

        // $user = Role::create(['name' => 'user']);
    }
}
