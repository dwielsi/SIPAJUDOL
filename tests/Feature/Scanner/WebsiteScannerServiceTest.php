<?php

namespace Tests\Feature\Scanner;

use App\Jobs\ScanWebsiteJob;
use App\Models\ScanResult;
use App\Models\Website;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class WebsiteScannerServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_scan_detects_threats_and_flags_website(): void
    {
        config(['services.sumopod.api_key' => 'test-key']);

        Http::fake([
            'ai.sumopod.com/*' => function ($request) {
                $isScreening = str_contains($request['messages'][0]['content'], 'menyaring konten');

                $content = $isScreening
                    ? ['findings' => [[
                        'page' => 1,
                        'category' => 'judol',
                        'severity' => 'high',
                        'evidence' => 'Menang besar di slot gacor hari ini!',
                        'reason' => 'Promosi situs judi online disisipkan pada halaman.',
                    ]]]
                    : [
                        'summary' => 'Ringkasan dari LLM.',
                        'conclusion' => 'Kesimpulan dari LLM.',
                        'recommendation' => 'Rekomendasi dari LLM.',
                    ];

                return Http::response(['choices' => [['message' => ['content' => json_encode($content)]]]]);
            },
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
        $this->assertSame(1, $scanResult->findings()->where('category', 'illegal_content')->count());
        $this->assertSame('Ringkasan dari LLM.', $scanResult->ai_summary);
        $this->assertSame('Rekomendasi dari LLM.', $scanResult->ai_recommendation);

        Http::assertSent(fn ($request) => str_contains($request->url(), 'ai.sumopod.com')
            && $request['model'] === 'gpt-4o-mini'
            && $request->hasHeader('Authorization', 'Bearer test-key'));
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
            '*' => fn () => throw new ConnectionException('Could not resolve host'),
        ]);

        $website = Website::factory()->create(['domain' => 'unreachable.example.invalid']);

        $scanResult = ScanResult::create([
            'website_id' => $website->id,
            'scan_date' => now(),
            'scan_state' => 'queued',
        ]);

        ScanWebsiteJob::dispatch($website, $scanResult);

        $scanResult->refresh();
        $website->refresh();

        $this->assertSame('failed', $scanResult->scan_state);
        $this->assertSame('scan_failed', $website->status);
        $this->assertStringContainsString('DNS', $website->scan_failure_reason);
        $this->assertStringContainsString('DNS', $scanResult->failure_reason);
        $this->assertSame('Gagal Dipindai', $scanResult->riskLevelLabel());
    }

    public function test_scan_fails_with_reason_when_website_keeps_blocking_scanner(): void
    {
        Http::fake(['*' => Http::response('Forbidden', 403)]);

        $website = Website::factory()->create(['domain' => 'waf.example.invalid', 'status' => 'safe']);

        $scanResult = ScanResult::create([
            'website_id' => $website->id,
            'scan_date' => now(),
            'scan_state' => 'queued',
        ]);

        ScanWebsiteJob::dispatch($website, $scanResult);

        $scanResult->refresh();
        $website->refresh();

        $this->assertSame('failed', $scanResult->scan_state);
        $this->assertSame('scan_failed', $website->status);
        $this->assertStringContainsString('memblokir akses pemindai (HTTP 403)', $website->scan_failure_reason);
    }

    public function test_scan_recovers_with_ai_strategy_when_website_blocks_scanner(): void
    {
        config(['services.sumopod.api_key' => 'test-key']);

        Http::fake([
            'ai.sumopod.com/*' => function ($request) {
                $content = str_contains($request['messages'][0]['content'], 'teknisi jaringan')
                    ? ['diagnosis' => 'WAF memblokir user agent bot.', 'strategy' => ['scheme' => 'https', 'user_agent' => 'chrome', 'browser_headers' => true]]
                    : ['findings' => []];

                return Http::response(['choices' => [['message' => ['content' => json_encode($content)]]]]);
            },
            'blocked.example.invalid*' => fn ($request) => str_contains($request->header('User-Agent')[0] ?? '', 'Chrome')
                ? Http::response('<html><head><title>Beranda</title></head><body><p>Website resmi.</p></body></html>', 200)
                : Http::response('Forbidden', 403),
        ]);

        $website = Website::factory()->create(['domain' => 'blocked.example.invalid']);

        $scanResult = ScanResult::create([
            'website_id' => $website->id,
            'scan_date' => now(),
            'scan_state' => 'queued',
        ]);

        ScanWebsiteJob::dispatch($website, $scanResult);

        $scanResult->refresh();

        $this->assertSame('completed', $scanResult->scan_state);
        $this->assertSame('safe', $scanResult->status);

        Http::assertSent(fn ($request) => str_contains($request->url(), 'ai.sumopod.com')
            && str_contains($request['messages'][1]['content'], 'HTTP 403'));
    }
}
