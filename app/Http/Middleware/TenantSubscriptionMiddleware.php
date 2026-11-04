<?php

namespace App\Http\Middleware;

use App\Models\Admin;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;

class TenantSubscriptionMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (str_ends_with($request->path(), '/logout')) {
            return $next($request);
        }

        $actor = $request->user();
        if (!($actor instanceof Admin) && !($actor instanceof User)) {
            return response()->json(['message' => 'A client account is required.'], 403);
        }

        $client = $actor->client;
        if (!$client) {
            return response()->json(['message' => 'This account is not linked to a client business.'], 403);
        }

        $client->refreshSubscriptionStatus();

        if ($client->status !== 'active') {
            return response()->json(['message' => 'This rental business account is inactive. Contact platform support.'], 423);
        }

        if ($client->isReadOnly() && !in_array($request->method(), ['GET', 'HEAD', 'OPTIONS'], true)) {
            return response()->json([
                'message' => 'This rental account is in read-only mode because its subscription is suspended. Contact the platform administrator to renew access.',
                'subscription_status' => $client->subscription_status,
            ], 423);
        }

        return $next($request);
    }
}
