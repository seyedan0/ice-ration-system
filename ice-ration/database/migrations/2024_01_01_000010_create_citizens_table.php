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
        Schema::create('citizens', function (Blueprint $table) {
            $table->id();
            $table->string('full_name', 200);
            $table->string('national_id', 20)->unique();
            $table->string('mobile', 20)->unique();
            $table->string('qr_code', 100)->unique();
            $table->smallInteger('daily_ration')->default(1);
            $table->foreignId('preferred_station_id')->constrained('stations');
            $table->boolean('is_active')->default(true);
            $table->timestamp('registered_at')->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrent()->useCurrentOnUpdate();

            $table->index('is_active');
        });

        // SQLite doesn't support ADD CONSTRAINT CHECK in ALTER TABLE
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement('ALTER TABLE citizens ADD CONSTRAINT chk_ration_positive CHECK (daily_ration > 0)');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('citizens');
    }
};
