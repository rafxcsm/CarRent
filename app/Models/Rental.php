<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rental extends Model
{
  protected $fillable = [
    'user_id',
    'vehicle_id',
    'rental_start',
    'rental_end',
    'daily_rate',
    'total_amount',
    'pickup_address',
    'pickup_phone',
    'pickup_instructions',
    'proof_file',
    'status',
    'discount_type',
    'discount_status',
    'discount_percent',
    'discount_amount',
    'qr_verified_at',
    'returned_at',
    'client_id',
];

  protected function casts(): array
  {
    return [
      'daily_rate' => 'decimal:2',
      'total_amount' => 'decimal:2',
    ];
  }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function user()
{
    return $this->belongsTo(User::class, 'user_id');
}

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class, 'vehicle_id');
    }
}
