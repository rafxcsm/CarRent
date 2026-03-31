<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class OwnerController extends Controller
{
    // Get all customers / owners
    public function index(Request $request)
    {
        $search = $request->query('search', '');

        $owners = User::when($search, function($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('license', 'like', "%{$search}%")
                      ->orWhere('contact', 'like', "%{$search}%");
            })
            ->select('id', 'name as full_name', 'contact as contact_no', 'address', 'license as license_no', 'created_at')
            ->orderBy('id', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $owners
        ]);
    }

    // Delete an owner
    public function destroy($id)
    {
        $owner = User::find($id);
        if (!$owner) {
            return response()->json(['success' => false, 'message' => 'Owner not found'], 404);
        }

        $owner->delete();

        return response()->json(['success' => true, 'message' => 'Owner deleted successfully']);
    }
}