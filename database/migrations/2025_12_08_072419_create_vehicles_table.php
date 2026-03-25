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
      Schema::create('vehicles', function (Blueprint $table) {
    $table->id();
    $table->string('reg_no')->unique();
    $table->string('brand');
    $table->string('color');
    $table->string('model');
    $table->decimal('rate', 10, 2);
    $table->string('status');
    $table->string('image'); // <-- ADD THIS
    $table->timestamps();
});


    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};
