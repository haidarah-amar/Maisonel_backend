<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    
        

     
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeonDelete();
            $table->foreignId('appartment_id')->constrained()->cascadeonDelete();
            $table->integer('guest_count');
            $table->date('check_in_date');
            $table->date('check_out_date');
            $table->index(['appartment_id', 'check_in_date', 'check_out_date']);
            $table->decimal('price_per_night', 8, 2); // Max -> the number is total 8 numbers with 2 after decimal 999,999.99
            $table->decimal('total_cost', 10, 2); // Max -> the number is total 10 numbers with 2 after decimal 99,999,999.99
            $table->enum('status', ['pending', 'confirmed', 'completed', 'cancelled' , 'rejected' ])->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
