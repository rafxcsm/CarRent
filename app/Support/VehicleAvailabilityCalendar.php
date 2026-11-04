<?php

namespace App\Support;

use App\Models\Rental;
use App\Models\Vehicle;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class VehicleAvailabilityCalendar
{
    public function entries(Vehicle $vehicle, string $from, string $to, bool $includeCustomer = false): Collection
    {
        $fromDate = Carbon::parse($from)->startOfDay();
        $toDate = Carbon::parse($to)->startOfDay();
        $toExclusive = $toDate->copy()->addDay();
        $entries = [];

        $rentals = Rental::query()
            ->where('client_id', $vehicle->client_id)
            ->where('vehicle_id', $vehicle->id)
            ->whereIn('status', ['pending', 'approved'])
            ->where('rental_start', '<', $toExclusive)
            ->where('rental_end', '>', $fromDate->toDateTimeString())
            ->when($includeCustomer, fn ($query) => $query->with('user:id,name'))
            ->get();

        foreach ($rentals as $rental) {
            $rentalStart = Carbon::parse($rental->rental_start);
            $rentalEnd = $rental->rental_end
                ? Carbon::parse($rental->rental_end)
                : $rentalStart->copy()->addDay();
            $lastOccupiedDate = $rentalEnd->copy()->subSecond()->startOfDay();
            $day = $rentalStart->copy()->startOfDay()->max($fromDate);

            while ($day->lte($lastOccupiedDate) && $day->lte($toDate)) {
                $date = $day->toDateString();
                $entries[$date] = [
                    'date' => $date,
                    'type' => 'booked',
                    'label' => 'Booked',
                    'status' => $rental->status,
                    ...($includeCustomer ? ['customer' => $rental->user?->name ?? 'Customer'] : []),
                ];
                $day->addDay();
            }
        }

        $maintenanceDates = $vehicle->maintenanceDates()
            ->whereBetween('maintenance_date', [$fromDate->toDateString(), $toDate->toDateString()])
            ->get(['maintenance_date', 'note']);

        foreach ($maintenanceDates as $maintenance) {
            $date = Carbon::parse($maintenance->maintenance_date)->toDateString();
            $entries[$date] = [
                'date' => $date,
                'type' => 'maintenance',
                'label' => $maintenance->note ?: 'Under maintenance',
            ];
        }

        ksort($entries);
        return collect(array_values($entries));
    }

    public function hasMaintenanceConflict(Vehicle $vehicle, Carbon $start, Carbon $end): bool
    {
        return $vehicle->maintenanceDates()
            ->whereBetween('maintenance_date', [$start->toDateString(), $end->copy()->subSecond()->toDateString()])
            ->exists();
    }

    public function hasBookingConflict(int $clientId, int $vehicleId, Carbon $start, Carbon $end, ?int $exceptRentalId = null): bool
    {
        return Rental::query()
            ->where('client_id', $clientId)
            ->where('vehicle_id', $vehicleId)
            ->whereIn('status', ['pending', 'approved'])
            ->where('rental_start', '<', $end)
            ->where('rental_end', '>', $start->toDateTimeString())
            ->when($exceptRentalId, fn ($query) => $query->where('id', '!=', $exceptRentalId))
            ->exists();
    }
}
