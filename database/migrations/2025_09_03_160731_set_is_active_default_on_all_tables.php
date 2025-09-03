<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The tables to be updated.
     *
     * @var array
     */
    protected $tables = [
        'announcements', 'categories', 'charging_powers', 'charging_types',
        'cuisines', 'cuisine_types', 'dishes', 'dish_categories', 'events',
        'event_categories', 'event_types', 'food_trucks', 'food_truck_types',
        'nature_produces', 'producer_types', 'product_types',
        'recharging_categories', 'recharging_stations', 'vehicle_types'
    ];

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        foreach ($this->tables as $tableName) {
            // Check if the table exists before attempting to modify it
            if (Schema::hasTable($tableName)) {
                Schema::table($tableName, function (Blueprint $table) {
                    // Change the is_active column to have a default value of true
                    $table->boolean('is_active')->default(true)->change();
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
             if (Schema::hasTable($tableName)) {
                Schema::table($tableName, function (Blueprint $table) {
                    // Revert the column to not have a default value
                    $table->boolean('is_active')->default(null)->change();
                });
            }
        }
    }
};
