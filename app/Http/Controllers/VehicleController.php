<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Vehicle;
use Inertia\Inertia;

class VehicleController extends Controller
{
    public function index()
    {
        $vehicles = Vehicle::all();

        return Inertia::render('Admin/Vehicles', [
            'vehicles' => $vehicles,
        ]);
    }
    public function store(Request $request)
    {
        $data = $request->validate([
            'reg_no' => 'required|unique:vehicles',
            'brand'  => 'required',
            'color'  => 'required',
            'model'  => 'required',
            'rate'   => 'required|numeric',
            'status' => 'required',
            'image'  => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $fileName = time() . '_' . $request->image->getClientOriginalName();
        $request->image->move(public_path('uploads/vehicles'), $fileName);

        $data['image'] = $fileName;

        Vehicle::create($data);

        return redirect()->back()->with('success', 'Vehicle added!');
    }

    public function update(Request $request, $vehicle)
    {
        if (!$vehicle instanceof Vehicle) {
            $vehicle = Vehicle::findOrFail($vehicle);
        }

        $data = $request->validate([
            'reg_no' => 'required',
            'brand'  => 'required',
            'color'  => 'required',
            'model'  => 'required',
            'rate'   => 'required|numeric',
            'status' => 'required',
            'image'  => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        if ($request->hasFile('image')) {
            if ($vehicle->image && file_exists(public_path('uploads/vehicles/' . $vehicle->image))) {
                unlink(public_path('uploads/vehicles/' . $vehicle->image));
            }

            $fileName = time() . '_' . $request->image->getClientOriginalName();
            $request->image->move(public_path('uploads/vehicles'), $fileName);

            $data['image'] = $fileName;
        }

        $vehicle->update($data);

        return redirect()->back()->with('success', 'Vehicle updated!');
    }

    public function destroy($vehicle)
    {
        if (!$vehicle instanceof Vehicle) {
            $vehicle = Vehicle::findOrFail($vehicle);
        }

        if ($vehicle->image && file_exists(public_path('uploads/vehicles/' . $vehicle->image))) {
            unlink(public_path('uploads/vehicles/' . $vehicle->image));
        }

        $vehicle->delete();

        return redirect()->back()->with('success', 'Vehicle deleted!');
    }
}