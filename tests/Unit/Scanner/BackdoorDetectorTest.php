<?php

namespace Tests\Unit\Scanner;

use App\Models\Website;
use App\Services\Scanner\Detectors\BackdoorDetector;
use App\Services\Scanner\DTO\PageContent;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class BackdoorDetectorTest extends TestCase
{
    private function probe(): array
    {
        $homepage = new PageContent(
            url: 'https://opd.kuburayakab.go.id',
            statusCode: 200,
            headers: [],
            html: '<html><title>Beranda</title></html>',
            responseTimeMs: 100,
            redirectChain: ['https://opd.kuburayakab.go.id'],
        );

        return (new BackdoorDetector(['shell.php']))
            ->probe(new Website(['domain' => 'opd.kuburayakab.go.id']), $homepage);
    }

    public function test_ignores_server_that_redirects_unknown_paths(): void
    {
        Http::fake(['*' => Http::response('', 302, ['Location' => 'https://opd.kuburayakab.go.id/berita'])]);

        $this->assertEmpty($this->probe());
    }

    public function test_ignores_soft_404_pages(): void
    {
        Http::fake(['*' => Http::response('<html><title>Berita</title><meta name="csrf-token" content="'.uniqid().'">daftar berita terbaru</html>')]);

        $this->assertEmpty($this->probe());
    }

    public function test_flags_real_shell_that_differs_from_unknown_paths(): void
    {
        Http::fake([
            '*/shell.php' => Http::response('<html><title>WSO 2.5</title><form>uname -a; cmd</form></html>'),
            '*' => Http::response('', 404),
        ]);

        $findings = $this->probe();

        $this->assertCount(1, $findings);
        $this->assertSame('backdoor', $findings[0]->category);
    }
}
