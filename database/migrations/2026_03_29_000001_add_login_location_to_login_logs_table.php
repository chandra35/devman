<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('login_logs', function (Blueprint $table) {
            $table->decimal('login_latitude', 10, 8)->nullable()->after('device_info');
            $table->decimal('login_longitude', 11, 8)->nullable()->after('login_latitude');
        });
    }

    public function down(): void
    {
        Schema::table('login_logs', function (Blueprint $table) {
            $table->dropColumn(['login_latitude', 'login_longitude']);
        });
    }
};
