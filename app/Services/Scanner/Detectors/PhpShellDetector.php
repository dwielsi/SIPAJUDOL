<?php

namespace App\Services\Scanner\Detectors;

use App\Models\Website;
use App\Services\Scanner\Contracts\SiteProbeInterface;
use App\Services\Scanner\DTO\Finding;
use App\Services\Scanner\DTO\PageContent;
use Illuminate\Support\Facades\Http;
use Throwable;

class PhpShellDetector implements SiteProbeInterface
{
    private const SIGNATURES = [
        'c99shell', 'r57shell', 'wso shell', 'b374k', 'indoxploit', 'phpspy', 'FilesMan',
    ];

    /**
     * @param  string[]  $probePaths
     */
    public function __construct(
        private readonly array $probePaths,
        private readonly int $timeout = 8,
    ) {}

    public function label(): string
    {
        return 'PHP Shell';
    }

    public function probe(Website $website, PageContent $homepage): array
    {
        $findings = [];
        $scheme = parse_url($homepage->url, PHP_URL_SCHEME) ?: 'https';
        $host = parse_url($homepage->url, PHP_URL_HOST) ?: $website->domain;

        foreach ($this->probePaths as $path) {
            $url = "{$scheme}://{$host}/".ltrim($path, '/');

            try {
                $response = Http::timeout($this->timeout)->get($url);
            } catch (Throwable) {
                continue;
            }

            if (! $response->successful()) {
                continue;
            }

            $body = strtolower($response->body());

            foreach (self::SIGNATURES as $signature) {
                if (str_contains($body, strtolower($signature))) {
                    $findings[] = new Finding(
                        category: 'php_shell',
                        severity: 'critical',
                        message: "Tanda tangan PHP shell \"{$signature}\" ditemukan pada {$path}",
                        evidence: $url,
                        pageUrl: $url,
                        location: $path,
                    );

                    break;
                }
            }
        }

        return $findings;
    }
}
