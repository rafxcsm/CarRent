<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Vehicle;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class UserVehicleController extends Controller
{
    public function index()
{
    $vehicles = Vehicle::all();

    return Inertia::render('User/Vehicles', [
        'vehicles' => $vehicles,
    ]);
}

    public function update(Request $request)
{
    $request->validate([
        'name' => 'required|string|max:255',
        'contact' => 'nullable|string|max:50',
        'address' => 'nullable|string|max:255',
    ]);

    auth()->user()->update([
        'name' => $request->name,
        'contact' => $request->contact,
        'address' => $request->address,
    ]);

    return back();
}

}
