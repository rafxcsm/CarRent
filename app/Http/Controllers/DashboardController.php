<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Vehicle;
use App\Models\Rental;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // Vehicle stats
        $totalCars = Vehicle::count();
        $availableCars = Vehicle::where('status', 'available')->count();
        $rentedCars = Vehicle::where('status', 'rented')->count();

        // Customer stats
        $totalCustomers = User::count();

        // Booking stats
        $bookingCountsRaw = Rental::selectRaw('LOWER(status) as status, COUNT(*) as count')
            ->groupBy(DB::raw('LOWER(status)'))
            ->get();

        // Ensure all statuses exist, default to 0
        $bookingCounts = [
            'approved' => 0,
            'pending'  => 0,
            'denied'   => 0,
        ];

        foreach ($bookingCountsRaw as $row) {
            $status = strtolower($row->status);
            if (array_key_exists($status, $bookingCounts)) {
                $bookingCounts[$status] = $row->count;
            }
        }

        return response()->json([
            'success' => true,
            'data' => [
                // Top 4 stats for boxes
                'stats' => [
                    ['title' => 'Total Cars', 'value' => $totalCars],
                    ['title' => 'Available Cars', 'value' => $availableCars],
                    ['title' => 'Rented Cars', 'value' => $rentedCars],
                    ['title' => 'Total Customers', 'value' => $totalCustomers],
                ],

                // Booking status for pie chart
                'bookings' => $bookingCounts,
            ],
        ]);
    }
}