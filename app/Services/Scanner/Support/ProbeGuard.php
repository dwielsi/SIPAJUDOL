<?php

namespace App\Services\Scanner\Support;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Throwable;

/**
 * Menyaring respons probe palsu: server yang mengalihkan (redirect) atau membalas
 * 200 untuk alamat yang tidak ada ("soft 404") tidak boleh dianggap file ditemukan.
 */
class ProbeGuard
{
    private ?string $baselineBody = null;

    private function __construct(private readonly int $timeout) {}

    public static function for(string $baseUrl, int $timeout): self
    {
        $guard = new self($timeout);
        $response = $guard->fetch(rtrim($baseUrl, '/').'/'.Str::lower(Str::random(16)).'.php');

        if ($response?->successful()) {
            $guard->baselineBody = $response->body();
        }

        return $guard;
    }

    public function fetch(string $url): ?Response
    {
        try {
            return Http::timeout($this->timeout)->withoutRedirecting()->get($url);
        } catch (Throwable) {
            return null;
        }
    }

    public function exists(?Response $response, string $homepageHtml): bool
    {
        if (! $response?->successful()) {
            return false;
        }

        $body = $response->body();
        $lower = strtolower($body);

        if (strlen($lower) < 20 || trim($body) === trim($homepageHtml)
            || str_contains($lower, 'page not found') || str_contains($lower, '404 not found')
            || str_contains($lower, 'halaman tidak ditemukan')) {
            return false;
        }

        return $this->baselineBody === null || ! $this->similar($body, $this->baselineBody);
    }

    private function similar(string $a, string $b): bool
    {
        $a = $this->normalize($a);
        $b = $this->normalize($b);

        if ($a === $b) {
            return true;
        }

        $longest = max(strlen($a), strlen($b));

        // Halaman soft-404 biasanya identik kecuali token dinamis; selisih panjang <5% dianggap sama.
        return $longest > 0 && abs(strlen($a) - strlen($b)) / $longest < 0.05;
    }

    private function normalize(string $html): string
    {
        return preg_replace(['/<meta[^>]+csrf[^>]*>/i', '/name="_token"[^>]*>/i', '/\s+/'], ['', '', ' '], $html) ?? $html;
    }
}
