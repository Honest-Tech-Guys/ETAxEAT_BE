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
        // Main events table
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('zip_code')->nullable();
            $table->string('country')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->timestamp('start_time')->nullable();
            $table->timestamp('end_time')->nullable();
            $table->text('about')->nullable();
            $table->longText('description')->nullable();
            $table->string('website_link')->nullable();
            $table->string('facebook_link')->nullable();
            $table->string('x_link')->nullable(); // For X (formerly Twitter)
            $table->string('main_image')->nullable();
            $table->json('images')->nullable();
            $table->timestamps();
        });

        // Event Categories
        Schema::create('event_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->timestamps();
        });

        // Event Types
        Schema::create('event_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->timestamps();
        });

        // Pivot table for categories and events
        Schema::create('event_category_event', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_category_id')->constrained()->onDelete('cascade');
            $table->foreignId('event_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });

        // Pivot table for types and events
        Schema::create('event_type_event', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_type_id')->constrained()->onDelete('cascade');
            $table->foreignId('event_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_type_event');
        Schema::dropIfExists('event_category_event');
        Schema::dropIfExists('event_types');
        Schema::dropIfExists('event_categories');
        Schema::dropIfExists('events');
    }
};
