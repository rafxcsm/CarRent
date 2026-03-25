<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminAuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (!Auth::guard('admin')->attempt($credentials)) {
            return response()->json([
                'message' => 'Invalid email or password.'
            ], 422);
        }

        $request->session()->regenerate();

        return response()->json([
            'message' => 'Login successful',
            'redirect' => '/admin/dashboard'
        ]);
    }
}