<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recharging_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->timestamps();
        });

        Schema::create('recharging_category_station', function (Blueprint $table) {
            $table->id();
            $table->foreignId('recharging_category_id')->constrained()->onDelete('cascade');
            $table->foreignId('recharging_station_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recharging_category_station');
        Schema::dropIfExists('recharging_categories');
    }
};
