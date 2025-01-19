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
        Schema::create('slots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('saloon_id')->constrained('shops')->onDelete('cascade');
            $table->string('slot_name')->unique();
            $table->time('start_time');
            $table->time('end_time');
            $table->integer('max_bookings')->default(1);
            $table->boolean('is_recurring')->default(1); // 
            $table->boolean('is_active')->default(1); //(1 = active, 0 = inactive
            $table->boolean('delete_status')->default(1); // 1 => active, 0 => deleted
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('slots');
    }
};
