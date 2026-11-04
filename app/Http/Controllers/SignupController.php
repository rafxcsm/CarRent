<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class SignupController extends Controller
{
    public function store(Request $request)
    {
        $clientSelection = $request->validate([
            'client_slug' => ['required', 'string', 'max:80'],
        ]);
        $client = Client::where('slug', $clientSelection['client_slug'])
            ->where('status', 'active')
            ->first();

        if (!$client) {
            return response()->json(['message' => 'Choose a valid rental business.'], 422);
        }

        $client->refreshSubscriptionStatus();
        if ($client->isReadOnly()) {
            return response()->json(['message' => 'This rental business is currently in read-only mode and is not accepting new accounts.'], 423);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->where('client_id', $client->id)],
            'contact' => 'required|string|max:20',
            'license' => ['required', 'string', 'max:50', Rule::unique('users', 'license')->where('client_id', $client->id)],
            'address' => 'required|string|max:255',
            'password' => 'required|string|min:6|confirmed',
            'accepted_terms' => 'required|accepted',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'client_id' => $client->id,
            'email' => $validated['email'],
            'contact' => $validated['contact'],
            'license' => $validated['license'],
            'address' => $validated['address'],
            'password' => Hash::make($validated['password']),
            'terms_accepted_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Account created successfully.',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'client_slug' => $client->slug,
            ],
        ], 201);
    }

    public function index(Request $request)
{
    $search = $request->search;

    $clientId = $request->user()?->client_id;
    $users = User::query()->where('client_id', $clientId)
        ->when($search, function ($query) use ($search) {
            $query->where(function ($searchQuery) use ($search) {
                $searchQuery->where('name', 'like', "%{$search}%")
                    ->orWhere('contact', 'like', "%{$search}%")
                    ->orWhere('address', 'like', "%{$search}%")
                    ->orWhere('license', 'like', "%{$search}%");
            });
        })
        ->get(['id', 'name', 'contact', 'address', 'license']);

    return response()->json([
        'success' => true,
        'data' => $users,
    ]);
}

public function destroy(Request $request, $id)
{
    $user = User::where('client_id', $request->user()?->client_id)->findOrFail($id);
    $user->delete();

    return response()->json([
        'success' => true,
        'message' => 'User deleted successfully.',
    ]);
}
}
