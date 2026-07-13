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
        Schema::create('deliveries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('station_id')->constrained('stations');
            $table->foreignId('manager_id')->constrained('users');
            $table->string('truck_plate', 50)->nullable();
            $table->integer('blocks_delivered');
            $table->enum('status', ['pending', 'confirmed', 'rejected'])->default('pending');
            $table->timestamp('submitted_at')->useCurrent();
            $table->timestamp('confirmed_at')->nullable();
            $table->foreignId('confirmed_by_agent_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();

            $table->index(['station_id', 'status']);
            $table->index('manager_id');
            $table->index('submitted_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deliveries');
    }
};
