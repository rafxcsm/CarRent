<?php

namespace App\Console\Commands;

use App\Mail\SubscriptionReminderMail;
use App\Models\Client;
use App\Models\SubscriptionReminder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Throwable;

class SendSubscriptionReminders extends Command
{
    protected $signature = 'subscriptions:send-reminders';

    protected $description = 'Send scheduled subscription renewal reminders to client businesses';

    public function handle(): int
    {
        $timezone = config('rental.timezone', 'Asia/Manila');
        $today = now($timezone)->startOfDay();
        $sent = 0;
        $failed = 0;

        Client::query()
            ->where('status', 'active')
            ->whereNotNull('contact_email')
            ->whereNotNull('subscription_due_at')
            ->orderBy('id')
            ->chunkById(100, function ($clients) use ($today, &$sent, &$failed): void {
                foreach ($clients as $client) {
                    $client->refreshSubscriptionStatus();
                    $dueAtStored = $client->subscription_due_at;
                    if (!$dueAtStored) {
                        continue;
                    }
                    $dueAt = $dueAtStored->copy()->timezone($timezone);
                    $graceEndsAt = $client->grace_ends_at?->copy()->timezone($timezone);

                    $reminderType = null;
                    if ($client->subscription_status === 'active' && $dueAt->isSameDay($today->copy()->addDays(7))) {
                        $reminderType = 'before_due';
                    } elseif ($dueAt->isSameDay($today)) {
                        $reminderType = 'due_today';
                    } elseif (
                        $client->subscription_status === 'past_due'
                        && $graceEndsAt
                        && $graceEndsAt->isSameDay($today->copy()->addDays(3))
                    ) {
                        $reminderType = 'grace_period';
                    }

                    if (!$reminderType) {
                        continue;
                    }

                    $reminder = SubscriptionReminder::firstOrCreate(
                        [
                            'client_id' => $client->id,
                            'subscription_due_at' => $dueAtStored->format('Y-m-d H:i:s'),
                            'reminder_type' => $reminderType,
                        ],
                        ['sent_at' => null]
                    );

                    if ($reminder->sent_at) {
                        continue;
                    }

                    try {
                        Mail::to($client->contact_email)->send(new SubscriptionReminderMail($client, $reminderType));
                        $reminder->update(['sent_at' => now(), 'last_error' => null]);
                        $sent++;
                    } catch (Throwable $exception) {
                        $reminder->update(['last_error' => mb_substr($exception->getMessage(), 0, 5000)]);
                        report($exception);
                        $failed++;
                    }
                }
            });

        $this->info("Subscription reminders sent: {$sent}; failed: {$failed}.");
        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }
}
