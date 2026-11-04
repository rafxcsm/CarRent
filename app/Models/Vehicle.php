<?php

namespace App\Models;

use App\Support\SupabaseStorage;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Vehicle extends Model
{
      protected $appends = ['image_url'];

      protected $fillable = [
        'reg_no', 'brand', 'color', 'model', 'rate', 'status', 'image', 'client_id'
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function maintenanceDates()
    {
        return $this->hasMany(VehicleMaintenanceDate::class);
    }

    public function rentals()
    {
        return $this->hasMany(Rental::class);
    }

    public function getImageUrlAttribute(): ?string
    {
        $image = $this->getRawOriginal('image');
        if (!filled($image)) {
            return null;
        }

        if (Str::startsWith($image, ['http://', 'https://'])) {
            return $image;
        }

        $storage = app(SupabaseStorage::class);
        if ($storage->configured()) {
            $path = Str::startsWith($image, 'vehicles/') ? $image : 'vehicles/'.basename($image);

            return $storage->publicUrl(config('services.supabase.vehicle_bucket'), $path);
        }

        return asset('uploads/vehicles/'.basename($image));
    }
}
