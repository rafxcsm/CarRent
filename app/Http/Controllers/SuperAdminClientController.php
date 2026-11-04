<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\Client;
use App\Models\Rental;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class SuperAdminClientController extends Controller
{
    public function index()
    {
        Client::query()->get()->each->refreshSubscriptionStatus();

        $clients = Client::query()
            ->withCount([
                'admins as admin_accounts_count' => fn ($query) => $query->where('role', 'admin'),
                'users',
                'vehicles',
                'rentals',
                'rentals as rentals_this_month_count' => fn ($query) => $query->where('created_at', '>=', now()->startOfMonth()),
            ])
            ->with('latestPayment')
            ->orderBy('name')
            ->get();

        $rentalStatuses = \App\Models\Rental::query()
            ->selectRaw('client_id, LOWER(status) as status, COUNT(*) as total')
            ->whereNotNull('client_id')
            ->groupBy('client_id', DB::raw('LOWER(status)'))
            ->get()
            ->groupBy('client_id');

        $clients->each(function (Client $client) use ($rentalStatuses) {
            $client->setAttribute(
                'rental_statuses',
                $rentalStatuses->get($client->id, collect())->mapWithKeys(fn ($row) => [$row->status => (int) $row->total])
            );
        });

        return response()->json(['success' => true, 'clients' => $clients]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:160'],
            'slug' => ['nullable', 'string', 'max:80', 'alpha_dash', Rule::unique('clients', 'slug')],
            'contact_email' => ['required', 'email', 'max:255'],
            'plan' => ['required', 'string', 'max:80'],
            'monthly_fee' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
            'admin_name' => ['required', 'string', 'max:160'],
            'admin_email' => ['required', 'email', 'max:255', Rule::unique('admins', 'email')],
            'admin_password' => ['required', 'string', 'min:12', 'confirmed'],
        ]);

        $slug = trim((string) ($validated['slug'] ?? '')) ?: Str::slug($validated['name']);
        if ($slug === '') {
            $slug = 'client-' . Str::lower(Str::random(8));
        }

        $baseSlug = $slug;
        $suffix = 2;
        while (Client::where('slug', $slug)->exists()) {
            if (!empty($validated['slug'])) {
                return response()->json(['message' => 'That client code is already in use.'], 422);
            }
            $slug = $baseSlug . '-' . $suffix++;
        }

        $client = DB::transaction(function () use ($validated, $slug) {
            $client = Client::create([
                'name' => $validated['name'],
                'slug' => $slug,
                'contact_email' => $validated['contact_email'] ?? null,
                'status' => 'active',
                'plan' => $validated['plan'],
                'monthly_fee' => $validated['monthly_fee'],
                'subscription_status' => 'active',
                'subscription_started_at' => now(),
                'subscription_due_at' => now()->addMonthNoOverflow(),
            ]);

            Admin::create([
                'name' => $validated['admin_name'],
                'email' => strtolower($validated['admin_email']),
                'password' => Hash::make($validated['admin_password']),
                'role' => 'admin',
                'client_id' => $client->id,
            ]);

            return $client;
        });

        return response()->json([
            'success' => true,
            'message' => 'Client and first rental admin created.',
            'client' => $client->loadCount(['users', 'vehicles', 'rentals']),
            'rental_admin' => ['name' => $validated['admin_name'], 'email' => strtolower($validated['admin_email'])],
        ], 201);
    }

    public function update(Request $request, Client $client)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:160'],
            'slug' => ['required', 'string', 'max:80', 'alpha_dash', Rule::unique('clients', 'slug')->ignore($client->id)],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'plan' => ['required', 'string', 'max:80'],
            'monthly_fee' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
        ]);

        $client->update($validated);

        return response()->json(['success' => true, 'message' => 'Client details updated.', 'client' => $client->fresh()]);
    }

    public function report(Client $client)
    {
        $client->refreshSubscriptionStatus();
        $statuses = $client->rentals()
            ->selectRaw('LOWER(status) as status, COUNT(*) as total')
            ->groupBy(DB::raw('LOWER(status)'))
            ->pluck('total', 'status');

        $transactions = Rental::where('client_id', $client->id)
            ->with(['user:id,name', 'vehicle:id,brand,model'])
            ->latest()
            ->limit(20)
            ->get()
            ->map(fn (Rental $rental) => [
                'id' => $rental->id,
                'customer' => $rental->user?->name ?? 'Customer',
                'vehicle' => trim(($rental->vehicle?->brand ?? '') . ' ' . ($rental->vehicle?->model ?? '')),
                'rental_start' => $rental->rental_start,
                'status' => $rental->status,
                'created_at' => $rental->created_at,
            ]);

        return response()->json([
            'success' => true,
            'client' => [
                'id' => $client->id,
                'name' => $client->name,
                'slug' => $client->slug,
                'subscription_status' => $client->subscription_status,
                'subscription_due_at' => $client->subscription_due_at,
                'counts' => [
                    'admins' => $client->admins()->where('role', 'admin')->count(),
                    'customers' => $client->users()->count(),
                    'vehicles' => $client->vehicles()->count(),
                    'rentals' => $client->rentals()->count(),
                    'rentals_this_month' => $client->rentals()->where('created_at', '>=', now()->startOfMonth())->count(),
                ],
                'rental_statuses' => $statuses,
            ],
            'transactions' => $transactions,
        ]);
    }

    public function updateSubscription(Request $request, Client $client)
    {
        $validated = $request->validate([
            'action' => ['required', Rule::in(['mark_paid', 'suspend'])],
            'reference' => ['nullable', 'string', 'max:120'],
        ]);

        if ($validated['action'] === 'suspend') {
            $client->update(['subscription_status' => 'suspended']);
            $message = 'Subscription suspended. Client accounts are read-only.';
        } else {
            DB::transaction(function () use ($client, $request, $validated) {
                $lockedClient = Client::whereKey($client->id)->lockForUpdate()->firstOrFail();
                $dueAt = $lockedClient->subscription_due_at;
                $periodStart = $dueAt && $dueAt->isFuture() ? $dueAt->copy() : now();
                $periodEnd = $periodStart->copy()->addMonthNoOverflow();

                $lockedClient->payments()->create([
                    'recorded_by_admin_id' => $request->user()->id,
                    'amount' => $lockedClient->monthly_fee,
                    'period_start' => $periodStart,
                    'period_end' => $periodEnd,
                    'payment_method' => 'manual',
                    'reference' => $validated['reference'] ?? null,
                ]);

                $lockedClient->update([
                    'subscription_status' => 'active',
                    'subscription_due_at' => $periodEnd,
                    'subscription_started_at' => $lockedClient->subscription_started_at ?: now(),
                    'grace_ends_at' => null,
                    'last_paid_at' => now(),
                ]);
            });
            $message = 'Payment recorded and one month of access added.';
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'client' => $client->fresh(),
        ]);
    }
}
