<?php

namespace App\Http\Controllers;

use App\Models\Client;

class ClientDirectoryController extends Controller
{
    public function index()
    {
        $clients = Client::where('status', 'active')
            ->orderBy('name')
            ->get(['name', 'slug', 'pickup_address', 'pickup_phone']);

        return response()->json(['success' => true, 'clients' => $clients]);
    }
}
