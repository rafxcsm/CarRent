<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubscriptionReminder extends Model
{
    protected $fillable = [
        'client_id',
        'subscription_due_at',
        'reminder_type',
        'sent_at',
        'last_error',
    ];

    protected function casts(): array
    {
        return [
            'subscription_due_at' => 'datetime',
            'sent_at' => 'datetime',
        ];
    }
}
