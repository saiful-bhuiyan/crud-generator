<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class MenusTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('menus')->delete();
        
        \DB::table('menus')->insert(array (
            0 => 
            array (
                'id' => 1,
                'title' => 'Menus',
                'route' => 'menus',
                'icon' => 'fa fa-bars',
                'permission_name' => 'menu-index',
                'parent_id' => NULL,
                'order' => 0,
                'created_at' => '2025-09-26 14:09:21',
                'updated_at' => '2025-09-26 14:09:21',
            ),
            1 => 
            array (
                'id' => 2,
                'title' => 'Roles',
                'route' => 'roles',
                'icon' => 'fa fa-bars',
                'permission_name' => 'role-index',
                'parent_id' => NULL,
                'order' => 0,
                'created_at' => '2025-09-26 14:10:08',
                'updated_at' => '2025-09-26 14:12:26',
            ),
            2 => 
            array (
                'id' => 3,
                'title' => 'Users',
                'route' => 'users',
                'icon' => 'fa fa-user',
                'permission_name' => 'user-index',
                'parent_id' => NULL,
                'order' => 0,
                'created_at' => '2025-09-26 14:15:26',
                'updated_at' => '2025-09-26 14:15:26',
            ),
        ));
        
        
    }
}