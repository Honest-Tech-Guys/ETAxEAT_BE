<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The specific tables to be updated.
     *
     * @var array
     */
    protected $tables = [
        'cuisines',
        'food_trucks',
        'events',
        'nature_produces',    // The table for NaturalProducer
        'recharging_stations' // The table for RechargingStation
    ];

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        foreach ($this->tables as $tableName) {
            if (Schema::hasTable($tableName)) {
                Schema::table($tableName, function (Blueprint $table) {
                    // Add a nullable text column for the location link.
                    // Using text is better for long URLs like Google Maps links.
                    $table->text('location_link')->nullable();
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        foreach ($this->tables as $tableName) {
             if (Schema::hasTable($tableName) && Schema::hasColumn($tableName, 'location_link')) {
                Schema::table($tableName, function (Blueprint $table) {
                    // Drop the column if the migration is rolled back
                    $table->dropColumn('location_link');
                });
            }
        }
    }
};
