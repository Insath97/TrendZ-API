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
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('saloon_id')->constrained('shops')->onDelete('cascade');
            $table->string('code')->unique();
            $table->string('name');
            $table->string('icon');
            $table->text('description');
            $table->decimal('price');
            $table->integer('duration');
            $table->boolean('status')->default(1);
            $table->boolean('delete_status')->default(1); // 1 => active, 0 => deleted
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};
