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
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade');
            $table->foreignId('shop_id')->constrained('shops')->onDelete('cascade');
            $table->foreignId('barber_id')->nullable()->constrained('barbers')->onDelete('set null');
            $table->date('booking_date');
            $table->string('unique_reference')->unique();
            $table->integer('booking_number');
            $table->decimal('total_amount', 10, 2);
            $table->enum('status', ['upcoming', 'completed', 'canceled','processing', 'rescheduled'])->default('upcoming');
            $table->string('cancellation_reason')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
