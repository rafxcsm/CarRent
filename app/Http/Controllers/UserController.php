<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\Client;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    // ✅ LOGIN (API - returns token)
    public function login(Request $request)
    {
        $request->validate([
            'client_slug' => ['required', 'string', 'max:80'],
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $client = Client::where('slug', $request->client_slug)->where('status', 'active')->first();
        $user = $client
            ? User::where('client_id', $client->id)->where('email', $request->email)->first()
            : null;

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Invalid credentials'
            ], 401);
        }

        // 🔑 Create token
        $token = $user->createToken('user-token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token,
            'client' => [
                'name' => $client->name,
                'slug' => $client->slug,
                'subscription_status' => $client->refreshSubscriptionStatus()->subscription_status,
                'subscription_due_at' => $client->subscription_due_at,
                'grace_ends_at' => $client->grace_ends_at,
            ],
        ]);
    }

    // ✅ GET VEHICLES (Protected)
    public function vehicles()
    {
        $vehicles = Vehicle::where('client_id', auth()->user()->client_id)->get();

        return response()->json([
            'vehicles' => $vehicles
        ]);
    }

    // ✅ LOGOUT (optional but recommended)
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully'
        ]);
    }

    public function profile(Request $request)
{
    return response()->json([
        'success' => true,
        'user' => $request->user()
    ]);
}
}
