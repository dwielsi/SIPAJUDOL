<?php

namespace App\Services\Scanner\Detectors;

use App\Models\Website;
use App\Services\Scanner\Contracts\SiteProbeInterface;
use App\Services\Scanner\DTO\Finding;
use App\Services\Scanner\DTO\PageContent;
use App\Services\Scanner\Support\HtmlDom;

class DefacementDetector implements SiteProbeInterface
{
    private const MARKERS = [
        'hacked by', 'owned by', 'pwned by', 'defaced by', 'we are legion', 'your security is low',
    ];

    public function label(): string
    {
        return 'Defacement';
    }

    public function probe(Website $website, PageContent $homepage): array
    {
        $findings = [];
        $dom = new HtmlDom($homepage->html);
        $text = strtolower(strip_tags($homepage->html));

        foreach (self::MARKERS as $marker) {
            if (str_contains($text, $marker)) {
                $findings[] = new Finding(
                    category: 'defacement',
                    severity: 'critical',
                    message: "Indikasi defacement terdeteksi: frasa \"{$marker}\" ditemukan pada halaman utama",
                    evidence: $marker,
                    pageUrl: $homepage->url,
                );
            }
        }

        $currentHash = self::computeHash($homepage);

        if ($website->baseline_hash && $website->baseline_hash !== $currentHash) {
            $findings[] = new Finding(
                category: 'defacement',
                severity: 'low',
                message: 'Judul/konten awal halaman utama berubah dibandingkan pemindaian sebelumnya',
                evidence: 'Baseline berubah',
                pageUrl: $homepage->url,
            );
        }

        return $findings;
    }

    public static function computeHash(PageContent $homepage): string
    {
        $dom = new HtmlDom($homepage->html);
        $text = strtolower(strip_tags($homepage->html));
        $signature = strtolower(trim($dom->title() ?? '')).'|'.strtolower(trim(substr($text, 0, 300)));

        return hash('sha256', $signature);
    }
}
