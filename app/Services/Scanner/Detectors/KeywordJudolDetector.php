<?php

namespace App\Services\Scanner\Detectors;

use App\Models\Website;
use App\Services\Scanner\Contracts\DetectorInterface;
use App\Services\Scanner\DTO\Finding;
use App\Services\Scanner\DTO\PageContent;

class KeywordJudolDetector implements DetectorInterface
{
    /**
     * @param  string[]  $keywords
     */
    public function __construct(private readonly array $keywords) {}

    public function label(): string
    {
        return 'Keyword Judi Online';
    }

    public function detect(PageContent $page, Website $website): array
    {
        $findings = [];
        $text = strip_tags($page->html);

        foreach ($this->keywords as $keyword) {
            $pattern = '/\b'.preg_quote($keyword, '/').'\b/i';

            if (preg_match_all($pattern, $text, $matches)) {
                $count = count($matches[0]);

                $findings[] = new Finding(
                    category: 'keyword_judol',
                    severity: $count >= 3 ? 'high' : 'medium',
                    message: "Kata kunci judi online \"{$keyword}\" ditemukan {$count}x",
                    evidence: $keyword,
                    pageUrl: $page->url,
                );
            }
        }

        return $findings;
    }
}
