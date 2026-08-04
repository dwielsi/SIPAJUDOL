<?php

namespace Tests\Unit\Scanner;

use App\Models\Website;
use App\Services\Scanner\Detectors\ExternalLinkDetector;
use App\Services\Scanner\Detectors\HiddenLinkDetector;
use App\Services\Scanner\Detectors\IframeDetector;
use App\Services\Scanner\Detectors\JsInjectionDetector;
use App\Services\Scanner\Detectors\JsObfuscationDetector;
use App\Services\Scanner\Detectors\KeywordJudolDetector;
use App\Services\Scanner\Detectors\MalwareSignatureDetector;
use App\Services\Scanner\Detectors\MetaTagSpamDetector;
use App\Services\Scanner\Detectors\RedirectDetector;
use App\Services\Scanner\Detectors\ScriptInjectionDetector;
use App\Services\Scanner\DTO\PageContent;
use PHPUnit\Framework\TestCase;

class DetectorsTest extends TestCase
{
    private function website(string $domain = 'opd.kuburayakab.go.id'): Website
    {
        return new Website(['domain' => $domain]);
    }

    private function page(string $html, string $url = 'https://opd.kuburayakab.go.id', array $redirectChain = []): PageContent
    {
        return new PageContent(
            url: $url,
            statusCode: 200,
            headers: [],
            html: $html,
            responseTimeMs: 100,
            redirectChain: $redirectChain ?: [$url],
        );
    }

    public function test_keyword_judol_detector_finds_configured_keywords(): void
    {
        $detector = new KeywordJudolDetector(['slot gacor', 'togel']);
        $page = $this->page('<p>Menang besar di slot gacor dan togel hari ini</p>');

        $findings = $detector->detect($page, $this->website());

        $this->assertCount(2, $findings);
        $this->assertSame('keyword_judol', $findings[0]->category);
    }

    public function test_keyword_judol_detector_ignores_clean_page(): void
    {
        $detector = new KeywordJudolDetector(['slot gacor']);
        $page = $this->page('<p>Selamat datang di website resmi pemerintah</p>');

        $this->assertEmpty($detector->detect($page, $this->website()));
    }

    public function test_iframe_detector_flags_external_iframe(): void
    {
        $detector = new IframeDetector();
        $page = $this->page('<iframe src="https://evil.example.com/ads"></iframe>');

        $findings = $detector->detect($page, $this->website());

        $this->assertCount(1, $findings);
        $this->assertSame('iframe', $findings[0]->category);
    }

    public function test_iframe_detector_ignores_same_domain_iframe(): void
    {
        $detector = new IframeDetector();
        $page = $this->page('<iframe src="https://opd.kuburayakab.go.id/embed"></iframe>');

        $this->assertEmpty($detector->detect($page, $this->website()));
    }

    public function test_js_obfuscation_detector_flags_eval_with_base64(): void
    {
        $detector = new JsObfuscationDetector();
        $page = $this->page('<script>eval(atob("YWxlcnQoMSk="));</script>');

        $findings = $detector->detect($page, $this->website());

        $this->assertCount(1, $findings);
        $this->assertSame('high', $findings[0]->severity);
    }

    public function test_malware_signature_detector_matches_known_signature(): void
    {
        $signatures = [
            ['pattern' => '/eval\s*\(\s*gzinflate\s*\(/i', 'message' => 'gzinflate payload'],
        ];
        $detector = new MalwareSignatureDetector($signatures);
        $page = $this->page('<?php eval(gzinflate(base64_decode("..."))); ?>');

        $findings = $detector->detect($page, $this->website());

        $this->assertCount(1, $findings);
        $this->assertSame('critical', $findings[0]->severity);
    }

    public function test_hidden_link_detector_flags_display_none_links(): void
    {
        $detector = new HiddenLinkDetector();
        $page = $this->page('<a href="https://judol.example.com" style="display:none">klik</a>');

        $findings = $detector->detect($page, $this->website());

        $this->assertCount(1, $findings);
        $this->assertSame('hidden_link', $findings[0]->category);
    }

    public function test_redirect_detector_flags_cross_domain_redirect(): void
    {
        $detector = new RedirectDetector();
        $page = $this->page(
            '<p>ok</p>',
            redirectChain: ['https://opd.kuburayakab.go.id', 'https://judol.example.com'],
        );

        $findings = $detector->detect($page, $this->website());

        $this->assertCount(1, $findings);
        $this->assertSame('redirect', $findings[0]->category);
    }

    public function test_redirect_detector_ignores_single_hop(): void
    {
        $detector = new RedirectDetector();
        $page = $this->page('<p>ok</p>', redirectChain: ['https://opd.kuburayakab.go.id']);

        $this->assertEmpty($detector->detect($page, $this->website()));
    }

    public function test_external_link_detector_flags_excessive_external_links(): void
    {
        $links = collect(range(1, 20))
            ->map(fn ($i) => "<a href=\"https://external{$i}.example.com\">link</a>")
            ->implode('');

        $detector = new ExternalLinkDetector(threshold: 15);
        $page = $this->page($links);

        $findings = $detector->detect($page, $this->website());

        $this->assertCount(1, $findings);
        $this->assertSame('external_link', $findings[0]->category);
    }

    public function test_meta_tag_spam_detector_flags_judol_keywords_in_meta(): void
    {
        $detector = new MetaTagSpamDetector();
        $page = $this->page('<meta name="keywords" content="slot gacor, casino, maxwin">');

        $findings = $detector->detect($page, $this->website());

        $this->assertNotEmpty($findings);
        $this->assertSame('meta_tag_spam', $findings[0]->category);
    }

    public function test_script_injection_detector_flags_suspicious_tld(): void
    {
        $detector = new ScriptInjectionDetector();
        $page = $this->page('<script src="https://cdn.malicious.xyz/inject.js"></script>');

        $findings = $detector->detect($page, $this->website());

        $this->assertCount(1, $findings);
        $this->assertSame('script_injection', $findings[0]->category);
    }

    public function test_js_injection_detector_flags_forced_external_redirect(): void
    {
        $detector = new JsInjectionDetector();
        $page = $this->page('<script>window.location.href = "https://judol.example.com";</script>');

        $findings = $detector->detect($page, $this->website());

        $this->assertCount(1, $findings);
        $this->assertSame('js_injection', $findings[0]->category);
    }
}
