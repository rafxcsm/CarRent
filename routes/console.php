<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Models\Admin;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schedule;
use App\Support\SupabaseStorage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('super-admin:create', function () {
    $name = trim((string) $this->ask('Super admin name'));
    if ($name === '') {
        $this->error('A name is required.');
        return 1;
    }

    $email = strtolower(trim((string) $this->ask('Super admin email')));
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $this->error('Enter a valid email address.');
        return 1;
    }

    if (Admin::where('email', $email)->exists()) {
        $this->error('That email is already used by an admin account. Choose another email.');
        return 1;
    }

    $password = (string) $this->secret('Password (at least 12 characters)');
    $confirmation = (string) $this->secret('Confirm password');
    if (strlen($password) < 12 || !hash_equals($password, $confirmation)) {
        $this->error('The password must be at least 12 characters and both entries must match.');
        return 1;
    }

    Admin::create([
        'name' => $name,
        'email' => $email,
        'password' => Hash::make($password),
        'role' => 'super_admin',
    ]);

    $this->info('Super admin created. Sign in at /super-admin/login.');
    return 0;
})->purpose('Create a private platform super admin account');

Artisan::command('supabase:sync-legacy-uploads {--dry-run}', function () {
    $storage = app(SupabaseStorage::class);
    if (!$storage->configured()) {
        $this->error('Set SUPABASE_URL and SUPABASE_SERVICE_ROLE_KEY before syncing files.');
        return 1;
    }

    $sources = [
        [public_path('uploads/vehicles'), config('services.supabase.vehicle_bucket'), 'vehicles'],
        [public_path('uploads/proofs'), config('services.supabase.proof_bucket'), 'uploads/proofs'],
        [storage_path('app/public/rental_receipts'), config('services.supabase.proof_bucket'), 'rental_receipts'],
    ];
    $synced = 0;

    foreach ($sources as [$directory, $bucket, $prefix]) {
        if (!File::isDirectory($directory)) {
            continue;
        }

        foreach (File::allFiles($directory) as $file) {
            $relativePath = Str::replace('\\', '/', Str::after($file->getPathname(), $directory.DIRECTORY_SEPARATOR));
            $remotePath = $prefix.'/'.$relativePath;
            if ($this->option('dry-run')) {
                $this->line("Would copy {$file->getFilename()} to {$bucket}/{$remotePath}");
                $synced++;
                continue;
            }

            $mimeType = mime_content_type($file->getPathname()) ?: 'application/octet-stream';
            $upload = new UploadedFile($file->getPathname(), $file->getFilename(), $mimeType, null, true);
            $storage->upload($upload, $bucket, $remotePath);
            $this->line("Copied {$file->getFilename()} to {$bucket}/{$remotePath}");
            $synced++;
        }
    }

    $this->info($this->option('dry-run')
        ? "Dry run complete: {$synced} file(s) found."
        : "Upload complete: {$synced} file(s) copied. Local files were left in place.");
    return 0;
})->purpose('Copy legacy vehicle photos and rental proofs into Supabase Storage without deleting local files');

Schedule::command('subscriptions:send-reminders')
    ->dailyAt('09:00')
    ->timezone(config('rental.timezone', 'Asia/Manila'))
    ->withoutOverlapping();
