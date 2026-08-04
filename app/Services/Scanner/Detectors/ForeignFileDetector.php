<?php

namespace App\Services\Scanner\Detectors;

use App\Models\Website;
use App\Services\Scanner\Contracts\SiteProbeInterface;
use App\Services\Scanner\DTO\Finding;
use App\Services\Scanner\DTO\PageContent;
use Illuminate\Support\Facades\Http;
use Throwable;

class ForeignFileDetector implements SiteProbeInterface
{
    /**
     * @param  string[]  $directories
     * @param  string[]  $extensions
     */
    public function __construct(
        private readonly array $directories,
        private readonly array $extensions,
        private readonly int $timeout = 8,
    ) {}

    public function label(): string
    {
        return 'File Asing';
    }

    public function probe(Website $website, PageContent $homepage): array
    {
        $findings = [];
        $scheme = parse_url($homepage->url, PHP_URL_SCHEME) ?: 'https';
        $host = parse_url($homepage->url, PHP_URL_HOST) ?: $website->domain;

        foreach ($this->directories as $directory) {
            foreach ($this->extensions as $extension) {
                $path = trim($directory, '/')."/index.{$extension}";
                $url = "{$scheme}://{$host}/{$path}";

                try {
                    $response = Http::timeout($this->timeout)->get($url);
                } catch (Throwable) {
                    continue;
                }

                if ($response->successful() && strlen($response->body()) > 20 && trim($response->body()) !== trim($homepage->html)) {
                    $findings[] = new Finding(
                        category: 'foreign_file',
                        severity: 'high',
                        message: "File asing dengan ekstensi eksekusi ditemukan pada {$path}",
                        evidence: $url,
                        pageUrl: $url,
                        location: $path,
                    );
                }
            }
        }

        return $findings;
    }
}
