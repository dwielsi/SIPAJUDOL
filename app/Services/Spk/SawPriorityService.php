<?php

namespace App\Services\Spk;

use App\Models\Website;
use App\Services\Spk\DTO\SpkRankedWebsite;
use Illuminate\Support\Collection;

class SawPriorityService
{
    /**
     * @param  array<string, array{label: string, weight: float, type: string}>|null  $criteria
     * @param  array<string, float>|null  $priorityThresholds
     */
    public function __construct(
        private readonly ?array $criteria = null,
        private readonly ?array $priorityThresholds = null,
    ) {}

    private function criteria(): array
    {
        return $this->criteria ?? config('spk.criteria');
    }

    private function priorityThresholds(): array
    {
        return $this->priorityThresholds ?? config('spk.priority_thresholds');
    }

    /**
     * Website yang punya minimal satu scan_result.
     *
     * @param  Collection<int, Website>  $websites
     * @return Collection<int, Website>
     */
    public function scanned(Collection $websites): Collection
    {
        return $websites->filter(fn (Website $website) => $website->scanResults->isNotEmpty())->values();
    }

    /**
     * Website yang belum pernah discan.
     *
     * @param  Collection<int, Website>  $websites
     * @return Collection<int, Website>
     */
    public function unscanned(Collection $websites): Collection
    {
        return $websites->filter(fn (Website $website) => $website->scanResults->isEmpty())->values();
    }

    /**
     * Hitung ranking prioritas dengan metode SAW berdasarkan scan terbaru tiap website.
     *
     * @param  Collection<int, Website>  $websites
     * @return Collection<int, SpkRankedWebsite>
     */
    public function rank(Collection $websites): Collection
    {
        $criteria = $this->criteria();
        $criteriaKeys = array_keys($criteria);

        $scanned = $this->scanned($websites);

        if ($scanned->isEmpty()) {
            return collect();
        }

        $rawMatrix = $scanned->mapWithKeys(function (Website $website) use ($criteriaKeys) {
            $scan = $website->scanResults->first();

            $values = collect($criteriaKeys)->mapWithKeys(
                fn (string $key) => [$key => (float) ($scan->{$key} ?? 0)]
            );

            return [$website->id => $values];
        });

        $max = collect($criteriaKeys)->mapWithKeys(
            fn (string $key) => [$key => (float) $rawMatrix->max(fn ($row) => $row[$key])]
        );

        $scored = $scanned->map(function (Website $website) use ($rawMatrix, $max, $criteria, $criteriaKeys) {
            $raw = $rawMatrix[$website->id];
            $breakdown = [];
            $score = 0.0;

            foreach ($criteriaKeys as $key) {
                $weight = (float) $criteria[$key]['weight'];
                $normalized = $max[$key] > 0.0 ? $raw[$key] / $max[$key] : 0.0;
                $contribution = $normalized * $weight;
                $score += $contribution;

                $breakdown[$key] = [
                    'label' => $criteria[$key]['label'],
                    'raw' => $raw[$key],
                    'normalized' => $normalized,
                    'weight' => $weight,
                    'contribution' => $contribution,
                ];
            }

            return ['website' => $website, 'score' => $score, 'breakdown' => $breakdown];
        })->sortByDesc('score')->values();

        return $scored->map(fn (array $row, int $index) => new SpkRankedWebsite(
            website: $row['website'],
            rank: $index + 1,
            score: $row['score'],
            priorityLevel: $this->priorityLevel($row['score']),
            breakdown: $row['breakdown'],
        ));
    }

    private function priorityLevel(float $score): string
    {
        $thresholds = $this->priorityThresholds();

        return match (true) {
            $score >= $thresholds['tinggi'] => 'tinggi',
            $score >= $thresholds['sedang'] => 'sedang',
            default => 'rendah',
        };
    }
}
