<?php

namespace Tests\Unit\Scanner;

use App\Services\Llm\SumopodClient;
use App\Services\Scanner\DTO\PageContent;
use App\Services\Scanner\LlmContentScreener;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class LlmContentScreenerTest extends TestCase
{
    private function page(string $url, string $html): PageContent
    {
        return new PageContent(url: $url, statusCode: 200, headers: [], html: $html, responseTimeMs: 100);
    }

    private function screener(?string $apiKey = 'test-key'): LlmContentScreener
    {
        return new LlmContentScreener(new SumopodClient([
            'base_url' => 'https://ai.sumopod.com/v1',
            'api_key' => $apiKey,
            'model' => 'gpt-4o-mini',
            'timeout' => 10,
        ]));
    }

    private function fakeLlm(array $findings): void
    {
        Http::fake([
            'ai.sumopod.com/*' => Http::response([
                'choices' => [['message' => ['content' => json_encode(['findings' => $findings])]]],
            ]),
        ]);
    }

    public function test_maps_llm_findings_to_pages(): void
    {
        $this->fakeLlm([
            ['page' => 2, 'category' => 'judol', 'severity' => 'high', 'evidence' => 'daftar slot gacor', 'reason' => 'Promosi judi online.'],
        ]);

        $pages = [
            $this->page('https://opd.go.id', '<p>Selamat datang</p>'),
            $this->page('https://opd.go.id/berita', '<div style="display:none">daftar slot gacor</div>'),
        ];

        $findings = $this->screener()->screen($pages);

        $this->assertArrayHasKey('https://opd.go.id/berita', $findings);
        $this->assertArrayNotHasKey('https://opd.go.id', $findings);
        $finding = $findings['https://opd.go.id/berita'][0];
        $this->assertSame('illegal_content', $finding->category);
        $this->assertSame('high', $finding->severity);
        $this->assertSame('Judi Online', $finding->location);

        Http::assertSent(fn ($request) => str_contains($request['messages'][1]['content'], 'daftar slot gacor')
            && ! str_contains($request['messages'][1]['content'], '<div'));
    }

    public function test_normalizes_invalid_category_and_severity(): void
    {
        $this->fakeLlm([
            ['page' => 1, 'category' => 'unknown', 'severity' => 'extreme', 'evidence' => 'teks aneh'],
            ['page' => 9, 'category' => 'judol', 'severity' => 'high', 'evidence' => 'halaman tidak ada'],
        ]);

        $findings = $this->screener()->screen([$this->page('https://opd.go.id', '<p>teks aneh</p>')]);

        $this->assertCount(1, $findings['https://opd.go.id']);
        $this->assertSame('medium', $findings['https://opd.go.id'][0]->severity);
        $this->assertSame('Konten Ilegal Lainnya', $findings['https://opd.go.id'][0]->location);
    }

    public function test_returns_nothing_when_llm_disabled_or_failing(): void
    {
        Http::fake(['*' => Http::response('error', 500)]);

        $pages = [$this->page('https://opd.go.id', '<p>slot gacor</p>')];

        $this->assertSame([], $this->screener(null)->screen($pages));
        Http::assertNothingSent();

        $this->assertSame([], $this->screener()->screen($pages));
    }
}
