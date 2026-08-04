<?php

namespace App\Services\Scanner\Detectors;

use App\Models\Website;
use App\Services\Scanner\Contracts\DetectorInterface;
use App\Services\Scanner\DTO\Finding;
use App\Services\Scanner\DTO\PageContent;
use App\Services\Scanner\Support\HtmlDom;
use Illuminate\Support\Str;

class JsObfuscationDetector implements DetectorInterface
{
    public function label(): string
    {
        return 'Obfuskasi eval()/base64';
    }

    public function detect(PageContent $page, Website $website): array
    {
        $findings = [];
        $dom = new HtmlDom($page->html);

        foreach ($dom->scripts() as $script) {
            $content = $script['inline'];

            if ($content === '' || ! preg_match('/eval\s*\(/i', $content)) {
                continue;
            }

            $hasBase64 = preg_match('/atob\s*\(|base64_decode|base64/i', $content);

            $findings[] = new Finding(
                category: 'eval_base64',
                severity: $hasBase64 ? 'high' : 'medium',
                message: $hasBase64
                    ? 'Skrip menggunakan eval() dengan payload base64/atob'
                    : 'Skrip menggunakan eval()',
                evidence: Str::limit($content, 200),
                pageUrl: $page->url,
                location: 'inline <script>',
            );
        }

        return $findings;
    }
}
