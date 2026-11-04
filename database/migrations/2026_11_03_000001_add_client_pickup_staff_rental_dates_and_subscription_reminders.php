<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Carbon;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->string('pickup_address')->nullable();
            $table->string('pickup_phone', 50)->nullable();
            $table->text('pickup_instructions')->nullable();
        });

        Schema::table('admins', function (Blueprint $table) {
            $table->boolean('is_active')->default(true);
        });

        Schema::table('rentals', function (Blueprint $table) {
            $table->dateTime('rental_end')->nullable();
            $table->decimal('daily_rate', 10, 2)->nullable();
            $table->decimal('total_amount', 10, 2)->nullable();
            $table->string('pickup_address')->nullable();
            $table->string('pickup_phone', 50)->nullable();
            $table->text('pickup_instructions')->nullable();
        });

        DB::table('rentals')
            ->leftJoin('vehicles', 'rentals.vehicle_id', '=', 'vehicles.id')
            ->select('rentals.id', 'rentals.rental_start', 'vehicles.rate')
            ->orderBy('rentals.id')
            ->chunk(200, function ($rentals): void {
                foreach ($rentals as $rental) {
                    $dailyRate = (float) ($rental->rate ?? 0);
                    DB::table('rentals')->where('id', $rental->id)->update([
                        'rental_end' => Carbon::parse($rental->rental_start)->addDay(),
                        'daily_rate' => $dailyRate,
                        'total_amount' => $dailyRate,
                    ]);
                }
            });

        Schema::create('subscription_reminders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('clients')->cascadeOnDelete();
            $table->timestamp('subscription_due_at');
            $table->string('reminder_type', 40);
            $table->timestamp('sent_at')->nullable();
            $table->text('last_error')->nullable();
            $table->timestamps();
            $table->unique(['client_id', 'subscription_due_at', 'reminder_type'], 'subscription_reminders_once');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_reminders');

        Schema::table('rentals', function (Blueprint $table) {
            $table->dropColumn([
                'rental_end',
                'daily_rate',
                'total_amount',
                'pickup_address',
                'pickup_phone',
                'pickup_instructions',
            ]);
        });

        Schema::table('admins', function (Blueprint $table) {
            $table->dropColumn('is_active');
        });

        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn(['pickup_address', 'pickup_phone', 'pickup_instructions']);
        });
    }
};
