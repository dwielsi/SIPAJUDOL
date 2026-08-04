<?php

namespace App\Services\Scanner\Detectors;

use App\Models\Website;
use App\Services\Scanner\Contracts\DetectorInterface;
use App\Services\Scanner\DTO\Finding;
use App\Services\Scanner\DTO\PageContent;
use App\Services\Scanner\Support\HtmlDom;
use Illuminate\Support\Str;

class JsInjectionDetector implements DetectorInterface
{
    public function label(): string
    {
        return 'JavaScript Injection';
    }

    public function detect(PageContent $page, Website $website): array
    {
        $findings = [];
        $dom = new HtmlDom($page->html);
        $host = parse_url("https://{$website->domain}", PHP_URL_HOST) ?: $website->domain;

        foreach ($dom->scripts() as $script) {
            $content = $script['inline'];

            if ($content === '') {
                continue;
            }

            if (preg_match('/document\.write\s*\(\s*unescape\s*\(/i', $content) || preg_match('/String\.fromCharCode\s*\(/i', $content)) {
                $findings[] = new Finding(
                    category: 'js_injection',
                    severity: 'high',
                    message: 'Pola injeksi JavaScript terobfuskasi terdeteksi (document.write/unescape/fromCharCode)',
                    evidence: Str::limit($content, 200),
                    pageUrl: $page->url,
                    location: 'inline <script>',
                );
            }

            if (preg_match('/window\.location(\.href)?\s*=\s*[\'"]([^\'"]+)[\'"]/i', $content, $matches)) {
                $targetHost = parse_url($matches[2], PHP_URL_HOST);

                if ($targetHost && ! str_ends_with($targetHost, $host)) {
                    $findings[] = new Finding(
                        category: 'js_injection',
                        severity: 'high',
                        message: "Skrip mengalihkan halaman secara paksa ke domain eksternal ({$targetHost})",
                        evidence: $matches[2],
                        pageUrl: $page->url,
                        location: 'inline <script>',
                    );
                }
            }
        }

        return $findings;
    }
}
