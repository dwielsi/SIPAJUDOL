<?php

namespace App\Services\Scanner\Contracts;

use App\Models\Website;
use App\Services\Scanner\DTO\PageContent;

interface DetectorInterface
{
    /**
     * @return \App\Services\Scanner\DTO\Finding[]
     */
    public function detect(PageContent $page, Website $website): array;

    public function label(): string;
}
