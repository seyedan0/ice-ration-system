<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('daily_tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('citizen_id')->constrained('citizens');
            $table->foreignId('station_id')->constrained('stations');
            $table->date('ticket_date');
            $table->smallInteger('allocated_blocks');
            $table->enum('status', ['pending', 'claimed', 'expired'])->default('pending');
            $table->timestamp('claimed_at')->nullable();
            $table->foreignId('claimed_by_agent_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['citizen_id', 'ticket_date'], 'uq_citizen_date');
            $table->index(['station_id', 'ticket_date', 'status'], 'idx_station_date_status');
            $table->index('ticket_date');
            $table->index('status');
        });

        DB::statement('ALTER TABLE daily_tickets ADD CONSTRAINT chk_blocks_positive CHECK (allocated_blocks > 0)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_tickets');
    }
};
