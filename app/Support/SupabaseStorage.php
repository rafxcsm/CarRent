<?php

namespace App\Support;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

class SupabaseStorage
{
    public function configured(): bool
    {
        return filled(config('services.supabase.url'))
            && filled(config('services.supabase.service_role_key'));
    }

    public function upload(UploadedFile $file, string $bucket, string $path): void
    {
        $this->request()
            ->withHeaders([
                'Content-Type' => $file->getMimeType() ?: 'application/octet-stream',
                'x-upsert' => 'true',
            ])
            ->withBody(file_get_contents($file->getRealPath()), $file->getMimeType() ?: 'application/octet-stream')
            ->post($this->objectUrl($bucket, $path))
            ->throw();
    }

    public function delete(string $bucket, ?string $path): void
    {
        if (!filled($path)) {
            return;
        }

        $this->request()
            ->delete($this->storageUrl('/object/'.rawurlencode($bucket)), [
                'prefixes' => [$path],
            ])
            ->throw();
    }

    public function publicUrl(string $bucket, string $path): string
    {
        $baseUrl = rtrim((string) config('services.supabase.url'), '/');

        return $baseUrl.'/storage/v1/object/public/'.rawurlencode($bucket).'/'.$this->encodePath($path);
    }

    public function signedUrl(string $bucket, string $path, int $expiresIn = 900): string
    {
        $response = $this->request()
            ->post($this->storageUrl('/object/sign/'.rawurlencode($bucket).'/'.$this->encodePath($path)), [
                'expiresIn' => $expiresIn,
            ])
            ->throw()
            ->json();

        $signedUrl = $response['signedURL'] ?? $response['signedUrl'] ?? null;
        if (!is_string($signedUrl) || $signedUrl === '') {
            throw new RuntimeException('Supabase did not return a signed file URL.');
        }

        if (Str::startsWith($signedUrl, ['http://', 'https://'])) {
            return $signedUrl;
        }

        $baseUrl = rtrim((string) config('services.supabase.url'), '/');
        if (Str::startsWith($signedUrl, '/storage/v1/')) {
            return $baseUrl.$signedUrl;
        }

        return $baseUrl.'/storage/v1'.(Str::startsWith($signedUrl, '/') ? '' : '/').$signedUrl;
    }

    public function privateObjectPath(string $path): string
    {
        // Older records contain paths from Laravel's public disk. Keep those
        // paths stable so they can be copied into the private bucket as-is.
        return ltrim(str_replace('\\', '/', $path), '/');
    }

    private function request(): PendingRequest
    {
        $key = (string) config('services.supabase.service_role_key');
        $headers = ['apikey' => $key];

        // Supabase's new secret keys are API keys, not JWTs. Legacy service_role
        // keys still need the Bearer header, so send it only for legacy keys.
        if (!Str::startsWith($key, 'sb_secret_')) {
            $headers['Authorization'] = 'Bearer '.$key;
        }

        return Http::acceptJson()
            ->timeout(30)
            ->withHeaders($headers);
    }

    private function objectUrl(string $bucket, string $path): string
    {
        return $this->storageUrl('/object/'.rawurlencode($bucket).'/'.$this->encodePath($path));
    }

    private function storageUrl(string $path): string
    {
        return rtrim((string) config('services.supabase.url'), '/').'/storage/v1'.$path;
    }

    private function encodePath(string $path): string
    {
        return implode('/', array_map('rawurlencode', explode('/', trim(str_replace('\\', '/', $path), '/'))));
    }
}
