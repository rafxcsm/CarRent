<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class SignupController extends Controller
{
    // Handle signup form submission
    public function store(Request $request)
    {
        // Validate signup data
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'contact' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'password' => 'required|min:6|confirmed',
        ]);

        // Create user
        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'contact' => $request->contact,
            'address' => $request->address,
            'password' => Hash::make($request->password),
        ]);

        // Redirect after signup
        return redirect()->route('signup.form')
            ->with('success', 'Account created successfully!');
    }
}
