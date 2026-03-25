<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Owner;
use App\Models\Rental;
use App\Models\Vehicle;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    // SHOW PROFILE PAGE
   public function index(Request $request)
    {
        $vehicle = null;

        if ($request->has('vehicle_id')) {
            $vehicle = Vehicle::find($request->vehicle_id);
        }

        return view('user.profile', compact('vehicle'));
    }

    // SAVE PROFILE + BOOKING
    public function storeAndBook(Request $request)
    {
        // Validate inputs including vehicle_id
        $request->validate([
            'first_name'     => 'required|string|max:255',
            'middle_name'    => 'nullable|string|max:255',
            'last_name'      => 'required|string|max:255',
            'gender'         => 'required|in:M,F',
            'birthdate'      => 'required|date',
            'address'        => 'required|string|max:500',
            'contact'        => 'required|string|max:20',
            'license_number' => 'nullable|string|max:50',
            'vehicle_id'     => 'required|exists:vehicles,id',
            'pickup_date'    => 'required|date',
            'pickup_time'    => 'required',
            'rental_type'    => 'required|string',
        ]);

        // Create owner
        $owner = Owner::create([
            'full_name'   => trim($request->first_name . ' ' . $request->middle_name . ' ' . $request->last_name),
            'gender'      => $request->gender,
            'birthdate'   => $request->birthdate,
            'address'     => $request->address,
            'contact_no'  => $request->contact,
            'license_no'  => $request->license_number,
            'status'      => 'Pending',
        ]);

        // Combine pickup date and time into datetime string for rental_start
        $rentalStart = $request->pickup_date . ' ' . $request->pickup_time . ':00';

        // Create rental
        Rental::create([
            'customer_id'  => $owner->id,
            'vehicle_id'   => $request->vehicle_id,
            'rental_type'  => $request->rental_type,
            'rental_start' => $rentalStart,
            'status'       => 'pending',
        ]);

        return back()->with('success', true);
    }
}
