<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Vehicle;

class VehicleController extends Controller
{
    public function index()
    {
        $vehicles = Vehicle::latest()->get();

        return response()->json([
            'success' => true,
            'vehicles' => $vehicles,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'reg_no' => 'required|unique:vehicles,reg_no',
            'brand'  => 'required',
            'color'  => 'required',
            'model'  => 'required',
            'rate'   => 'required|numeric',
            'status' => 'required',
            'image'  => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        if ($request->hasFile('image')) {
            $fileName = time() . '_' . $request->file('image')->getClientOriginalName();
            $request->file('image')->move(public_path('uploads/vehicles'), $fileName);
            $data['image'] = $fileName;
        }

        $vehicle = Vehicle::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Vehicle added successfully.',
            'vehicle' => $vehicle,
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $vehicle = Vehicle::findOrFail($id);

        $data = $request->validate([
            'reg_no' => 'required|unique:vehicles,reg_no,' . $vehicle->id,
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

            $fileName = time() . '_' . $request->file('image')->getClientOriginalName();
            $request->file('image')->move(public_path('uploads/vehicles'), $fileName);
            $data['image'] = $fileName;
        }

        $vehicle->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Vehicle updated successfully.',
            'vehicle' => $vehicle->fresh(),
        ]);
    }

    public function destroy($id)
    {
        $vehicle = Vehicle::findOrFail($id);

        if ($vehicle->image && file_exists(public_path('uploads/vehicles/' . $vehicle->image))) {
            unlink(public_path('uploads/vehicles/' . $vehicle->image));
        }

        $vehicle->delete();

        return response()->json([
            'success' => true,
            'message' => 'Vehicle deleted successfully.',
        ]);
    }
}