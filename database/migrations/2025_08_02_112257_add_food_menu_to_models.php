<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cuisines', function (Blueprint $table) {
            $table->json('food_menu')->nullable()->after('features');
        });

        Schema::table('food_trucks', function (Blueprint $table) {
            $table->json('food_menu')->nullable()->after('is_active');
        });
    }

    public function down(): void
    {
        Schema::table('cuisines', function (Blueprint $table) {
            $table->dropColumn('food_menu');
        });

        Schema::table('food_trucks', function (Blueprint $table) {
            $table->dropColumn('food_menu');
        });
    }
};
