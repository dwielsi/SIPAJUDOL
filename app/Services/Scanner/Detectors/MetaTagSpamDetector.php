<?php

namespace App\Services\Scanner\Detectors;

use App\Models\Website;
use App\Services\Scanner\Contracts\DetectorInterface;
use App\Services\Scanner\DTO\Finding;
use App\Services\Scanner\DTO\PageContent;
use App\Services\Scanner\Support\HtmlDom;
use Illuminate\Support\Str;

class MetaTagSpamDetector implements DetectorInterface
{
    public function label(): string
    {
        return 'Meta Tag Spam';
    }

    public function detect(PageContent $page, Website $website): array
    {
        $findings = [];
        $dom = new HtmlDom($page->html);

        foreach ($dom->metaTags() as $meta) {
            $content = strtolower($meta['content']);

            if ($content === '') {
                continue;
            }

            if ($meta['name'] === 'keywords') {
                $keywordCount = count(array_filter(array_map('trim', explode(',', $content))));

                if ($keywordCount > 20) {
                    $findings[] = new Finding(
                        category: 'meta_tag_spam',
                        severity: 'medium',
                        message: "Meta keywords berisi {$keywordCount} kata kunci (indikasi keyword stuffing)",
                        evidence: Str::limit($meta['content'], 200),
                        pageUrl: $page->url,
                    );
                }
            }

            if (preg_match('/slot|gacor|casino|judi|maxwin|togel/i', $content)) {
                $findings[] = new Finding(
                    category: 'meta_tag_spam',
                    severity: 'high',
                    message: "Meta tag \"{$meta['name']}\" mengandung kata kunci judi online",
                    evidence: Str::limit($meta['content'], 200),
                    pageUrl: $page->url,
                );
            }
        }

        return $findings;
    }
}
