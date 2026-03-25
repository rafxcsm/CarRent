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
        Schema::create('rentals', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('customer_id');
        $table->unsignedBigInteger('vehicle_id');
        $table->string('rental_type');
        $table->dateTime('rental_start');
        $table->dateTime('rental_end');
        $table->string('duration');
        $table->string('status')->default('pending'); // ← include status here
        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rentals');
    }
};
