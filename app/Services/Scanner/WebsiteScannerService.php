<?php

namespace App\Services\Scanner;

use App\Models\Keyword;
use App\Models\ScanFinding;
use App\Models\ScanResult;
use App\Models\Website;
use App\Services\NotificationService;
use App\Services\Scanner\Detectors\BackdoorDetector;
use App\Services\Scanner\Detectors\DefacementDetector;
use App\Services\Scanner\Detectors\ExternalLinkDetector;
use App\Services\Scanner\Detectors\ForeignFileDetector;
use App\Services\Scanner\Detectors\HiddenLinkDetector;
use App\Services\Scanner\Detectors\IframeDetector;
use App\Services\Scanner\Detectors\JsInjectionDetector;
use App\Services\Scanner\Detectors\JsObfuscationDetector;
use App\Services\Scanner\Detectors\KeywordJudolDetector;
use App\Services\Scanner\Detectors\MalwareSignatureDetector;
use App\Services\Scanner\Detectors\MetaTagSpamDetector;
use App\Services\Scanner\Detectors\PhpShellDetector;
use App\Services\Scanner\Detectors\RedirectDetector;
use App\Services\Scanner\Detectors\ScriptInjectionDetector;
use App\Services\Scanner\Detectors\SeoSpamDetector;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

class WebsiteScannerService
{
    public function __construct(
        private readonly NotificationService $notifications,
        private readonly AiAnalysisService $ai,
        private readonly ScreenshotService $screenshots,
    ) {}

    public function run(Website $website, ScanResult $scanResult): ScanResult
    {
        $config = config('scanner');

        $scanResult->update([
            'scan_state' => 'running',
            'progress_percent' => 5,
            'current_step' => "Memulai pemindaian {$website->domain}",
            'started_at' => now(),
        ]);

        try {
            $crawler = new Crawler($config);

            $pages = $crawler->crawl($website->domain, function (int $current, int $total, string $url) use ($scanResult) {
                $percent = (int) min(70, 10 + ($current / max(1, $total)) * 60);
                $scanResult->update([
                    'progress_percent' => $percent,
                    'current_step' => "Memeriksa halaman: {$url}",
                ]);
            });

            if (empty($pages)) {
                throw new RuntimeException("Website {$website->domain} tidak dapat diakses.");
            }

            $homepage = $pages[0];

            $scanResult->update([
                'progress_percent' => 72,
                'current_step' => 'Mengambil tangkapan layar website',
            ]);

            $screenshotPath = $this->screenshots->capture($homepage->url, "{$website->domain}-{$scanResult->id}");

            $keywords = Keyword::query()->where('active', true)->pluck('keyword')->all();

            $pageDetectors = [
                new KeywordJudolDetector($keywords),
                new IframeDetector(),
                new JsObfuscationDetector(),
                new MalwareSignatureDetector($config['malware_signatures']),
                new RedirectDetector(),
                new SeoSpamDetector(),
                new MetaTagSpamDetector(),
                new HiddenLinkDetector(),
                new ExternalLinkDetector(),
                new ScriptInjectionDetector(),
                new JsInjectionDetector(),
            ];

            $siteProbes = [
                new BackdoorDetector($config['backdoor_probe_paths']),
                new PhpShellDetector($config['backdoor_probe_paths']),
                new ForeignFileDetector($config['foreign_file_directories'], $config['foreign_file_extensions']),
                new DefacementDetector(),
            ];

            $scanResult->update([
                'progress_percent' => 75,
                'current_step' => 'Menjalankan pemeriksaan mendalam (backdoor, shell, defacement)',
            ]);

            $findings = [];
            $infectedPages = 0;

            foreach ($pages as $page) {
                $pageFindings = [];

                foreach ($pageDetectors as $detector) {
                    $pageFindings = [...$pageFindings, ...$detector->detect($page, $website)];
                }

                if (! empty($pageFindings)) {
                    $infectedPages++;
                }

                $findings = [...$findings, ...$pageFindings];
            }

            foreach ($siteProbes as $probe) {
                $findings = [...$findings, ...$probe->probe($website, $homepage)];
            }

            $scanResult->update([
                'progress_percent' => 90,
                'current_step' => 'Menghitung skor risiko dan menyusun analisis',
            ]);

            foreach ($findings as $finding) {
                ScanFinding::create([
                    'scan_result_id' => $scanResult->id,
                    'category' => $finding->category,
                    'severity' => $finding->severity,
                    'message' => $finding->message,
                    'evidence' => $finding->evidence,
                    'page_url' => $finding->pageUrl,
                    'location' => $finding->location,
                ]);
            }

            $uniqueFindings = collect($findings)
                ->unique(fn ($finding) => "{$finding->category}|{$finding->severity}|{$finding->evidence}")
                ->all();

            $scoring = new RiskScoringService($config['severity_weights']);
            $riskScore = $scoring->score($uniqueFindings);
            $status = $scoring->status($riskScore);

            $countByCategory = fn (array $categories) => collect($findings)
                ->filter(fn ($f) => in_array($f->category, $categories, true))
                ->count();

            $keywordCount = $countByCategory(['keyword_judol', 'seo_spam', 'meta_tag_spam']);
            $judolLinkCount = $countByCategory(['hidden_link', 'iframe']);
            $redirectCount = $countByCategory(['redirect']);
            $malwareCount = $countByCategory([
                'malware_signature', 'backdoor', 'php_shell', 'foreign_file',
                'script_injection', 'js_injection', 'eval_base64', 'defacement',
            ]);
            $externalLinkCount = $countByCategory(['external_link']);

            $ai = $this->ai->analyze($findings, $riskScore, $status, $website->website_name);

            $topFinding = collect($findings)->sortByDesc(
                fn ($f) => $config['severity_weights'][$f->severity] ?? 0
            )->first();

            $sslHost = parse_url($homepage->url, PHP_URL_HOST) ?: $website->domain;
            $ssl = $this->checkSsl($sslHost);

            $scanResult->update([
                'status' => $status,
                'risk_score' => $riskScore,
                'threat_type' => $topFinding?->category,
                'keyword_count' => $keywordCount,
                'judol_link_count' => $judolLinkCount,
                'infected_pages' => $infectedPages,
                'redirect_count' => $redirectCount,
                'malware_count' => $malwareCount,
                'external_link_count' => $externalLinkCount,
                'screenshot_path' => $screenshotPath,
                'findings_summary' => $ai['summary'],
                'ai_summary' => $ai['summary'],
                'ai_conclusion' => $ai['conclusion'],
                'ai_recommendation' => $ai['recommendation'],
                'ai_priority' => $ai['priority'],
                'scan_state' => 'completed',
                'progress_percent' => 100,
                'current_step' => null,
                'completed_at' => now(),
            ]);

            $website->update([
                'status' => $status,
                'last_scanned_at' => now(),
                'response_time_ms' => $homepage->responseTimeMs,
                'latest_risk_score' => $riskScore,
                'baseline_hash' => DefacementDetector::computeHash($homepage),
                'ssl_valid' => $ssl['valid'],
                'ssl_expires_at' => $ssl['expires_at'],
            ]);

            $this->notifyResult($website, $status, $riskScore);

            return $scanResult->fresh();
        } catch (Throwable $e) {
            Log::error("Scan gagal untuk {$website->domain}: {$e->getMessage()}");

            $scanResult->update([
                'scan_state' => 'failed',
                'current_step' => $e->getMessage(),
                'completed_at' => now(),
            ]);

            $this->notifications->notify(
                'Scan Gagal',
                "Pemindaian {$website->website_name} ({$website->domain}) gagal: {$e->getMessage()}",
                'warning',
            );

            return $scanResult->fresh();
        }
    }

    private function notifyResult(Website $website, string $status, int $riskScore): void
    {
        $this->notifications->notify(
            'Scan Berhasil',
            "Pemindaian {$website->website_name} selesai dengan skor risiko {$riskScore}/100.",
            'success',
        );

        if ($status === 'flagged') {
            $this->notifications->notify(
                'Website Terindikasi',
                "{$website->website_name} ({$website->domain}) terindikasi ancaman dengan skor risiko {$riskScore}/100.",
                'threat',
            );
        } elseif ($status === 'needs_review') {
            $this->notifications->notify(
                'Website Perlu Pemeriksaan',
                "{$website->website_name} ({$website->domain}) memerlukan pemeriksaan lebih lanjut (skor risiko {$riskScore}/100).",
                'warning',
            );
        }
    }

    /**
     * @return array{valid: ?bool, expires_at: ?Carbon}
     */
    private function checkSsl(string $domain): array
    {
        $result = ['valid' => null, 'expires_at' => null];

        $context = stream_context_create([
            'ssl' => ['capture_peer_cert' => true, 'verify_peer' => false, 'verify_peer_name' => false],
        ]);

        try {
            $client = @stream_socket_client(
                "ssl://{$domain}:443",
                $errno,
                $errstr,
                5,
                STREAM_CLIENT_CONNECT,
                $context,
            );

            if (! $client) {
                return $result;
            }

            $params = stream_context_get_params($client);
            $cert = $params['options']['ssl']['peer_certificate'] ?? null;

            if ($cert) {
                $parsed = openssl_x509_parse($cert);
                $expiresAt = isset($parsed['validTo_time_t'])
                    ? Carbon::createFromTimestamp($parsed['validTo_time_t'])
                    : null;

                $result = [
                    'valid' => $expiresAt?->isFuture(),
                    'expires_at' => $expiresAt,
                ];
            }

            fclose($client);
        } catch (Throwable) {
            // Leave result as null/unknown when the SSL handshake fails.
        }

        return $result;
    }
}
