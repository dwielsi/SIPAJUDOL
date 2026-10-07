<?php

namespace App\Services\Scanner\Detectors;

use App\Models\Website;
use App\Services\Scanner\Contracts\SiteProbeInterface;
use App\Services\Scanner\DTO\Finding;
use App\Services\Scanner\DTO\PageContent;
use App\Services\Scanner\Support\ProbeGuard;

class BackdoorDetector implements SiteProbeInterface
{
    /**
     * @param  string[]  $probePaths
     */
    public function __construct(
        private readonly array $probePaths,
        private readonly int $timeout = 8,
    ) {}

    public function label(): string
    {
        return 'Backdoor / Web Shell';
    }

    public function probe(Website $website, PageContent $homepage): array
    {
        $findings = [];
        $scheme = parse_url($homepage->url, PHP_URL_SCHEME) ?: 'https';
        $host = parse_url($homepage->url, PHP_URL_HOST) ?: $website->domain;

        $guard = ProbeGuard::for("{$scheme}://{$host}", $this->timeout);

        foreach ($this->probePaths as $path) {
            $url = "{$scheme}://{$host}/".ltrim($path, '/');

            if ($guard->exists($guard->fetch($url), $homepage->html)) {
                $findings[] = new Finding(
                    category: 'backdoor',
                    severity: 'critical',
                    message: "File backdoor/web shell yang dapat diakses ditemukan pada {$path}",
                    evidence: $url,
                    pageUrl: $url,
                    location: $path,
                );
            }
        }

        return $findings;
    }
}
