<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rentals', function (Blueprint $table) {
            $table->string('discount_type')->nullable();
            $table->string('discount_status')->default('none');
            $table->decimal('discount_percent', 5, 2)->default(0);
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->timestamp('qr_verified_at')->nullable();
            $table->timestamp('returned_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('rentals', function (Blueprint $table) {
            $table->dropColumn([
                'discount_type',
                'discount_status',
                'discount_percent',
                'discount_amount',
                'qr_verified_at',
                'returned_at',
            ]);
        });
    }
};
