<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use App\Models\Rental;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(\Illuminate\Http\Request $request)
    {
        $clientId = $request->user()->client_id;
        $client = $request->user()->client;

        // Vehicle stats
        $totalCars = Vehicle::where('client_id', $clientId)->count();
        $availableCars = Vehicle::where('client_id', $clientId)->whereRaw('LOWER(status) = ?', ['available'])->count();
        $rentedCars = Vehicle::where('client_id', $clientId)->whereRaw('LOWER(status) = ?', ['rented'])->count();

        // Customer stats
        $totalCustomers = User::where('client_id', $clientId)->count();

        // Booking stats
        $bookingCountsRaw = Rental::where('client_id', $clientId)
            ->selectRaw('LOWER(status) as status, COUNT(*) as count')
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
                'client' => [
                    'name' => $client->name,
                    'pickup_address' => $client->pickup_address,
                    'pickup_phone' => $client->pickup_phone,
                    'subscription_status' => $client->refreshSubscriptionStatus()->subscription_status,
                    'subscription_due_at' => $client->subscription_due_at,
                    'grace_ends_at' => $client->grace_ends_at,
                ],
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
