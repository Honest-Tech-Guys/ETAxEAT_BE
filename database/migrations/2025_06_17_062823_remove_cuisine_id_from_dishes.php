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
        Schema::table('dishes', function (Blueprint $table) {
            // Drop foreign key constraint first
            $table->dropForeign(['cuisine_id']);
            // Then drop the column
            $table->dropColumn('cuisine_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dishes', function (Blueprint $table) {
            $table->foreignId('cuisine_id')->constrained()->onDelete('cascade');
        });
    }
};