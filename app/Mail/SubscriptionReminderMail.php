<?php

namespace App\Mail;

use App\Models\Client;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SubscriptionReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Client $client,
        public string $reminderType,
    ) {}

    public function build(): self
    {
        $subjectLine = match ($this->reminderType) {
            'due_today' => 'Subscription payment is due today',
            'grace_period' => 'Subscription renewal reminder',
            default => 'Upcoming subscription payment',
        };

        return $this->subject($subjectLine . ' — ' . $this->client->name)
            ->view('emails.subscription-reminder')
            ->with([
                'client' => $this->client,
                'reminderType' => $this->reminderType,
                'dueDate' => $this->client->subscription_due_at?->timezone(config('rental.timezone'))->format('F j, Y'),
                'graceEndDate' => $this->client->grace_ends_at?->timezone(config('rental.timezone'))->format('F j, Y'),
                'monthlyFee' => number_format((float) $this->client->monthly_fee, 2),
            ]);
    }
}
