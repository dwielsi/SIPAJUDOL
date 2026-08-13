<?php

namespace App\Services\Spk\DTO;

use App\Models\Website;

final class SpkRankedWebsite
{
    /**
     * @param  array<string, array{label: string, raw: float, max: float, normalized: float, weight: float, contribution: float}>  $breakdown
     */
    public function __construct(
        public readonly Website $website,
        public readonly int $rank,
        public readonly float $score,
        public readonly string $priorityLevel,
        public readonly array $breakdown,
    ) {}

    public function scorePercent(): string
    {
        return number_format($this->score * 100, 1);
    }
}
