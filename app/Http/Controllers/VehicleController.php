<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Vehicle;
use App\Models\Rental;
use App\Support\SupabaseStorage;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use App\Support\VehicleAvailabilityCalendar;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;

class VehicleController extends Controller
{
    public function index(Request $request)
    {
        $vehicles = Vehicle::where('client_id', $request->user()->client_id)->latest()->get();

        return response()->json([
            'success' => true,
            'vehicles' => $vehicles,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'reg_no' => ['required', Rule::unique('vehicles', 'reg_no')->where('client_id', $request->user()->client_id)],
            'brand'  => 'required',
            'color'  => 'required',
            'model'  => 'required',
            'rate'   => 'required|numeric',
            'status' => 'required|in:Available,Rented,Under Maintenance',
            'image'  => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $data['client_id'] = $request->user()->client_id;
        $storedImage = $this->storeVehicleImage($request, $data['client_id']);
        $data['image'] = $storedImage;

        try {
            $vehicle = Vehicle::create($data);
        } catch (\Throwable $exception) {
            $this->deleteVehicleImage($storedImage);
            throw $exception;
        }

        return response()->json([
            'success' => true,
            'message' => 'Vehicle added successfully.',
            'vehicle' => $vehicle,
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $vehicle = Vehicle::where('client_id', $request->user()->client_id)->findOrFail($id);

        $data = $request->validate([
            'reg_no' => ['required', Rule::unique('vehicles', 'reg_no')->where('client_id', $request->user()->client_id)->ignore($vehicle->id)],
            'brand'  => 'required',
            'color'  => 'required',
            'model'  => 'required',
            'rate'   => 'required|numeric',
            'status' => 'required|in:Available,Rented,Under Maintenance',
            'image'  => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $oldImage = $vehicle->getRawOriginal('image');
        $newImage = $request->hasFile('image')
            ? $this->storeVehicleImage($request, $request->user()->client_id)
            : null;
        if ($newImage) {
            $data['image'] = $newImage;
        }

        try {
            $vehicle->update($data);
        } catch (\Throwable $exception) {
            if ($newImage) {
                $this->deleteVehicleImage($newImage);
            }
            throw $exception;
        }

        if ($newImage) {
            $this->deleteVehicleImage($oldImage);
        }

        return response()->json([
            'success' => true,
            'message' => 'Vehicle updated successfully.',
            'vehicle' => $vehicle->fresh(),
        ]);
    }

    public function destroy(Request $request, $id)
    {
        $vehicle = Vehicle::where('client_id', $request->user()->client_id)->findOrFail($id);

        $image = $vehicle->getRawOriginal('image');
        $vehicle->delete();
        $this->deleteVehicleImage($image);

        return response()->json([
            'success' => true,
            'message' => 'Vehicle deleted successfully.',
        ]);
    }

    public function availability(Request $request, $id)
    {
        $validated = $request->validate([
            'month' => ['nullable', 'date_format:Y-m'],
            'from' => ['nullable', 'date_format:Y-m-d', 'required_with:to'],
            'to' => ['nullable', 'date_format:Y-m-d', 'required_with:from', 'after_or_equal:from'],
        ]);
        if (!empty($validated['from'])) {
            $start = Carbon::parse($validated['from'])->toDateString();
            $end = Carbon::parse($validated['to'])->toDateString();
            if (Carbon::parse($start)->diffInDays(Carbon::parse($end)) > 100) {
                return response()->json(['message' => 'Availability range is too large.'], 422);
            }
        } else {
            $month = Carbon::createFromFormat('Y-m', $validated['month'] ?? now()->format('Y-m'));
            $start = $month->copy()->startOfMonth()->toDateString();
            $end = $month->copy()->endOfMonth()->toDateString();
        }
        $vehicle = Vehicle::where('client_id', $request->user()->client_id)->findOrFail($id);
        $entries = app(VehicleAvailabilityCalendar::class)->entries($vehicle, $start, $end, true);

        return response()->json(['success' => true, 'vehicle_status' => $vehicle->status, 'entries' => $entries]);
    }

    private function storeVehicleImage(Request $request, int|string $clientId): string
    {
        $file = $request->file('image');
        $fileName = Str::uuid().'.'.$file->getClientOriginalExtension();
        $storage = app(SupabaseStorage::class);

        if ($storage->configured()) {
            $path = 'vehicles/'.$clientId.'/'.$fileName;
            $storage->upload($file, config('services.supabase.vehicle_bucket'), $path);

            return $path;
        }

        $directory = public_path('uploads/vehicles');
        File::ensureDirectoryExists($directory);
        $file->move($directory, $fileName);

        return $fileName;
    }

    private function deleteVehicleImage(?string $image): void
    {
        if (!filled($image)) {
            return;
        }

        $storage = app(SupabaseStorage::class);
        if ($storage->configured()) {
            $path = str_starts_with($image, 'vehicles/') ? $image : 'vehicles/'.basename($image);
            try {
                $storage->delete(config('services.supabase.vehicle_bucket'), $path);
            } catch (\Throwable $exception) {
                report($exception);
            }
        }

        $localPath = public_path('uploads/vehicles/'.basename($image));
        if (File::exists($localPath)) {
            File::delete($localPath);
        }
    }

    public function saveMaintenance(Request $request, $id)
    {
        $validated = $request->validate(['date' => 'required|date_format:Y-m-d|after_or_equal:today']);
        $vehicle = Vehicle::where('client_id', $request->user()->client_id)->findOrFail($id);
        $date = $validated['date'];

        $dateStart = Carbon::parse($date)->startOfDay();
        $dateEnd = $dateStart->copy()->addDay();
        if (Rental::where('client_id', $request->user()->client_id)->where('vehicle_id', $vehicle->id)
            ->where('rental_start', '<', $dateEnd)
            ->where('rental_end', '>', $dateStart->toDateTimeString())
            ->whereNotNull('qr_verified_at')->whereNull('returned_at')->exists()) {
            throw ValidationException::withMessages(['date' => 'This vehicle has already been picked up for that date and cannot be scheduled for maintenance.']);
        }

        $maintenance = $vehicle->maintenanceDates()->updateOrCreate(
            ['maintenance_date' => $date], ['note' => 'Under maintenance']
        );
        $conflicts = Rental::with('user')->where('client_id', $request->user()->client_id)->where('vehicle_id', $vehicle->id)
            ->where('rental_start', '<', $dateEnd)
            ->where('rental_end', '>', $dateStart->toDateTimeString())
            ->whereIn('status', ['pending', 'approved'])
            ->get(['id', 'user_id', 'rental_start', 'status']);

        return response()->json([
            'success' => true, 'maintenance' => $maintenance,
            'conflicts' => $conflicts->map(fn ($rental) => ['id' => $rental->id, 'customer' => $rental->user->name ?? 'Customer', 'status' => $rental->status]),
        ]);
    }

    public function deleteMaintenance(Request $request, $id)
    {
        $validated = $request->validate(['date' => 'required|date_format:Y-m-d']);
        $vehicle = Vehicle::where('client_id', $request->user()->client_id)->findOrFail($id);
        $deleted = $vehicle->maintenanceDates()->whereDate('maintenance_date', $validated['date'])->delete();
        return response()->json(['success' => true, 'deleted' => $deleted > 0]);
    }

    public function availableForDate(Request $request)
    {
        $validated = $request->validate([
            'date' => 'required|date_format:Y-m-d|after_or_equal:today',
            'end_date' => 'nullable|date_format:Y-m-d|after_or_equal:date',
            'exclude_vehicle_id' => 'nullable|integer',
        ]);
        $date = $validated['date'];
        $dateStart = Carbon::parse($date)->startOfDay();
        $dateEnd = Carbon::parse($validated['end_date'] ?? $date)->startOfDay()->addDay();
        $vehicles = Vehicle::query()->where('client_id', $request->user()->client_id)->where('status', 'Available')
            ->when($request->filled('exclude_vehicle_id'), fn ($query) => $query->where('id', '!=', $request->exclude_vehicle_id))
            ->whereDoesntHave('maintenanceDates', fn ($query) => $query->whereDate('maintenance_date', $date))
            ->whereDoesntHave('rentals', fn ($query) => $query
                ->where('client_id', $request->user()->client_id)
                ->where('rental_start', '<', $dateEnd)
                ->where('rental_end', '>', $dateStart->toDateTimeString())
                ->whereIn('status', ['pending', 'approved']))
            ->orderBy('brand')->orderBy('model')->get(['id', 'reg_no', 'brand', 'model', 'rate']);

        return response()->json(['success' => true, 'vehicles' => $vehicles]);
    }
}
