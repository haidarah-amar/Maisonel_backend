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
            $table->string('address');
            $table->integer('size');
            $table->string('title');
            $table->text('description');
            $table->float('price');
            $table->boolean('is_approved')->default(false); //admin approval
            $table->integer('bedrooms');
            $table->integer('bathrooms');
            $table->enum('is_favorite', ['yes', 'no'])->default('no');
            $table->enum('type',['apartment','house','studio','villa'])->default('apartment');
            $table->enum('rating',['1','2','3','4','5'])->default('3');
            $table->integer('views')->default(0);
            $table->string('location');
            $table->string('image_url')->nullable();
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
