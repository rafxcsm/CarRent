<?php

namespace App\Http\Controllers;

use App\Models\Owner;
use Illuminate\Http\Request;

class OwnerController extends Controller
{
    // ADMIN - SHOW OWNERS LIST
    public function ownerList()
    {
        $owners = Owner::latest()->get();
        return view('admin.ownerslist', compact('owners'));
    }

    // ADMIN - DELETE OWNER (FIXED)
    public function destroy($id)
    {
        $owner = Owner::findOrFail($id);
        $owner->delete();

        return redirect('/ownerslist')
            ->with('success', 'Owner deleted successfully.');
    }
}
