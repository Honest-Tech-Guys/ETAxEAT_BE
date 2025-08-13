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
        Schema::table('cuisines', function (Blueprint $table) {
            $table->string('youtube_link')->nullable();
        });

        Schema::table('food_trucks', function (Blueprint $table) {
            $table->string('youtube_link')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cuisines', function (Blueprint $table) {
            $table->dropColumn('youtube_link');
        });

        Schema::table('food_trucks', function (Blueprint $table) {
            $table->dropColumn('youtube_link');
        });
    }
};
