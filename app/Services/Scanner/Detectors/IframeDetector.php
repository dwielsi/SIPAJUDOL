<?php

namespace App\Services\Scanner\Detectors;

use App\Models\Website;
use App\Services\Scanner\Contracts\DetectorInterface;
use App\Services\Scanner\DTO\Finding;
use App\Services\Scanner\DTO\PageContent;
use App\Services\Scanner\Support\HtmlDom;

class IframeDetector implements DetectorInterface
{
    public function label(): string
    {
        return 'Iframe Mencurigakan';
    }

    public function detect(PageContent $page, Website $website): array
    {
        $findings = [];
        $dom = new HtmlDom($page->html);
        $host = parse_url("https://{$website->domain}", PHP_URL_HOST) ?: $website->domain;

        foreach ($dom->iframes() as $iframe) {
            $src = $iframe['src'];

            if ($src === '') {
                continue;
            }

            $iframeHost = parse_url($src, PHP_URL_HOST);
            $isExternal = $iframeHost && ! str_ends_with($iframeHost, $host);
            $isHidden = $iframe['hidden'];

            if (! $isExternal && ! $isHidden) {
                continue;
            }

            $findings[] = new Finding(
                category: 'iframe',
                severity: $isHidden && $isExternal ? 'critical' : 'high',
                message: $isHidden ? 'Iframe tersembunyi terdeteksi pada halaman' : 'Iframe menuju domain eksternal terdeteksi',
                evidence: $src,
                pageUrl: $page->url,
            );
        }

        return $findings;
    }
}
