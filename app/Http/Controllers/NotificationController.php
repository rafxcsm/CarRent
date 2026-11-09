<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\SubscriptionReminder;
use App\Notifications\InAppNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $actor = $request->user();

        if ($actor instanceof Admin) {
            $this->createSubscriptionDueNotice($actor);
        }

        $notifications = $actor->notifications()
            ->latest('created_at')
            ->limit(30)
            ->get()
            ->map(fn ($notification) => [
                'id' => $notification->id,
                'data' => $notification->data,
                'read_at' => $notification->read_at,
                'created_at' => $notification->created_at,
            ]);

        return response()->json([
            'success' => true,
            'notifications' => $notifications,
            'unread_count' => $actor->unreadNotifications()->count(),
        ]);
    }

    public function markRead(Request $request, string $notification)
    {
        $item = $request->user()->notifications()->whereKey($notification)->firstOrFail();
        $item->markAsRead();

        return response()->json(['success' => true]);
    }

    public function markAllRead(Request $request)
    {
        $request->user()->unreadNotifications()->update(['read_at' => now()]);

        return response()->json(['success' => true]);
    }

    private function createSubscriptionDueNotice(Admin $viewer): void
    {
        $client = $viewer->client;
        if (!$client || $client->status !== 'active' || !$client->subscription_due_at) {
            return;
        }

        $timezone = config('rental.timezone', 'Asia/Manila');
        $today = now($timezone)->startOfDay();
        $dueDate = $client->subscription_due_at->copy()->timezone($timezone)->startOfDay();

        if ($dueDate->lt($today) || $dueDate->gt($today->copy()->addDays(7))) {
            return;
        }

        $daysUntilDue = (int) $today->diffInDays($dueDate);
        $reminder = SubscriptionReminder::firstOrCreate(
            [
                'client_id' => $client->id,
                'subscription_due_at' => $client->subscription_due_at->format('Y-m-d H:i:s'),
                'reminder_type' => 'admin_due_'.$daysUntilDue.'d',
            ],
            ['sent_at' => null]
        );

        DB::transaction(function () use ($client, $daysUntilDue, $reminder, $dueDate): void {
            $lockedReminder = SubscriptionReminder::query()
                ->whereKey($reminder->getKey())
                ->lockForUpdate()
                ->first();

            if (!$lockedReminder || $lockedReminder->sent_at) {
                return;
            }

            $daysText = $daysUntilDue === 0
                ? 'today'
                : 'in '.$daysUntilDue.' '.($daysUntilDue === 1 ? 'day' : 'days');
            $dueLabel = $dueDate->format('F j, Y');

            Admin::query()
                ->where('client_id', $client->id)
                ->where('role', 'admin')
                ->where('is_active', true)
                ->get()
                ->each(fn (Admin $admin) => $admin->notify(new InAppNotification([
                    'type' => 'subscription_due',
                    'title' => 'Subscription due '.$daysText,
                    'message' => 'Your '.$client->plan.' subscription for '.$client->name.' is due '.$daysText.' ('.$dueLabel.').',
                    'url' => '/admin/business',
                ])));

            $lockedReminder->update(['sent_at' => now(), 'last_error' => null]);
        });
    }
}
