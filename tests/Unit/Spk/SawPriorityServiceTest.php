<?php

namespace Tests\Unit\Spk;

use App\Models\ScanResult;
use App\Models\Website;
use App\Services\Spk\SawPriorityService;
use PHPUnit\Framework\TestCase;

class SawPriorityServiceTest extends TestCase
{
    private function criteria(): array
    {
        return [
            'malware_count' => ['label' => 'Malware', 'weight' => 0.5, 'type' => 'benefit'],
            'judol_link_count' => ['label' => 'Link Judol', 'weight' => 0.5, 'type' => 'benefit'],
        ];
    }

    private function thresholds(): array
    {
        return ['tinggi' => 0.6, 'sedang' => 0.3];
    }

    private function websiteWithScan(int $id, array $scanAttributes): Website
    {
        $website = new Website(['website_name' => "Website {$id}", 'opd_name' => "OPD {$id}"]);
        $website->id = $id;
        $website->setRelation('scanResults', collect([new ScanResult($scanAttributes)]));

        return $website;
    }

    public function test_ranks_websites_by_saw_score_descending(): void
    {
        $service = new SawPriorityService($this->criteria(), $this->thresholds());

        // score A = (10/10)*0.5 + (0/10)*0.5  = 0.5
        $a = $this->websiteWithScan(1, ['malware_count' => 10, 'judol_link_count' => 0]);
        // score B = (2/10)*0.5  + (10/10)*0.5 = 0.6
        $b = $this->websiteWithScan(2, ['malware_count' => 2, 'judol_link_count' => 10]);
        // score C = 0
        $c = $this->websiteWithScan(3, ['malware_count' => 0, 'judol_link_count' => 0]);

        $ranked = $service->rank(collect([$a, $b, $c]));

        $this->assertSame([2, 1, 3], $ranked->pluck('website.id')->all());
        $this->assertSame(1, $ranked[0]->rank);
        $this->assertSame(2, $ranked[1]->rank);
        $this->assertSame(3, $ranked[2]->rank);
    }

    public function test_handles_all_zero_criterion_without_division_by_zero(): void
    {
        $service = new SawPriorityService($this->criteria(), $this->thresholds());

        $a = $this->websiteWithScan(1, ['malware_count' => 0, 'judol_link_count' => 5]);
        $b = $this->websiteWithScan(2, ['malware_count' => 0, 'judol_link_count' => 0]);

        $ranked = $service->rank(collect([$a, $b]));

        $this->assertSame(0.0, $ranked->firstWhere('website.id', 1)->breakdown['malware_count']['normalized']);
        $this->assertSame(0.0, $ranked->firstWhere('website.id', 2)->breakdown['malware_count']['normalized']);
    }

    public function test_excludes_unscanned_websites_from_ranking(): void
    {
        $service = new SawPriorityService($this->criteria(), $this->thresholds());

        $scanned = $this->websiteWithScan(1, ['malware_count' => 5, 'judol_link_count' => 5]);

        $unscanned = new Website(['website_name' => 'Website 2', 'opd_name' => 'OPD 2']);
        $unscanned->id = 2;
        $unscanned->setRelation('scanResults', collect());

        $ranked = $service->rank(collect([$scanned, $unscanned]));
        $unscannedList = $service->unscanned(collect([$scanned, $unscanned]));

        $this->assertCount(1, $ranked);
        $this->assertCount(1, $unscannedList);
        $this->assertSame(2, $unscannedList->first()->id);
    }

    public function test_priority_level_thresholds(): void
    {
        $service = new SawPriorityService($this->criteria(), $this->thresholds());

        // score = 1.0 -> tinggi (>= 0.6)
        $tinggi = $this->websiteWithScan(1, ['malware_count' => 10, 'judol_link_count' => 10]);
        // score = 0.5 -> sedang (>= 0.3 dan < 0.6)
        $sedang = $this->websiteWithScan(2, ['malware_count' => 10, 'judol_link_count' => 0]);
        // score = 0.0 -> rendah (< 0.3)
        $rendah = $this->websiteWithScan(3, ['malware_count' => 0, 'judol_link_count' => 0]);

        $ranked = $service->rank(collect([$tinggi, $sedang, $rendah]));

        $this->assertSame('tinggi', $ranked->firstWhere('website.id', 1)->priorityLevel);
        $this->assertSame('sedang', $ranked->firstWhere('website.id', 2)->priorityLevel);
        $this->assertSame('rendah', $ranked->firstWhere('website.id', 3)->priorityLevel);
    }

    public function test_returns_empty_collection_when_no_websites_scanned(): void
    {
        $service = new SawPriorityService($this->criteria(), $this->thresholds());

        $this->assertTrue($service->rank(collect())->isEmpty());
    }
}
