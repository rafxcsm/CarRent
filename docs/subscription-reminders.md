# Subscription reminder email setup

The app sends client subscription reminders seven days before the due date, on the due date, and three days before the grace period ends. It sends them to the client's **Subscription contact email** on the Admin → Business details page. A reminder is recorded per subscription period and type so scheduled runs do not resend successful emails.

## Configure a real mail sender

The sample environment uses Laravel's `log` mailer, which writes email content to the application log instead of delivering it. Configure an SMTP or transactional mail provider in the server's private environment settings. Set `MAIL_MAILER`, `MAIL_HOST`, `MAIL_PORT`, `MAIL_USERNAME`, `MAIL_PASSWORD`, `MAIL_SCHEME`, `MAIL_FROM_ADDRESS`, and `MAIL_FROM_NAME` to the values provided by that service. Keep credentials in `.env` or the host's secret settings, never in source control.

`RENTAL_TIMEZONE` defaults to `Asia/Manila`; the daily reminder task runs at 9:00 AM in that time zone.

## Run the schedule

For local development, run this in a separate terminal:

```powershell
php artisan schedule:work
```

On the production server, configure Laravel's scheduler to execute `php artisan schedule:run` every minute. The scheduled command is `subscriptions:send-reminders`; it can also be invoked manually after mail delivery is configured.

## Current billing behavior

These emails are reminders only. The platform does not charge clients automatically or include an online payment link. The super admin still records each subscription payment in the platform dashboard.
