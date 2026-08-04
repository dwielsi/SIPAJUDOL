<?php

namespace App\Services\Scanner\Detectors;

use App\Models\Website;
use App\Services\Scanner\Contracts\DetectorInterface;
use App\Services\Scanner\DTO\Finding;
use App\Services\Scanner\DTO\PageContent;
use App\Services\Scanner\Support\HtmlDom;

class HiddenLinkDetector implements DetectorInterface
{
    public function label(): string
    {
        return 'Hidden Link';
    }

    public function detect(PageContent $page, Website $website): array
    {
        $findings = [];
        $dom = new HtmlDom($page->html);

        foreach ($dom->links() as $link) {
            if ($link['hidden'] && $link['href'] !== '') {
                $findings[] = new Finding(
                    category: 'hidden_link',
                    severity: 'high',
                    message: 'Tautan tersembunyi (hidden link) terdeteksi pada halaman',
                    evidence: $link['href'],
                    pageUrl: $page->url,
                );
            }
        }

        return $findings;
    }
}
