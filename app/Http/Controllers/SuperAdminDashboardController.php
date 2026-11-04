<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\Client;
use App\Models\Rental;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Support\Facades\DB;

class SuperAdminDashboardController extends Controller
{
    public function index()
    {
        Client::query()->get()->each->refreshSubscriptionStatus();

        $rentalStatuses = Rental::query()
            ->selectRaw('LOWER(status) as status, COUNT(*) as total')
            ->groupBy(DB::raw('LOWER(status)'))
            ->pluck('total', 'status');

        return response()->json([
            'success' => true,
            'data' => [
                'generated_at' => now()->toIso8601String(),
                'metrics' => [
                    'clients' => Client::where('status', 'active')->count(),
                    'past_due_clients' => Client::where('subscription_status', 'past_due')->count(),
                    'suspended_clients' => Client::where('subscription_status', 'suspended')->count(),
                    'active_monthly_value' => Client::where('status', 'active')
                        ->where('subscription_status', 'active')
                        ->sum('monthly_fee'),
                    'admin_accounts' => Admin::where('role', 'admin')->count(),
                    'customers' => User::count(),
                    'vehicles' => Vehicle::count(),
                    'rentals' => Rental::count(),
                    'rentals_this_month' => Rental::where('created_at', '>=', now()->startOfMonth())->count(),
                ],
                'rental_statuses' => $rentalStatuses,
            ],
        ]);
    }
}
