<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GeneralSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Remove the legacy sidebar_theme setting
        DB::table('general_settings')->where('type', 'sidebar_theme')->delete();

        // Seed Primary Color
        DB::table('general_settings')->updateOrInsert(
            ['type' => 'primary_color'],
            ['value' => '#1b2850', 'created_at' => now(), 'updated_at' => now()]
        );

        // Seed Secondary Color
        DB::table('general_settings')->updateOrInsert(
            ['type' => 'secondary_color'],
            ['value' => '#ff9f43', 'created_at' => now(), 'updated_at' => now()]
        );

        // Seed Sidebar Background Color
        DB::table('general_settings')->updateOrInsert(
            ['type' => 'sidebar_bg_color'],
            ['value' => '#ffffff', 'created_at' => now(), 'updated_at' => now()]
        );

        // Seed Sidebar Text Color
        DB::table('general_settings')->updateOrInsert(
            ['type' => 'sidebar_text_color'],
            ['value' => '#637381', 'created_at' => now(), 'updated_at' => now()]
        );
    }
}
