<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubscriptionPayment extends Model
{
    protected $fillable = [
        'client_id',
        'recorded_by_admin_id',
        'amount',
        'period_start',
        'period_end',
        'payment_method',
        'reference',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'period_start' => 'datetime',
            'period_end' => 'datetime',
        ];
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function recordedBy()
    {
        return $this->belongsTo(Admin::class, 'recorded_by_admin_id');
    }
}
