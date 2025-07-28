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
        Schema::create('foodtruck_type_foodtruck', function (Blueprint $table) {
            $table->id();
            $table->foreignId('foodtruck_id')->constrained()->onDelete('cascade');
            $table->foreignId('foodtruck_type_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('_food_truck_type__food_truck');
    }
};
