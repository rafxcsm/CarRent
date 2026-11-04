<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'contact_email',
        'pickup_address',
        'pickup_phone',
        'pickup_instructions',
        'status',
        'plan',
        'monthly_fee',
        'subscription_status',
        'subscription_started_at',
        'subscription_due_at',
        'grace_ends_at',
        'last_paid_at',
    ];

    protected function casts(): array
    {
        return [
            'monthly_fee' => 'decimal:2',
            'subscription_started_at' => 'datetime',
            'subscription_due_at' => 'datetime',
            'grace_ends_at' => 'datetime',
            'last_paid_at' => 'datetime',
        ];
    }

    public function admins()
    {
        return $this->hasMany(Admin::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function vehicles()
    {
        return $this->hasMany(Vehicle::class);
    }

    public function rentals()
    {
        return $this->hasMany(Rental::class);
    }

    public function payments()
    {
        return $this->hasMany(SubscriptionPayment::class);
    }

    public function latestPayment()
    {
        return $this->hasOne(SubscriptionPayment::class)->latestOfMany();
    }

    public function refreshSubscriptionStatus(): self
    {
        if ($this->status !== 'active') {
            return $this;
        }

        $now = now();

        if (
            $this->subscription_status === 'active'
            && $this->subscription_due_at
            && $now->greaterThan($this->subscription_due_at)
        ) {
            $dueAt = $this->subscription_due_at->copy();
            $this->update([
                'subscription_status' => 'past_due',
                'grace_ends_at' => $dueAt->addDays(7),
            ]);
        }

        if (
            $this->subscription_status === 'past_due'
            && $this->grace_ends_at
            && $now->greaterThan($this->grace_ends_at)
        ) {
            $this->update(['subscription_status' => 'suspended']);
        }

        return $this->refresh();
    }

    public function isReadOnly(): bool
    {
        return $this->status !== 'active' || $this->subscription_status === 'suspended';
    }
}
