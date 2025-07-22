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
        Schema::create('walking_customers', function (Blueprint $table) {
            $table->id();
            $table->string('fullname');
            $table->integer('age');
            $table->string('phone_number');
            $table->enum('gender',['male','female','others']);
            $table->string('address')->nullable();
            $table->string('email')->nullable();
            $table->string('occupation')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('walking_customers');
    }
};
