<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicle_maintenance_dates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained()->cascadeOnDelete();
            $table->date('maintenance_date');
            $table->string('note', 255)->nullable();
            $table->timestamps();
            $table->unique(['vehicle_id', 'maintenance_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicle_maintenance_dates');
    }
};
