<?php

namespace App\Services\Scanner;

use App\Services\Scanner\DTO\Finding;

class RiskScoringService
{
    /**
     * @param  array<string, int>  $weights
     */
    public function __construct(private readonly array $weights) {}

    /**
     * @param  Finding[]  $findings
     */
    public function score(array $findings): int
    {
        $total = 0;

        foreach ($findings as $finding) {
            $total += $this->weights[$finding->severity] ?? $this->weights['info'] ?? 1;
        }

        return min(100, $total);
    }

    public function status(int $score): string
    {
        return match (true) {
            $score >= 61 => 'flagged',
            $score >= 31 => 'needs_review',
            default => 'safe',
        };
    }
}
