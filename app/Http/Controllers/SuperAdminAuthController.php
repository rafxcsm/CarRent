<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class SuperAdminAuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $superAdmin = Admin::where('email', $credentials['email'])
            ->where('role', 'super_admin')
            ->where('is_active', true)
            ->first();

        if (!$superAdmin || !Hash::check($credentials['password'], $superAdmin->password)) {
            return response()->json(['message' => 'Invalid email or password.'], 401);
        }

        $token = $superAdmin->createToken('super-admin-token', ['super-admin'])->plainTextToken;

        return response()->json([
            'message' => 'Login successful.',
            'token' => $token,
            'super_admin' => [
                'id' => $superAdmin->id,
                'name' => $superAdmin->name,
                'email' => $superAdmin->email,
                'role' => $superAdmin->role,
            ],
        ]);
    }

    public function logout(Request $request)
    {
        $token = $request->user()?->currentAccessToken();
        if ($token && method_exists($token, 'delete')) {
            $token->delete();
        }

        return response()->json(['message' => 'Logged out successfully.']);
    }
}
