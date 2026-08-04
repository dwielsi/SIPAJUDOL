<?php

namespace App\Services\Scanner\Detectors;

use App\Models\Website;
use App\Services\Scanner\Contracts\DetectorInterface;
use App\Services\Scanner\DTO\Finding;
use App\Services\Scanner\DTO\PageContent;

class SeoSpamDetector implements DetectorInterface
{
    private const SPAM_TERMS = [
        'klik disini', 'daftar sekarang', 'bonus 100%', 'wd tercepat',
        'gacor hari ini', 'anti rungkad', 'link alternatif', 'deposit pulsa',
    ];

    public function label(): string
    {
        return 'SEO Spam';
    }

    public function detect(PageContent $page, Website $website): array
    {
        $text = strtolower(strip_tags($page->html));

        $matchedTerms = array_values(array_filter(self::SPAM_TERMS, fn ($term) => str_contains($text, $term)));
        $hits = array_sum(array_map(fn ($term) => substr_count($text, $term), self::SPAM_TERMS));

        if (count($matchedTerms) === 0 || $hits < 3) {
            return [];
        }

        return [new Finding(
            category: 'seo_spam',
            severity: $hits >= 6 ? 'high' : 'medium',
            message: "Kepadatan kata kunci SEO spam tinggi terdeteksi ({$hits} kemunculan)",
            evidence: implode(', ', $matchedTerms),
            pageUrl: $page->url,
        )];
    }
}
