<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AdminAuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $hardcodedEmail = 'rafakijay@gmail.com';
        $hardcodedPassword = '12345678';

        if (
            $request->email !== $hardcodedEmail ||
            $request->password !== $hardcodedPassword
        ) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid email or password.',
            ], 401);
        }

        return response()->json([
            'success' => true,
            'message' => 'Login successful.',
            'redirect' => '/admin/dashboard',
            'admin' => [
                'id' => 1,
                'name' => 'Admin',
                'email' => $hardcodedEmail,
                'role' => 'admin',
            ],
        ]);
    }
}