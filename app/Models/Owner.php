<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Owner extends Model
{
    use HasFactory;

      protected $fillable = [
        'full_name',
        'gender',
        'birthdate',
        'address',
        'contact_no',
        'license_no',
        'status',
    ];

   

    public function rentals()
    {
        return $this->hasMany(Rental::class);
    }

}
