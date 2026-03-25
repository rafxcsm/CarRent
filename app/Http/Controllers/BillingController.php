<?php

namespace App\Http\Controllers;

use App\Models\Billing;
use Illuminate\Http\Request;

class BillingController extends Controller
{
    public function index()
    {
        $billings = Billing::orderBy('id', 'desc')->get();
        return view('admin.billings', compact('billings'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'customer' => 'required|string|max:255',
            'vehicle' => 'required|string|max:255',
            'total_amount' => 'required|numeric',
            'bill_date' => 'required|date',
        ]);

        Billing::create($request->all());
        return redirect()->route('billing.index')->with('success', 'Billing added successfully.');
    }

    public function destroy($billing)
{
    if (!$billing instanceof \App\Models\Billing) {
        $billing = \App\Models\Billing::findOrFail($billing);
    }

    $billing->delete();

    return redirect()->back()->with('success', 'Billing deleted!');
}

}