<?php

namespace Tests\Feature\Scanner;

use App\Jobs\ScanWebsiteJob;
use App\Models\Keyword;
use App\Models\ScanResult;
use App\Models\Website;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class WebsiteScannerServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_scan_detects_threats_and_flags_website(): void
    {
        Keyword::create(['keyword' => 'slot gacor', 'category' => 'judol', 'active' => true]);

        Http::fake([
            'https://malicious.example.invalid' => Http::response(
                '<html><head><title>Beranda</title></head><body>'
                .'<p>Menang besar di slot gacor hari ini!</p>'
                .'<iframe src="https://evil-external.example.invalid/ads"></iframe>'
                .'<script>eval(atob("YWxlcnQoMSk="));</script>'
                .'</body></html>',
                200,
            ),
            '*' => Http::response('Not Found', 404),
        ]);

        $website = Website::factory()->create(['domain' => 'malicious.example.invalid']);

        $scanResult = ScanResult::create([
            'website_id' => $website->id,
            'scan_date' => now(),
            'scan_state' => 'queued',
        ]);

        ScanWebsiteJob::dispatch($website, $scanResult);

        $scanResult->refresh();
        $website->refresh();

        $this->assertSame('completed', $scanResult->scan_state);
        $this->assertGreaterThan(30, $scanResult->risk_score);
        $this->assertContains($scanResult->status, ['needs_review', 'flagged']);
        $this->assertSame($scanResult->status, $website->status);
        $this->assertGreaterThan(0, $scanResult->findings()->count());
        $this->assertNotNull($scanResult->ai_summary);
        $this->assertNotNull($scanResult->ai_recommendation);
        $this->assertSame(1, $scanResult->keyword_count);
    }

    public function test_scan_marks_clean_website_as_safe(): void
    {
        Http::fake([
            '*' => Http::response(
                '<html><head><title>Beranda Resmi</title></head><body><p>Selamat datang di website resmi pemerintah.</p></body></html>',
                200,
            ),
        ]);

        $website = Website::factory()->create(['domain' => 'clean.example.invalid']);

        $scanResult = ScanResult::create([
            'website_id' => $website->id,
            'scan_date' => now(),
            'scan_state' => 'queued',
        ]);

        ScanWebsiteJob::dispatch($website, $scanResult);

        $scanResult->refresh();
        $website->refresh();

        $this->assertSame('completed', $scanResult->scan_state);
        $this->assertSame('safe', $scanResult->status);
        $this->assertSame(0, $scanResult->risk_score);
        $this->assertSame('safe', $website->status);
    }

    public function test_scan_marks_as_failed_when_website_unreachable(): void
    {
        Http::fake([
            '*' => fn () => throw new \Illuminate\Http\Client\ConnectionException('Could not resolve host'),
        ]);

        $website = Website::factory()->create(['domain' => 'unreachable.example.invalid']);

        $scanResult = ScanResult::create([
            'website_id' => $website->id,
            'scan_date' => now(),
            'scan_state' => 'queued',
        ]);

        ScanWebsiteJob::dispatch($website, $scanResult);

        $scanResult->refresh();

        $this->assertSame('failed', $scanResult->scan_state);
    }
}
