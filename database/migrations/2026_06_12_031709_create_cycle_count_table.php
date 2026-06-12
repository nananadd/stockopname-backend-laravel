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
        Schema::create('cycle_count', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rack_id')->constrained('racks')->onDelete('cascade');
            $table->enum('status', ['draft', 'reviewed', 'approved', 'recount', 'submitted'])->nullable();
            $table->text('notes')->nullable();
            $table->date('schedule_at')->nullable();
            $table->foreignId('counted_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('started_at')->useCurrent()->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cycle_count');
    }
};
