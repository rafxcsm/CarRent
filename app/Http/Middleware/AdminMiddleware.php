<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Admin;
use Illuminate\Support\Facades\Auth;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if (!($user instanceof Admin) || !in_array($user->role, ['admin', 'staff'], true) || !$user->is_active) {
            return response()->json([
                'message' => 'Unauthorized admin access'
            ], 403);
        }

        return $next($request);
    }
}
