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
        // Main table for nature produce locations
        Schema::create('nature_produces', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('zip_code')->nullable();
            $table->string('country')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->enum('status', ['open', 'closed'])->default('open');
            $table->text('about')->nullable();
            $table->longText('description')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->double('rating', 8, 2)->nullable()->default(0.00);
            $table->json('operating_hours')->nullable();
            $table->string('website_link')->nullable();
            $table->string('facebook_link')->nullable();
            $table->string('main_image')->nullable();
            $table->json('images')->nullable();
            $table->timestamps();
        });

        // Filter table for Producer Types
        Schema::create('producer_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->timestamps();
        });

        // Filter table for Product Types
        Schema::create('product_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->timestamps();
        });

        // Pivot table for nature_produces and producer_types
        Schema::create('nature_produce_producer_type', function (Blueprint $table) {
            $table->id();
            $table->foreignId('nature_produce_id')->constrained()->onDelete('cascade');
            $table->foreignId('producer_type_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });

        // Pivot table for nature_produces and product_types
        Schema::create('nature_produce_product_type', function (Blueprint $table) {
            $table->id();
            $table->foreignId('nature_produce_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_type_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nature_produce_product_type');
        Schema::dropIfExists('nature_produce_producer_type');
        Schema::dropIfExists('product_types');
        Schema::dropIfExists('producer_types');
        Schema::dropIfExists('nature_produces');
    }
};
