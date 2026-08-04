<?php

namespace Tests\Unit\Scanner;

use App\Services\Scanner\DTO\Finding;
use App\Services\Scanner\RiskScoringService;
use PHPUnit\Framework\TestCase;

class RiskScoringServiceTest extends TestCase
{
    private function weights(): array
    {
        return ['critical' => 25, 'high' => 15, 'medium' => 8, 'low' => 3, 'info' => 1];
    }

    public function test_no_findings_score_zero_and_safe(): void
    {
        $service = new RiskScoringService($this->weights());

        $this->assertSame(0, $service->score([]));
        $this->assertSame('safe', $service->status(0));
    }

    public function test_score_is_capped_at_100(): void
    {
        $service = new RiskScoringService($this->weights());
        $findings = array_fill(0, 10, new Finding('malware_signature', 'critical', 'x'));

        $this->assertSame(100, $service->score($findings));
    }

    public function test_status_bands_match_spec_thresholds(): void
    {
        $service = new RiskScoringService($this->weights());

        $this->assertSame('safe', $service->status(30));
        $this->assertSame('needs_review', $service->status(31));
        $this->assertSame('needs_review', $service->status(60));
        $this->assertSame('flagged', $service->status(61));
    }
}
