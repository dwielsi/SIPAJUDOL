<?php

namespace App\Services\Scanner\Contracts;

use App\Models\Website;
use App\Services\Scanner\DTO\PageContent;

interface SiteProbeInterface
{
    /**
     * @return \App\Services\Scanner\DTO\Finding[]
     */
    public function probe(Website $website, PageContent $homepage): array;

    public function label(): string;
}
