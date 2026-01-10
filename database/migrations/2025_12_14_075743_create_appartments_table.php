<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */

    public function up(): void
    {
        Schema::create('appartments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_id')->constrained('users')->cascadeOnDelete();
            $table->string('city');
            $table->string('location');
            $table->integer('size');
            $table->string('title');
            $table->text('description');
            $table->float('price');
            $table->boolean('is_active')->default(0); // -1: rejected, 0: pending, 1: active
            $table->integer('is_approved')->default(0); // -1: rejected, 0: pending, 1: active
            $table->integer('bedrooms');
            $table->integer('bathrooms');
            $table->enum('type',['Apartment','House','Studio','Villa'])->default('Apartment');
            $table->integer('views')->default(0);
            $table->string('image_url')->nullable();
            $table->json('amenities')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appartments');
    }
};
