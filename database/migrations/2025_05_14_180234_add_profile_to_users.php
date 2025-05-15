<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('last_name')->nullable()->after('name');
            $table->string('phone')->nullable()->after('last_name');
            $table->text('avatar')->nullable()->after('phone');
            $table->ipAddress('ip_address')->nullable()->after('avatar');
            $table->timestamp('last_login')->nullable()->after('ip_address');
            $table->boolean('is_active')->default(true)->after('last_login');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
            'last_name',
            'avatar',
            'phone',
            'ip_address',
            'last_login',
            'is_active',
            ]);
        });
    }
};
