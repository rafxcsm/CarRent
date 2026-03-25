<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rentals', function (Blueprint $table) {
            if (Schema::hasColumn('rentals', 'rental_type')) {
                $table->dropColumn('rental_type');
            }

            if (Schema::hasColumn('rentals', 'rental_end')) {
                $table->dropColumn('rental_end');
            }

            if (Schema::hasColumn('rentals', 'duration')) {
                $table->dropColumn('duration');
            }

            if (!Schema::hasColumn('rentals', 'proof_file')) {
                $table->string('proof_file')->nullable()->after('rental_start');
            }
        });
    }

    public function down(): void
    {
        Schema::table('rentals', function (Blueprint $table) {
            if (!Schema::hasColumn('rentals', 'rental_type')) {
                $table->string('rental_type')->nullable();
            }

            if (!Schema::hasColumn('rentals', 'rental_end')) {
                $table->dateTime('rental_end')->nullable();
            }

            if (!Schema::hasColumn('rentals', 'duration')) {
                $table->string('duration')->nullable();
            }

            if (Schema::hasColumn('rentals', 'proof_file')) {
                $table->dropColumn('proof_file');
            }
        });
    }
};