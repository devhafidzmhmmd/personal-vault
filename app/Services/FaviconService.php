<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class FaviconService
{
    private const GOOGLE_FAVICON_URL = 'https://www.google.com/s2/favicons';

    private const FAVICON_SIZE = 128;

    private const STORAGE_DIR = 'shortcuts/favicons';

    /**
     * Fetch favicon from URL and save to storage.
     * Uses Google's favicon API for reliable favicon retrieval.
     *
     * @return string|null Path relative to storage (e.g. shortcuts/favicons/abc123.png) or null on failure
     */
    public function fetchAndSave(string $url): ?string
    {
        $domain = $this->extractDomain($url);
        if (! $domain) {
            return null;
        }

        $faviconUrl = self::GOOGLE_FAVICON_URL.'?domain='.$domain.'&sz='.self::FAVICON_SIZE;

        $response = Http::timeout(5)->get($faviconUrl);

        if (! $response->successful() || $response->body() === '') {
            return null;
        }

        $extension = $this->guessExtension($response->header('Content-Type'));
        $filename = $this->generateFilename($domain, $extension);

        $path = self::STORAGE_DIR.'/'.$filename;

        $saved = Storage::disk('public')->put($path, $response->body());

        return $saved ? $path : null;
    }

    /**
     * Delete favicon from storage if it exists.
     */
    public function delete(string $path): bool
    {
        if (empty($path)) {
            return false;
        }

        return Storage::disk('public')->delete($path);
    }

    private function extractDomain(string $url): ?string
    {
        $parsed = parse_url($url);
        $host = $parsed['host'] ?? null;

        if (! $host) {
            return null;
        }

        return strtolower($host);
    }

    /**
     * @param  array<string, string>|string|null  $contentType
     */
    private function guessExtension($contentType): string
    {
        if (is_array($contentType)) {
            $contentType = $contentType[0] ?? '';
        }

        return match (true) {
            str_contains((string) $contentType, 'png') => 'png',
            str_contains((string) $contentType, 'jpeg') => 'jpg',
            str_contains((string) $contentType, 'gif') => 'gif',
            str_contains((string) $contentType, 'webp') => 'webp',
            str_contains((string) $contentType, 'svg') => 'svg',
            default => 'png',
        };
    }

    private function generateFilename(string $domain, string $extension): string
    {
        $safe = preg_replace('/[^a-z0-9.-]/', '-', $domain);

        return $safe.'-'.uniqid().'.'.$extension;
    }
}
