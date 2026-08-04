<?php

namespace App\Services\Scanner\Detectors;

use App\Models\Website;
use App\Services\Scanner\Contracts\DetectorInterface;
use App\Services\Scanner\DTO\Finding;
use App\Services\Scanner\DTO\PageContent;

class RedirectDetector implements DetectorInterface
{
    public function label(): string
    {
        return 'Redirect Mencurigakan';
    }

    public function detect(PageContent $page, Website $website): array
    {
        if (count($page->redirectChain) < 2) {
            return [];
        }

        $host = parse_url("https://{$website->domain}", PHP_URL_HOST) ?: $website->domain;
        $finalUrl = $page->redirectChain[array_key_last($page->redirectChain)];
        $finalHost = parse_url($finalUrl, PHP_URL_HOST);

        if ($finalHost && ! str_ends_with($finalHost, $host)) {
            return [new Finding(
                category: 'redirect',
                severity: 'high',
                message: "Halaman dialihkan (redirect) ke domain eksternal ({$finalHost})",
                evidence: implode(' -> ', $page->redirectChain),
                pageUrl: $page->url,
            )];
        }

        if (count($page->redirectChain) > 2) {
            return [new Finding(
                category: 'redirect',
                severity: 'low',
                message: 'Rantai pengalihan (redirect) yang cukup panjang terdeteksi',
                evidence: implode(' -> ', $page->redirectChain),
                pageUrl: $page->url,
            )];
        }

        return [];
    }
}
