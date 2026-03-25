<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Rental;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class RentalController extends Controller
{
    public function index()
    {
        $rentals = Rental::with(['customer', 'vehicle'])->latest()->get();
        return view('admin.rent', compact('rentals'));
    }

    public function userReceipts()
    {
        $rentals = Rental::with('vehicle')
            ->where('customer_id', auth()->id())
            ->latest()
            ->get();

        return view('user.receipts', compact('rentals'));
    }

    public function storeUserBooking(Request $request)
    {
        $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'date' => 'required|date',
            'time' => 'required',
            'proof_file' => 'required|file|mimes:jpg,jpeg,png,pdf,doc,docx|max:10240',
        ]);

        $fileName = null;

        if ($request->hasFile('proof_file')) {
            $file = $request->file('proof_file');
            $fileName = time() . '_' . preg_replace('/\s+/', '_', $file->getClientOriginalName());
            $file->move(public_path('uploads/proofs'), $fileName);
        }

        $rentalStart = Carbon::parse($request->date . ' ' . $request->time);

        Rental::create([
            'customer_id' => auth()->id(),
            'vehicle_id' => $request->vehicle_id,
            'rental_start' => $rentalStart,
            'proof_file' => $fileName,
            'status' => 'pending',
        ]);

        return redirect()->route('user.receipts')->with('success', 'Booking saved successfully.');
    }

    public function approve($id)
    {
        $rental = Rental::findOrFail($id);

        if ($rental->status === 'approved') {
            return response()->json([
                'success' => true,
                'message' => 'Rental already approved.',
                'status' => 'approved',
            ]);
        }

        $rental->status = 'approved';
        $rental->save();

        return response()->json([
            'success' => true,
            'message' => 'Rental approved successfully.',
            'status' => 'approved',
        ]);
    }

    public function deny($id)
    {
        $rental = Rental::findOrFail($id);

        $rental->status = 'denied';
        $rental->save();

        return response()->json([
            'success' => true,
            'message' => 'Rental denied successfully.',
            'status' => 'denied',
        ]);
    }
}