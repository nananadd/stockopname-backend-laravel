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
        Schema::table('racks', function (Blueprint $table) {
            // Kategori A (Penting), B (Menengah), C (Jarang)
            $table->enum('category', ['A', 'B', 'C'])->default('C')->after('code');
            // Kapan terakhir kali rak ini disetujui (Approved) hasil hitungannya?
            $table->date('last_counted_at')->nullable()->after('category');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('racks', function (Blueprint $table) {
            //
        });
    }
};
