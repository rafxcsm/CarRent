<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Drivora subscription reminder</title>
</head>
<body style="margin:0;background:#f3f7fd;color:#152b45;font-family:Arial,sans-serif;">
    <div style="max-width:600px;margin:32px auto;padding:32px;background:#fff;border:1px solid #dce6f2;border-radius:16px;">
        <p style="margin:0 0 10px;color:#2563eb;font-size:12px;font-weight:bold;letter-spacing:2px;text-transform:uppercase;">Drivora subscription</p>
        <h1 style="margin:0 0 18px;font-size:24px;">{{ $reminderType === 'due_today' ? 'Payment is due today' : ($reminderType === 'grace_period' ? 'Please renew your subscription' : 'Your subscription is coming up for renewal') }}</h1>
        <p style="line-height:1.6;">Hello {{ $client->name }},</p>
        <p style="line-height:1.6;">This is a reminder about your Drivora monthly subscription.</p>
        <div style="margin:22px 0;padding:18px;background:#f3f7fd;border-radius:12px;line-height:1.8;">
            <strong>Plan:</strong> {{ $client->plan }}<br>
            <strong>Monthly amount:</strong> ₱{{ $monthlyFee }}<br>
            <strong>Due date:</strong> {{ $dueDate ?? 'Please contact Drivora support' }}
            @if ($reminderType === 'grace_period' && $graceEndDate)
                <br><strong>Grace period ends:</strong> {{ $graceEndDate }}
            @endif
        </div>
        <p style="line-height:1.6;">Please contact your Drivora platform administrator to confirm payment and avoid interruption to your rental workspace.</p>
        <p style="margin-top:28px;color:#52677f;font-size:13px;">This is an automated subscription notice for {{ $client->name }}.</p>
    </div>
</body>
</html>
