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
            $table->dropForeign(['truck_id']);
            $table->dropColumn('truck_id');
        });
        
        Schema::table('deliveries', function (Blueprint $table) {
            $table->dropForeign(['truck_id']);
            $table->dropColumn('truck_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('deliveries', function (Blueprint $table) {
            $table->foreignId('truck_id')->nullable()->after('driver_id')->constrained('trucks')->nullOnDelete();
        });
        
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('truck_id')->nullable()->after('manager_id')->constrained('trucks')->nullOnDelete();
        });
    }
};
