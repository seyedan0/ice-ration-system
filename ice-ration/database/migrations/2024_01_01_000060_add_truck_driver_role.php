<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adds the `truck_driver` role and the `manager_id` self-FK to the users table.
 *
 * Drivers belong to a truck manager, who creates and manages their accounts.
 * Drivers do NOT own trucks — they pick a truck from their manager's fleet when
 * filing a delivery. Attribution on the resulting deliveries record is to the
 * manager_id (preserving existing accounting semantics).
 */
return new class extends Migration
{
    public function up(): void
    {
        // 1. Extend the role ENUM to include truck_driver.
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['super_admin', 'station_agent', 'truck_manager', 'truck_driver'])
                ->change();
        });

        // 2. Add nullable manager_id self-referencing FK.
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('manager_id')
                ->nullable()
                ->after('station_id')
                ->constrained('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['manager_id']);
            $table->dropColumn('manager_id');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['super_admin', 'station_agent', 'truck_manager'])
                ->change();
        });
    }
};
