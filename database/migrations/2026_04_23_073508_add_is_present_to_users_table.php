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
            // Tambahkan is_present (tipe boolean/tinyint). 
            // Default 1 (true) artinya semua staf diasumsikan masuk, kecuali di-set 0 (false)
            $table->boolean('is_present')->default(true)->after('role_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Hapus kolom jika kita melakukan rollback
            $table->dropColumn('is_present');
        });
    }
};