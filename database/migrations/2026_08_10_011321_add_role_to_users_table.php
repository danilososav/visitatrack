<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('worker')->after('email');
            $table->string('phone')->nullable()->after('role');
            $table->decimal('base_lat', 10, 7)->nullable()->after('phone');
            $table->decimal('base_lng', 10, 7)->nullable()->after('base_lat');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'phone', 'base_lat', 'base_lng']);
        });
    }
};
