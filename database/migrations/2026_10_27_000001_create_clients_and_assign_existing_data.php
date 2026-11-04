<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('contact_email')->nullable();
            $table->string('status')->default('active');
            $table->string('plan')->default('Monthly');
            $table->decimal('monthly_fee', 10, 2)->default(0);
            $table->string('subscription_status')->default('active');
            $table->timestamp('subscription_started_at')->nullable();
            $table->timestamp('subscription_due_at')->nullable();
            $table->timestamp('grace_ends_at')->nullable();
            $table->timestamp('last_paid_at')->nullable();
            $table->timestamps();
        });

        $clientId = DB::table('clients')->insertGetId([
            'name' => 'Rafakijay Car Rental',
            'slug' => 'rafakijay',
            'status' => 'active',
            'plan' => 'Monthly',
            'monthly_fee' => 0,
            'subscription_status' => 'active',
            'subscription_started_at' => now(),
            'subscription_due_at' => now()->addMonthNoOverflow(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        foreach (['admins', 'users', 'vehicles', 'rentals', 'owners'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->foreignId('client_id')->nullable()->constrained('clients')->restrictOnDelete();
            });

            $query = DB::table($tableName)->whereNull('client_id');
            if ($tableName === 'admins') {
                $query->where('role', 'admin');
            }
            $query->update(['client_id' => $clientId]);
        }

        // User identity and vehicle registration numbers are unique per rental business.
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique('users_email_unique');
            $table->dropUnique('users_license_unique');
            $table->unique(['client_id', 'email']);
            $table->unique(['client_id', 'license']);
        });

        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropUnique('vehicles_reg_no_unique');
            $table->unique(['client_id', 'reg_no']);
        });

        Schema::create('subscription_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('clients')->restrictOnDelete();
            $table->foreignId('recorded_by_admin_id')->constrained('admins')->restrictOnDelete();
            $table->decimal('amount', 10, 2);
            $table->timestamp('period_start');
            $table->timestamp('period_end');
            $table->string('payment_method')->default('manual');
            $table->string('reference')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_payments');

        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropUnique(['client_id', 'reg_no']);
            $table->unique('reg_no');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['client_id', 'email']);
            $table->dropUnique(['client_id', 'license']);
            $table->unique('email');
            $table->unique('license');
        });

        foreach (['owners', 'rentals', 'vehicles', 'users', 'admins'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->dropConstrainedForeignId('client_id');
            });
        }

        Schema::dropIfExists('clients');
    }
};
