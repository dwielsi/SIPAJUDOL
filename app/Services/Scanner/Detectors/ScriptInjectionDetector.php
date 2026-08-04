<?php

namespace App\Services\Scanner\Detectors;

use App\Models\Website;
use App\Services\Scanner\Contracts\DetectorInterface;
use App\Services\Scanner\DTO\Finding;
use App\Services\Scanner\DTO\PageContent;
use App\Services\Scanner\Support\HtmlDom;

class ScriptInjectionDetector implements DetectorInterface
{
    private const SUSPICIOUS_TLDS = ['xyz', 'top', 'club', 'icu', 'cn', 'ru', 'tk', 'buzz'];

    public function label(): string
    {
        return 'Script Injection';
    }

    public function detect(PageContent $page, Website $website): array
    {
        $findings = [];
        $dom = new HtmlDom($page->html);
        $host = parse_url("https://{$website->domain}", PHP_URL_HOST) ?: $website->domain;

        foreach ($dom->scripts() as $script) {
            $src = $script['src'];

            if ($src === '') {
                continue;
            }

            $srcHost = parse_url($src, PHP_URL_HOST);

            if (! $srcHost || str_ends_with($srcHost, $host)) {
                continue;
            }

            $hostTld = strtolower(substr($srcHost, strrpos($srcHost, '.') + 1));

            if (in_array($hostTld, self::SUSPICIOUS_TLDS, true)) {
                $findings[] = new Finding(
                    category: 'script_injection',
                    severity: 'high',
                    message: "Skrip eksternal dari domain mencurigakan ({$srcHost}) dimuat pada halaman",
                    evidence: $src,
                    pageUrl: $page->url,
                );
            }
        }

        return $findings;
    }
}
