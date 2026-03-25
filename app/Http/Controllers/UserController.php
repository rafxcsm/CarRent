<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class UserController extends Controller
{
    // Show login page
    public function showLoginForm()
{
    return Inertia::render('User/Login');
}

    // Handle login
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            return redirect()->intended(route('user.vehicles'));
        }

        return back()->withErrors([
            'email' => 'Invalid credentials.',
        ])->onlyInput('email');
    }

    // Show vehicles
    public function vehicles()
    {
        $vehicles = \App\Models\Vehicle::all();
        return view('user.vehicles', compact('vehicles'));
    }
}
