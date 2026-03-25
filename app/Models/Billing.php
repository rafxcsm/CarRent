<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Billing extends Model
{
    use SoftDeletes; // ⭐ THIS IS THE KEY

    protected $fillable = [
        'customer',
        'vehicle',
        'total_amount',
        'bill_date',
    ];
}
