<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('announcements', function (Blueprint $table) {
            $table->boolean('is_active')->default(true);
        });
        Schema::table('categories', function (Blueprint $table) {
            $table->boolean('is_active')->default(true);
        });
        Schema::table('charging_powers', function (Blueprint $table) {
            $table->boolean('is_active')->default(true);
        });
        Schema::table('charging_types', function (Blueprint $table) {
            $table->boolean('is_active')->default(true);
        });
        Schema::table('cuisines', function (Blueprint $table) {
            $table->boolean('is_active')->default(true);
        });
        Schema::table('cuisine_types', function (Blueprint $table) {
            $table->boolean('is_active')->default(true);
        });
        Schema::table('dishes', function (Blueprint $table) {
            $table->boolean('is_active')->default(true);
        });
        Schema::table('dish_categories', function (Blueprint $table) {
            $table->boolean('is_active')->default(true);
        });
        Schema::table('events', function (Blueprint $table) {
            $table->boolean('is_active')->default(true);
        });
        Schema::table('event_categories', function (Blueprint $table) {
            $table->boolean('is_active')->default(true);
        });
        Schema::table('event_types', function (Blueprint $table) {
            $table->boolean('is_active')->default(true);
        });
        Schema::table('food_trucks', function (Blueprint $table) {
            $table->boolean('is_active')->default(true);
        });
        Schema::table('food_truck_types', function (Blueprint $table) {
            $table->boolean('is_active')->default(true);
        });
        Schema::table('nature_produces', function (Blueprint $table) {
            $table->boolean('is_active')->default(true);
        });
        Schema::table('producer_types', function (Blueprint $table) {
            $table->boolean('is_active')->default(true);
        });
        Schema::table('product_types', function (Blueprint $table) {
            $table->boolean('is_active')->default(true);
        });
        Schema::table('recharging_categories', function (Blueprint $table) {
            $table->boolean('is_active')->default(true);
        });
        Schema::table('recharging_stations', function (Blueprint $table) {
            $table->boolean('is_active')->default(true);
        });
        Schema::table('vehicle_types', function (Blueprint $table) {
            $table->boolean('is_active')->default(true);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Drop is_active from all tables
        $tables = [
            'announcements', 'categories', 'charging_powers', 'charging_types',
            'cuisines', 'cuisine_types', 'dishes', 'dish_categories', 'events',
            'event_categories', 'event_types', 'food_trucks', 'food_truck_types',
            'nature_produces', 'producer_types', 'product_types',
            'recharging_categories', 'recharging_stations', 'vehicle_types'
        ];

        foreach ($tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->dropColumn('is_active');
            });
        }
    }
};