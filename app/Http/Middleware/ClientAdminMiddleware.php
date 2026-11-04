<?php

namespace App\Http\Middleware;

use App\Models\Admin;
use Closure;
use Illuminate\Http\Request;

class ClientAdminMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        if (!($user instanceof Admin) || $user->role !== 'admin') {
            return response()->json(['message' => 'Rental administrator access required.'], 403);
        }

        return $next($request);
    }
}
