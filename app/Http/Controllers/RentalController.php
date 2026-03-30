<?php

namespace App\Http\Controllers;

use App\Models\Rental;
use Illuminate\Http\Request;

class RentalController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:users,id',
            'vehicle_id' => 'required|exists:vehicles,id',
            'rental_start' => 'required|date',
            'proof_file' => 'required|file|mimes:jpg,jpeg,png,pdf,doc,docx',
        ]);

        $proofPath = null;

        if ($request->hasFile('proof_file')) {
            $proofPath = $request->file('proof_file')->store('rental_receipts', 'public');
        }

        Rental::create([
            'customer_id' => $request->customer_id,
            'vehicle_id' => $request->vehicle_id,
            'rental_start' => $request->rental_start,
            'proof_file' => $proofPath,
            'status' => 'pending',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Rental submitted successfully.',
        ]);
    }

    public function index()
    {
        $rentals = Rental::with(['customer', 'vehicle'])->latest()->get();

        $formatted = $rentals->map(function ($rental) {
            return [
                'id' => $rental->id,
                'customer' => $rental->customer->name ?? 'N/A',
                'vehicle' => trim(($rental->vehicle->brand ?? '') . ' ' . ($rental->vehicle->model ?? '')),
                'proof_file' => $rental->proof_file ? basename($rental->proof_file) : null,
                'proof_url' => $rental->proof_file ? asset('storage/' . $rental->proof_file) : null,
                'status' => $rental->status,
                'rental_start' => $rental->rental_start,
            ];
        });

        return response()->json([
            'success' => true,
            'rentals' => $formatted,
        ]);
    }

    public function approve($id)
    {
        $rental = Rental::findOrFail($id);
        $rental->status = 'approved';
        $rental->save();

        return response()->json([
            'success' => true,
            'message' => 'Rental approved.',
        ]);
    }

    public function deny($id)
    {
        $rental = Rental::findOrFail($id);
        $rental->status = 'denied';
        $rental->save();

        return response()->json([
            'success' => true,
            'message' => 'Rental denied.',
        ]);
    }
}