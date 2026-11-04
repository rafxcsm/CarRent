<?php

namespace App\Http\Controllers;

use App\Models\Rental;
use App\Models\Admin;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;
use App\Support\VehicleAvailabilityCalendar;
use App\Support\SupabaseStorage;
use Illuminate\Support\Str;
use Throwable;

class RentalController extends Controller
{
    /* ================= CREATE RENTAL ================= */
    public function store(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $validated = $request->validate([
            'vehicle_id' => ['required', 'integer', Rule::exists('vehicles', 'id')->where('client_id', $user->client_id)],
            'rental_start' => ['required', 'date', 'after_or_equal:today'],
            'rental_end' => ['required', 'date', 'after:rental_start'],
            'proof_file' => 'required|file|mimes:jpg,jpeg,png,pdf,doc,docx|max:10240',
            'discount_type' => 'nullable|in:senior,pwd',
        ]);

        $start = Carbon::parse($validated['rental_start']);
        $end = Carbon::parse($validated['rental_end']);
        $durationDays = (int) $start->copy()->startOfDay()->diffInDays($end->copy()->startOfDay());
        if ($durationDays < 1 || $durationDays > 30) {
            throw ValidationException::withMessages(['rental_end' => 'Choose a rental duration from 1 to 30 days.']);
        }

        $proofFile = $request->file('proof_file');
        $storage = app(SupabaseStorage::class);
        if ($storage->configured()) {
            $proofPath = 'proofs/'.$user->client_id.'/'.$user->id.'/'.Str::uuid().'.'.$proofFile->getClientOriginalExtension();
            $storage->upload($proofFile, config('services.supabase.proof_bucket'), $proofPath);
        } else {
            $proofPath = $proofFile->store('rental_receipts', 'public');
        }
        try {
            $rental = DB::transaction(function () use ($user, $validated, $start, $end, $durationDays, $proofPath) {
                $vehicle = Vehicle::where('client_id', $user->client_id)
                    ->whereKey($validated['vehicle_id'])
                    ->lockForUpdate()
                    ->firstOrFail();

                if (strcasecmp($vehicle->status, 'Available') !== 0) {
                    throw ValidationException::withMessages(['vehicle_id' => 'This vehicle is not available for booking.']);
                }

                $availability = app(VehicleAvailabilityCalendar::class);
                if ($availability->hasMaintenanceConflict($vehicle, $start, $end)) {
                    throw ValidationException::withMessages(['rental_start' => 'This vehicle has maintenance during the selected rental dates. Choose another date or duration.']);
                }
                if ($availability->hasBookingConflict($user->client_id, $vehicle->id, $start, $end)) {
                    throw ValidationException::withMessages(['rental_start' => 'This vehicle is already booked during part of the selected rental dates. Choose another date or duration.']);
                }

                $client = $user->client;
                $dailyRate = (float) $vehicle->rate;
                return Rental::create([
                    'client_id' => $user->client_id,
                    'user_id' => $user->id,
                    'vehicle_id' => $vehicle->id,
                    'rental_start' => $start,
                    'rental_end' => $end,
                    'daily_rate' => $dailyRate,
                    'total_amount' => round($dailyRate * $durationDays, 2),
                    'pickup_address' => $client?->pickup_address,
                    'pickup_phone' => $client?->pickup_phone,
                    'pickup_instructions' => $client?->pickup_instructions,
                    'proof_file' => $proofPath,
                    'status' => 'pending',
                    'discount_type' => $validated['discount_type'] ?? null,
                    'discount_status' => !empty($validated['discount_type']) ? 'pending' : 'none',
                    'discount_percent' => !empty($validated['discount_type'])
                        ? max(0, min(100, (float) config('rental.senior_pwd_discount_percent', 20)))
                        : 0,
                ]);
            });
        } catch (Throwable $exception) {
            if ($storage->configured()) {
                try {
                    $storage->delete(config('services.supabase.proof_bucket'), $proofPath);
                } catch (Throwable $cleanupException) {
                    report($cleanupException);
                }
            } else {
                Storage::disk('public')->delete($proofPath);
            }
            throw $exception;
        }

        return response()->json([
            'success' => true,
            'message' => 'Rental submitted successfully',
            'rental' => $rental
        ]);
    }

    /* ================= USER RENTALS ================= */
    public function userRentals(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 401);
        }

        $rentals = Rental::with('vehicle')
            ->where('client_id', $user->client_id)
            ->where('user_id', $user->id)
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'rentals' => $rentals->map(function ($rental) {

                $dateObj = $rental->rental_start
                    ? Carbon::parse($rental->rental_start)
                    : null;

                return [
                    'id' => $rental->id,

                    // vehicle info
                    'vehicle' => trim(
                        ($rental->vehicle->brand ?? '') . ' ' .
                        ($rental->vehicle->model ?? '')
                    ),

                    'rate' => (float) ($rental->daily_rate ?? $rental->vehicle->rate ?? 0),
                    'rental_end' => $rental->rental_end,
                    'days' => $this->rentalDays($rental),
                    'total_amount' => $this->rentalTotal($rental),
                    'final_amount' => $this->rentalFinalTotal($rental),
                    'pickup_address' => $rental->pickup_address,
                    'pickup_phone' => $rental->pickup_phone,
                    'pickup_instructions' => $rental->pickup_instructions,
                    'status' => $rental->status,
                    'discount_type' => $rental->discount_type,
                    'discount_status' => $rental->discount_status,
                    'discount_percent' => (float) $rental->discount_percent,
                    'discount_amount' => (float) $rental->discount_amount,
                    'estimated_discount_per_day' => $rental->discount_type
                        && $rental->discount_status === 'pending'
                            ? round(
                                (float) ($rental->daily_rate ?? $rental->vehicle->rate ?? 0)
                                    * ((float) $rental->discount_percent / 100),
                                2
                            )
                            : (float) $rental->discount_amount,
                    'qr_payload' => $rental->status === 'approved' && !$rental->returned_at
                        ? $this->makeQrPayload($rental)
                        : null,
                    'qr_verified_at' => $rental->qr_verified_at,
                    'returned_at' => $rental->returned_at,

                    // proof file
                    'proof_file' => $rental->proof_file
                        ? basename($rental->proof_file)
                        : null,

                    'proof_url' => $this->proofUrl($rental->proof_file),

                    // formatted date/time
                    'date' => $dateObj ? $dateObj->format('Y-m-d') : null,
                    'time' => $dateObj ? $dateObj->format('H:i') : null,

                    'rental_start' => $rental->rental_start,
                ];
            }),
        ]);
    }

    public function adminRentals(Request $request)
{
    $rentals = Rental::where('client_id', $request->user()->client_id)->with(['user', 'vehicle'])->latest()->get();

    return response()->json([
        'success' => true,
        'rentals' => $rentals->map(function ($rental) {
            return [
                'id' => $rental->id,

                'customer' => $rental->user->name ?? 'N/A',

                'vehicle' => trim(
                    ($rental->vehicle->brand ?? '') . ' ' .
                    ($rental->vehicle->model ?? '')
                ),

                'rental_start' => $rental->rental_start,
                'vehicle_id' => $rental->vehicle_id,
                'maintenance_conflict' => $rental->vehicle
                    ? app(VehicleAvailabilityCalendar::class)->hasMaintenanceConflict(
                        $rental->vehicle,
                        Carbon::parse($rental->rental_start),
                        Carbon::parse($rental->rental_end ?? Carbon::parse($rental->rental_start)->copy()->addDay())
                    )
                    : false,
                'status' => $rental->status,
                'rate' => (float) ($rental->daily_rate ?? $rental->vehicle->rate ?? 0),
                'rental_end' => $rental->rental_end,
                'days' => $this->rentalDays($rental),
                'total_amount' => $this->rentalTotal($rental),
                'pickup_address' => $rental->pickup_address,
                'pickup_phone' => $rental->pickup_phone,
                'pickup_instructions' => $rental->pickup_instructions,
                'discount_type' => $rental->discount_type,
                'discount_status' => $rental->discount_status,
                'discount_percent' => (float) $rental->discount_percent,
                'discount_amount' => (float) $rental->discount_amount,
                'qr_verified_at' => $rental->qr_verified_at,
                'returned_at' => $rental->returned_at,

                'proof_file' => $rental->proof_file
                    ? basename($rental->proof_file)
                    : null,

                'proof_url' => $this->proofUrl($rental->proof_file),
            ];
        }),
    ]);
}
    /* ================= APPROVE RENTAL ================= */
    public function approve(Request $request, $id)
    {
        $clientId = $request->user()->client_id;
        DB::transaction(function () use ($clientId, $id) {
            $rental = Rental::where('client_id', $clientId)->lockForUpdate()->findOrFail($id);
            if ($rental->status !== 'pending') {
                throw ValidationException::withMessages(['rental' => 'Only pending bookings can be approved.']);
            }

            $vehicle = Vehicle::where('client_id', $clientId)->whereKey($rental->vehicle_id)->lockForUpdate()->first();
            $start = Carbon::parse($rental->rental_start);
            $end = Carbon::parse($rental->rental_end ?? $start->copy()->addDay());
            $availability = app(VehicleAvailabilityCalendar::class);
            if (!$vehicle || strcasecmp($vehicle->status, 'Available') !== 0
                || $availability->hasMaintenanceConflict($vehicle, $start, $end)) {
                throw ValidationException::withMessages(['rental' => 'This booking conflicts with vehicle availability or maintenance. Resolve the conflict first.']);
            }
            if ($availability->hasBookingConflict($clientId, $vehicle->id, $start, $end, $rental->id)) {
                throw ValidationException::withMessages(['rental' => 'Another active booking overlaps these dates. Resolve the conflict first.']);
            }

            $rental->update(['status' => 'approved']);
            return $rental;
        });

        return response()->json([
            'success' => true,
            'message' => 'Rental approved'
        ]);
    }

    public function resolveMaintenance(Request $request, $id)
    {
        if (!($request->user() instanceof Admin)) {
            return response()->json(['message' => 'Admin access required.'], 403);
        }

        $validated = $request->validate([
            'action' => 'required|in:reschedule,reassign,cancel',
            'customer_acknowledged' => 'nullable|accepted',
            'customer_notified' => 'nullable|accepted',
            'rental_start' => 'required_if:action,reschedule|nullable|date|after_or_equal:today',
            'vehicle_id' => 'required_if:action,reassign|nullable|exists:vehicles,id',
        ]);
        if (in_array($validated['action'], ['reschedule', 'reassign'], true) && !$request->boolean('customer_acknowledged')) {
            throw ValidationException::withMessages(['customer_acknowledged' => 'Confirm the customer agreed to this change.']);
        }
        if ($validated['action'] === 'cancel' && !$request->boolean('customer_notified')) {
            throw ValidationException::withMessages(['customer_notified' => 'Confirm the customer has been notified before cancelling.']);
        }

        $clientId = $request->user()->client_id;
        $rental = DB::transaction(function () use ($validated, $id, $clientId) {
            $rental = Rental::where('client_id', $clientId)->with('vehicle')->lockForUpdate()->findOrFail($id);
            if (!in_array($rental->status, ['pending', 'approved'], true) || $rental->qr_verified_at || $rental->returned_at) {
                throw ValidationException::withMessages(['rental' => 'Only bookings awaiting pickup can be resolved this way.']);
            }
            $availability = app(VehicleAvailabilityCalendar::class);
            $oldStart = Carbon::parse($rental->rental_start);
            $oldEnd = Carbon::parse($rental->rental_end ?? $oldStart->copy()->addDay());
            if (!$rental->vehicle || !$availability->hasMaintenanceConflict($rental->vehicle, $oldStart, $oldEnd)) {
                throw ValidationException::withMessages(['rental' => 'This booking no longer conflicts with a maintenance date. Refresh the list.']);
            }

            if ($validated['action'] === 'cancel') {
                $rental->update(['status' => 'denied']);
                return $rental->fresh(['vehicle']);
            }

            if ($validated['action'] === 'reschedule') {
                $newDateTime = Carbon::parse($validated['rental_start']);
                $vehicleId = $rental->vehicle_id;
            } else {
                $newDateTime = Carbon::parse($rental->rental_start);
                $vehicleId = (int) $validated['vehicle_id'];
            }

            $target = Vehicle::where('client_id', $clientId)->whereKey($vehicleId)->lockForUpdate()->firstOrFail();
            $durationSeconds = max(1, (int) $oldStart->diffInSeconds($oldEnd));
            $newEnd = $newDateTime->copy()->addSeconds($durationSeconds);
            if (strcasecmp($target->status, 'Available') !== 0
                || $availability->hasMaintenanceConflict($target, $newDateTime, $newEnd)
                || $availability->hasBookingConflict($clientId, $target->id, $newDateTime, $newEnd, $rental->id)) {
                throw ValidationException::withMessages(['vehicle_id' => 'That vehicle is not available on the selected date.']);
            }

            $rental->update(['vehicle_id' => $target->id, 'rental_start' => $newDateTime, 'rental_end' => $newEnd]);
            return $rental->fresh(['vehicle']);
        });

        return response()->json(['success' => true, 'message' => 'Maintenance conflict resolved.', 'rental_id' => $rental->id]);
    }

    /* ================= DENY RENTAL ================= */
    public function deny(Request $request, $id)
    {
        $rental = Rental::where('client_id', $request->user()->client_id)->findOrFail($id);

        $rental->update([
            'status' => 'denied'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Rental denied'
        ]);
    }

    /* ================= ADMIN QR PICKUP VERIFICATION ================= */
    public function verifyQr(Request $request)
    {
        if (!($request->user() instanceof Admin)) {
            return response()->json(['message' => 'Admin access required.'], 403);
        }

        $validated = $request->validate([
            'qr_payload' => 'required|string|max:180',
            'action' => 'required|in:pickup,return',
        ]);

        $parts = explode(':', $validated['qr_payload']);
        if (count($parts) !== 3 || $parts[0] !== 'CARRENT-BOOKING' || !ctype_digit($parts[1])) {
            return response()->json(['message' => 'This QR code is not a valid CarRent booking code.'], 422);
        }

        $rental = Rental::where('client_id', $request->user()->client_id)
            ->with(['user', 'vehicle'])
            ->find((int) $parts[1]);
        if (!$rental) {
            return response()->json(['message' => 'Booking not found.'], 404);
        }

        $expectedSignature = hash_hmac(
            'sha256',
            $rental->id . '|' . $rental->user_id,
            (string) config('app.key')
        );

        if (!hash_equals($expectedSignature, $parts[2])) {
            return response()->json(['message' => 'This QR code is invalid.'], 422);
        }

        if ($rental->status !== 'approved') {
            return response()->json([
                'message' => 'Only approved bookings can be scanned for pickup or return.',
            ], 422);
        }

        if ($validated['action'] === 'pickup') {
            $alreadyVerified = (bool) $rental->qr_verified_at;
            if (!$alreadyVerified) {
                $rental->qr_verified_at = now();
                $rental->save();
            }
        } else {
            if (!$rental->qr_verified_at) {
                return response()->json([
                    'message' => 'Record pickup verification before recording the vehicle return.',
                ], 422);
            }

            $alreadyVerified = (bool) $rental->returned_at;
            if (!$alreadyVerified) {
                $rental->returned_at = now();
                $rental->save();
            }
        }

        return response()->json([
            'success' => true,
            'already_verified' => $alreadyVerified,
            'rental' => [
                'id' => $rental->id,
                'customer' => $rental->user->name ?? 'N/A',
                'vehicle' => trim(($rental->vehicle->brand ?? '') . ' ' . ($rental->vehicle->model ?? '')),
                'rental_start' => $rental->rental_start,
                'status' => $rental->status,
                'rate' => (float) ($rental->daily_rate ?? $rental->vehicle->rate ?? 0),
                'discount_type' => $rental->discount_type,
                'discount_status' => $rental->discount_status,
                'discount_percent' => (float) $rental->discount_percent,
                'discount_amount' => (float) $rental->discount_amount,
                'qr_verified_at' => $rental->qr_verified_at,
                'returned_at' => $rental->returned_at,
            ],
        ]);
    }

    /* ================= ADMIN DISCOUNT ID DECISION ================= */
    public function verifyDiscount(Request $request, $id)
    {
        if (!($request->user() instanceof Admin)) {
            return response()->json(['message' => 'Admin access required.'], 403);
        }

        $validated = $request->validate([
            'decision' => 'required|in:verified,rejected',
        ]);

        $rental = Rental::where('client_id', $request->user()->client_id)->with('vehicle')->findOrFail($id);

        if ($rental->status !== 'approved' || !$rental->qr_verified_at) {
            return response()->json([
                'message' => 'Scan the approved booking QR at pickup before deciding the discount.',
            ], 422);
        }

        if (!$rental->discount_type || $rental->discount_status !== 'pending') {
            return response()->json([
                'message' => 'This booking has no pending discount request.',
            ], 422);
        }

        if ($validated['decision'] === 'verified') {
            $percent = max(0, min(100, (float) config('rental.senior_pwd_discount_percent', 20)));
            $dailyRate = (float) ($rental->daily_rate ?? $rental->vehicle->rate ?? 0);

            $rental->discount_status = 'verified';
            $rental->discount_percent = $percent;
            $rental->discount_amount = round($dailyRate * ($percent / 100), 2);
        } else {
            $rental->discount_status = 'rejected';
            $rental->discount_percent = 0;
            $rental->discount_amount = 0;
        }

        $rental->save();

        return response()->json([
            'success' => true,
            'message' => $rental->discount_status === 'verified'
                ? 'Discount verified and applied to the daily rate.'
                : 'Discount request rejected.',
            'discount_status' => $rental->discount_status,
            'discount_percent' => (float) $rental->discount_percent,
            'discount_amount' => (float) $rental->discount_amount,
        ]);
    }

    private function rentalDays(Rental $rental): int
    {
        $start = Carbon::parse($rental->rental_start)->startOfDay();
        $end = Carbon::parse($rental->rental_end ?? Carbon::parse($rental->rental_start)->addDay())->startOfDay();
        return max(1, (int) $start->diffInDays($end));
    }

    private function rentalTotal(Rental $rental): float
    {
        $rate = (float) ($rental->daily_rate ?? $rental->vehicle?->rate ?? 0);
        return (float) ($rental->total_amount ?? round($rate * $this->rentalDays($rental), 2));
    }

    private function rentalFinalTotal(Rental $rental): float
    {
        if ($rental->discount_status !== 'verified') {
            return $this->rentalTotal($rental);
        }

        $rate = (float) ($rental->daily_rate ?? $rental->vehicle?->rate ?? 0);
        return round(max(0, $rate - (float) $rental->discount_amount) * $this->rentalDays($rental), 2);
    }

    private function proofUrl(?string $path): ?string
    {
        if (!filled($path)) {
            return null;
        }

        $storage = app(SupabaseStorage::class);
        if ($storage->configured()) {
            return $storage->signedUrl(
                config('services.supabase.proof_bucket'),
                $storage->privateObjectPath($path),
                900
            );
        }

        return asset('storage/'.$path);
    }

    private function makeQrPayload(Rental $rental): string
    {
        $signature = hash_hmac(
            'sha256',
            $rental->id . '|' . $rental->user_id,
            (string) config('app.key')
        );

        return 'CARRENT-BOOKING:' . $rental->id . ':' . $signature;
    }
}
