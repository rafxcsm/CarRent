<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class BusinessProfileController extends Controller
{
    public function show(Request $request)
    {
        return response()->json(['success' => true, 'business' => $this->publicDetails($request->user()->client)]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'contact_email' => ['required', 'email', 'max:255'],
            'pickup_address' => ['required', 'string', 'max:255'],
            'pickup_phone' => ['required', 'string', 'max:50'],
            'pickup_instructions' => ['nullable', 'string', 'max:2000'],
        ]);

        $client = $request->user()->client;
        $client->update([
            'contact_email' => strtolower($validated['contact_email']),
            'pickup_address' => trim($validated['pickup_address']),
            'pickup_phone' => trim($validated['pickup_phone']),
            'pickup_instructions' => isset($validated['pickup_instructions'])
                ? trim($validated['pickup_instructions']) ?: null
                : null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Business pickup and contact details saved.',
            'business' => $this->publicDetails($client->fresh()),
        ]);
    }

    private function publicDetails($client): array
    {
        return [
            'name' => $client->name,
            'contact_email' => $client->contact_email,
            'pickup_address' => $client->pickup_address,
            'pickup_phone' => $client->pickup_phone,
            'pickup_instructions' => $client->pickup_instructions,
        ];
    }
}
