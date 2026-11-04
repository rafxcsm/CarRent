<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AdminStaffController extends Controller
{
    public function index(Request $request)
    {
        $staff = Admin::query()
            ->where('client_id', $request->user()->client_id)
            ->where('role', 'staff')
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'is_active', 'created_at']);

        return response()->json(['success' => true, 'staff' => $staff]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:160'],
            'email' => ['required', 'email', 'max:255', Rule::unique('admins', 'email')],
            'password' => ['required', 'string', 'min:12', 'confirmed'],
        ]);

        $staff = Admin::create([
            'client_id' => $request->user()->client_id,
            'name' => trim($validated['name']),
            'email' => strtolower($validated['email']),
            'password' => Hash::make($validated['password']),
            'role' => 'staff',
            'is_active' => true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Staff account created. Share the login details with the staff member securely.',
            'staff' => $staff->only(['id', 'name', 'email', 'is_active', 'created_at']),
        ], 201);
    }

    public function update(Request $request, int $id)
    {
        $validated = $request->validate(['is_active' => ['required', 'boolean']]);
        $staff = Admin::query()
            ->where('client_id', $request->user()->client_id)
            ->where('role', 'staff')
            ->findOrFail($id);

        if (!$validated['is_active']) {
            $staff->tokens()->delete();
        }

        $staff->update(['is_active' => $validated['is_active']]);

        return response()->json([
            'success' => true,
            'message' => $staff->is_active ? 'Staff account enabled.' : 'Staff account disabled and signed out.',
            'staff' => $staff->only(['id', 'name', 'email', 'is_active', 'created_at']),
        ]);
    }
}
