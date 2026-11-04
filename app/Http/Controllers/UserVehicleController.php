<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Vehicle;
use App\Models\Rental;
use Illuminate\Support\Carbon;
use App\Support\VehicleAvailabilityCalendar;

class UserVehicleController extends Controller
{
    public function index(Request $request)
    {
        $client = $request->user()->client;
        $vehicles = Vehicle::where('client_id', $client->id)->latest()->get();

        return response()->json([
            'success' => true,
            'vehicles' => $vehicles,
            'client' => [
                'name' => $client->name,
                'contact_email' => $client->contact_email,
                'pickup_address' => $client->pickup_address,
                'pickup_phone' => $client->pickup_phone,
                'pickup_instructions' => $client->pickup_instructions,
            ],
            'client_subscription' => [
                'status' => $client->refreshSubscriptionStatus()->subscription_status,
                'due_at' => $client->subscription_due_at,
                'grace_ends_at' => $client->grace_ends_at,
            ],
        ]);
    }

    public function availability(Request $request, $id)
    {
        $validated = $request->validate([
            'month' => ['nullable', 'date_format:Y-m'],
            'from' => ['nullable', 'date_format:Y-m-d', 'required_with:to'],
            'to' => ['nullable', 'date_format:Y-m-d', 'required_with:from', 'after_or_equal:from'],
        ]);
        if (!empty($validated['from'])) {
            $start = Carbon::parse($validated['from'])->toDateString();
            $end = Carbon::parse($validated['to'])->toDateString();
            if (Carbon::parse($start)->diffInDays(Carbon::parse($end)) > 100) {
                return response()->json(['message' => 'Availability range is too large.'], 422);
            }
        } else {
            $month = Carbon::createFromFormat('Y-m', $validated['month'] ?? now()->format('Y-m'));
            $start = $month->copy()->startOfMonth()->toDateString();
            $end = $month->copy()->endOfMonth()->toDateString();
        }
        $vehicle = Vehicle::where('client_id', $request->user()->client_id)->findOrFail($id);
        $entries = app(VehicleAvailabilityCalendar::class)->entries($vehicle, $start, $end);

        return response()->json(['success' => true, 'vehicle_status' => $vehicle->status, 'entries' => $entries]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'contact' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:255',
        ]);

        $user = auth()->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not authenticated.',
            ], 401);
        }

        $user->update([
            'name' => $request->name,
            'contact' => $request->contact,
            'address' => $request->address,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully.',
            'user' => $user,
        ]);
    }
}
