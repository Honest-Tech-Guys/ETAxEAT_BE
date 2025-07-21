<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use App\Models\Dish;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('dishes', function (Blueprint $table) {
            // Add the slug column after the name column
            $table->string('slug')->unique()->after('name')->nullable();
        });

        // Optional: Populate the slug for existing dishes
        // This ensures that your old data has slugs.
        $dishes = Dish::all();
        foreach ($dishes as $dish) {
            $dish->slug = Str::slug($dish->name, '-');
            $dish->save();
        }

        // Make the column not nullable after populating it
        Schema::table('dishes', function (Blueprint $table) {
            $table->string('slug')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('dishes', function (Blueprint $table) {
            // Drop the unique index first, then the column
            $table->dropUnique(['slug']);
            $table->dropColumn('slug');
        });
    }
};
