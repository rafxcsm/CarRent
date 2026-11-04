<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VehicleMaintenanceDate extends Model
{
    protected $fillable = [
        'vehicle_id',
        'maintenance_date',
        'note',
    ];

    protected $casts = [
        'maintenance_date' => 'date:Y-m-d',
    ];

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }
}
