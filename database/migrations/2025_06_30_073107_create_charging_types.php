<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('charging_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->timestamps();
        });

        Schema::create('charging_type_station', function (Blueprint $table) {
            $table->id();
            $table->foreignId('charging_type_id')->constrained()->onDelete('cascade');
            $table->foreignId('recharging_station_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('charging_type_station');
        Schema::dropIfExists('charging_types');
    }
};
