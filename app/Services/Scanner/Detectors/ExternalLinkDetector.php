<?php

namespace App\Services\Scanner\Detectors;

use App\Models\Website;
use App\Services\Scanner\Contracts\DetectorInterface;
use App\Services\Scanner\DTO\Finding;
use App\Services\Scanner\DTO\PageContent;
use App\Services\Scanner\Support\HtmlDom;
use Illuminate\Support\Str;

class ExternalLinkDetector implements DetectorInterface
{
    public function __construct(private readonly int $threshold = 15) {}

    public function label(): string
    {
        return 'External Link';
    }

    public function detect(PageContent $page, Website $website): array
    {
        $dom = new HtmlDom($page->html);
        $host = parse_url("https://{$website->domain}", PHP_URL_HOST) ?: $website->domain;
        $externalHosts = [];

        foreach ($dom->links() as $link) {
            $href = $link['href'];

            if ($href === '' || Str::startsWith($href, ['#', 'mailto:', 'tel:', 'javascript:'])) {
                continue;
            }

            $linkHost = parse_url($href, PHP_URL_HOST);

            if ($linkHost && ! str_ends_with($linkHost, $host)) {
                $externalHosts[] = $linkHost;
            }
        }

        if (count($externalHosts) <= $this->threshold) {
            return [];
        }

        $unique = array_values(array_unique($externalHosts));

        return [new Finding(
            category: 'external_link',
            severity: 'medium',
            message: 'Jumlah tautan eksternal tidak wajar terdeteksi ('.count($externalHosts).' tautan, '.count($unique).' domain)',
            evidence: implode(', ', array_slice($unique, 0, 10)),
            pageUrl: $page->url,
        )];
    }
}
