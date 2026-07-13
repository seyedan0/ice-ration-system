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
        Schema::create('trucks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('manager_id')->constrained('users');
            $table->string('plate_number', 50)->unique();
            $table->integer('capacity')->default(1000);
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['manager_id', 'is_active']);
        });

        Schema::table('deliveries', function (Blueprint $table) {
            $table->foreignId('truck_id')->nullable()->after('manager_id')->constrained('trucks')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('deliveries', function (Blueprint $table) {
            $table->dropForeign(['truck_id']);
            $table->dropColumn('truck_id');
        });

        Schema::dropIfExists('trucks');
    }
};
