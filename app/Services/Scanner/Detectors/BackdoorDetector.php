<?php

namespace App\Services\Scanner\Detectors;

use App\Models\Website;
use App\Services\Scanner\Contracts\SiteProbeInterface;
use App\Services\Scanner\DTO\Finding;
use App\Services\Scanner\DTO\PageContent;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Throwable;

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

        foreach ($this->probePaths as $path) {
            $url = "{$scheme}://{$host}/".ltrim($path, '/');

            try {
                $response = Http::timeout($this->timeout)->get($url);
            } catch (Throwable) {
                continue;
            }

            if ($this->looksSuspicious($response, $homepage->html)) {
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

    private function looksSuspicious(Response $response, string $homepageHtml): bool
    {
        if (! $response->successful()) {
            return false;
        }

        $body = $response->body();

        if (trim($body) === trim($homepageHtml)) {
            return false;
        }

        $lower = strtolower($body);

        if (strlen($lower) < 20 || str_contains($lower, 'page not found') || str_contains($lower, '404 not found')) {
            return false;
        }

        return true;
    }
}
