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
        Schema::table('pusaka_users', function (Blueprint $table) {
            $table->string('nama_lengkap')->nullable()->after('name');
            $table->string('jabatan')->nullable()->after('nama_lengkap');
            $table->string('satker')->nullable()->after('jabatan');
            $table->string('golongan', 20)->nullable()->after('satker');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pusaka_users', function (Blueprint $table) {
            $table->dropColumn(['nama_lengkap', 'jabatan', 'satker', 'golongan']);
        });
    }
};
