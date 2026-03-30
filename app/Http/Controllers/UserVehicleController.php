<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Vehicle;

class UserVehicleController extends Controller
{
    public function index()
    {
        $vehicles = Vehicle::latest()->get();

        return response()->json([
            'success' => true,
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