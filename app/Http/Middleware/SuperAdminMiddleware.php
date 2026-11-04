<?php

namespace App\Http\Middleware;

use App\Models\Admin;
use Closure;
use Illuminate\Http\Request;

class SuperAdminMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $actor = $request->user();
        $token = $actor?->currentAccessToken();

        if (
            !($actor instanceof Admin)
            || $actor->role !== 'super_admin'
            || !$token
            || !method_exists($token, 'can')
            || !$token->can('super-admin')
        ) {
            return response()->json(['message' => 'Super admin access required.'], 403);
        }

        return $next($request);
    }
}
