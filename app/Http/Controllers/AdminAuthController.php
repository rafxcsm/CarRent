<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminAuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $admin = Admin::where('email', $request->email)
            ->whereIn('role', ['admin', 'staff'])
            ->where('is_active', true)
            ->first();

        if (!$admin || !Hash::check($request->password, $admin->password)) {
            return response()->json([
                'message' => 'Invalid email or password.',
            ], 401);
        }

        // 🔑 CREATE TOKEN (IMPORTANT)
        $token = $admin->createToken($admin->role . '-token', ['admin'])->plainTextToken;
        return response()->json([
            'message' => 'Login successful.',
            'token' => $token,
            'admin' => [
                'id' => $admin->id,
                'name' => $admin->name,
                'email' => $admin->email,
                'role' => $admin->role ?? 'admin',
                'client_id' => $admin->client_id,
            ],
            'client' => $admin->client ? [
                'name' => $admin->client->name,
                'slug' => $admin->client->slug,
                'pickup_address' => $admin->client->pickup_address,
                'pickup_phone' => $admin->client->pickup_phone,
                'subscription_status' => $admin->client->refreshSubscriptionStatus()->subscription_status,
                'subscription_due_at' => $admin->client->subscription_due_at,
                'grace_ends_at' => $admin->client->grace_ends_at,
            ] : null,
        ]);
    }

    // OPTIONAL: logout
    public function logout(Request $request)
    {
        if (!($request->user() instanceof Admin)) {
            return response()->json(['message' => 'Admin access required.'], 403);
        }

        $token = $request->user()->currentAccessToken();
        if ($token && method_exists($token, 'delete')) {
            $token->delete();
        }

        return response()->json([
            'message' => 'Logged out successfully'
        ]);
    }
}
