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
        Schema::table('cycle_counts', function (Blueprint $table) {
            // Menambahkan kolom notes setelah kolom status, tipenya text agar bisa panjang
            $table->text('notes')->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('cycle_counts', function (Blueprint $table) {
            $table->dropColumn('notes');
        });
    }
};
