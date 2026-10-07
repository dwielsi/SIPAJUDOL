<?php

namespace App\Services\Scanner;

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
use App\Services\Scanner\Detectors\MalwareSignatureDetector;
use App\Services\Scanner\Detectors\MetaTagSpamDetector;
use App\Services\Scanner\Detectors\PhpShellDetector;
use App\Services\Scanner\Detectors\RedirectDetector;
use App\Services\Scanner\Detectors\ScriptInjectionDetector;
use App\Services\Scanner\Detectors\SeoSpamDetector;
use App\Services\Scanner\DTO\PageContent;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class WebsiteScannerService
{
    public function __construct(
        private readonly NotificationService $notifications,
        private readonly AiAnalysisService $ai,
        private readonly ScreenshotService $screenshots,
        private readonly LlmContentScreener $contentScreener,
        private readonly ScanRecoveryAdvisor $recovery,
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
            $pages = $this->crawlWithRecovery($website, $scanResult, $config);

            $homepage = $pages[0];

            $scanResult->update([
                'progress_percent' => 72,
                'current_step' => 'Mengambil tangkapan layar website',
            ]);

            $screenshotPath = $this->screenshots->capture($homepage->url, "{$website->domain}-{$scanResult->id}");

            $llmFindingsByUrl = $this->contentScreener->screen($pages, function (int $batch, int $total) use ($scanResult) {
                $scanResult->update([
                    'progress_percent' => 73,
                    'current_step' => "Menyaring konten ilegal menggunakan AI (bagian {$batch}/{$total})",
                ]);
            });

            $pageDetectors = [
                new IframeDetector,
                new JsObfuscationDetector,
                new MalwareSignatureDetector($config['malware_signatures']),
                new RedirectDetector,
                new SeoSpamDetector,
                new MetaTagSpamDetector,
                new HiddenLinkDetector,
                new ExternalLinkDetector,
                new ScriptInjectionDetector,
                new JsInjectionDetector,
            ];

            $siteProbes = [
                new BackdoorDetector($config['backdoor_probe_paths']),
                new PhpShellDetector($config['backdoor_probe_paths']),
                new ForeignFileDetector($config['foreign_file_directories'], $config['foreign_file_extensions']),
                new DefacementDetector,
            ];

            $scanResult->update([
                'progress_percent' => 75,
                'current_step' => 'Menjalankan pemeriksaan mendalam (backdoor, shell, defacement)',
            ]);

            $findings = [];
            $infectedPages = 0;

            foreach ($pages as $page) {
                $pageFindings = $llmFindingsByUrl[$page->url] ?? [];

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

            $counts = ScanResult::countsFromFindings($findings);

            $ai = $this->ai->analyze($findings, $riskScore, $status, $website->displayName());

            $topFinding = collect($findings)->sortByDesc(
                fn ($f) => $config['severity_weights'][$f->severity] ?? 0
            )->first();

            $sslHost = parse_url($homepage->url, PHP_URL_HOST) ?: $website->domain;
            $ssl = $this->checkSsl($sslHost);

            $scanResult->update([
                'status' => $status,
                'risk_score' => $riskScore,
                'threat_type' => $topFinding?->category,
                ...$counts,
                'infected_pages' => $infectedPages,
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
                'scan_failure_reason' => null,
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

            $scanResult->markFailed($e->getMessage());
            $website->markScanFailed($e->getMessage());

            $this->notifications->notify(
                'Scan Gagal',
                "Pemindaian {$website->website_name} ({$website->domain}) gagal: {$e->getMessage()}",
                'warning',
            );

            return $scanResult->fresh();
        }
    }

    /** Status HTTP halaman utama yang menandakan akses diblokir/bermasalah sehingga perlu dicoba strategi lain. */
    private const BLOCKED_STATUSES = [401, 403, 406, 429];

    /**
     * Crawl website; bila gagal diakses, minta AI mendiagnosis penyebabnya dan mencoba strategi
     * akses lain berulang kali sampai berhasil (dibatasi jumlah percobaan dan waktu).
     *
     * @return PageContent[]
     */
    private function crawlWithRecovery(Website $website, ScanResult $scanResult, array $config): array
    {
        $maxAttempts = (int) ($config['recovery']['max_attempts'] ?? 8);
        $timeBudget = (int) ($config['recovery']['time_budget'] ?? 300);
        $startedAt = microtime(true);
        $history = [];
        $strategy = [];

        $onPage = function (int $current, int $total, string $url) use ($scanResult) {
            $scanResult->update([
                'progress_percent' => (int) min(70, 10 + ($current / max(1, $total)) * 60),
                'current_step' => Str::limit("Memeriksa halaman: {$url}", 250),
            ]);
        };

        while (true) {
            $crawler = new Crawler($config, $strategy);
            $pages = $crawler->crawl($website->domain, $onPage);
            $status = $pages[0]->statusCode ?? null;

            $problem = match (true) {
                $pages === [] => $crawler->lastError() ?? 'Website tidak merespons',
                in_array($status, self::BLOCKED_STATUSES, true) || $status >= 500 => "Halaman utama merespons HTTP {$status}",
                default => null,
            };

            if ($problem === null) {
                if ($history !== []) {
                    Log::info("Scan {$website->domain} berhasil setelah ".count($history).' percobaan pemulihan dengan strategi: '.$this->recovery->describe($strategy));
                }

                return $pages;
            }

            $history[] = ['strategy' => $strategy, 'error' => $problem];
            $attempt = count($history);

            if ($attempt > $maxAttempts || microtime(true) - $startedAt > $timeBudget) {
                break;
            }

            $scanResult->update([
                'progress_percent' => 8,
                'current_step' => Str::limit("Akses gagal ({$problem}). AI mencari solusi (percobaan {$attempt}/{$maxAttempts})", 250),
            ]);

            $strategy = $this->recovery->next($website->domain, $history);

            if ($strategy === null) {
                break;
            }

            $scanResult->update([
                'current_step' => Str::limit("Percobaan {$attempt}/{$maxAttempts}: {$strategy['reason']}", 250),
            ]);
        }

        $lastError = end($history)['error'];

        throw new RuntimeException(
            'Website gagal dipindai: '.$this->explainFailure($lastError)
            .' Sudah dicoba '.count($history).' cara akses berbeda (termasuk solusi dari AI) namun tetap gagal.'
            ." Detail teknis: {$lastError}"
        );
    }

    /**
     * Terjemahkan pesan kesalahan teknis menjadi penjelasan penyebab yang mudah dipahami.
     */
    private function explainFailure(string $error): string
    {
        $status = preg_match('/HTTP (\d{3})/', $error, $m) ? (int) $m[1] : null;
        $has = fn (string ...$needles) => Str::contains($error, $needles, ignoreCase: true);

        return match (true) {
            $status === 401 => 'Website meminta autentikasi (HTTP 401) sehingga halaman tidak dapat dibuka pemindai.',
            in_array($status, [403, 406], true) => "Server atau firewall (WAF) website memblokir akses pemindai (HTTP {$status}).",
            $status === 429 => 'Server membatasi jumlah akses (HTTP 429 - terlalu banyak permintaan).',
            $status !== null && $status >= 500 => "Server website sedang bermasalah/down (HTTP {$status}).",
            $has('resolve host', 'cURL error 6', 'getaddrinfo', 'Name or service not known') => 'Domain tidak ditemukan (DNS gagal) - kemungkinan domain sudah kedaluwarsa, belum aktif, atau salah penulisan.',
            $has('timed out', 'cURL error 28', 'timeout') => 'Server website tidak merespons dalam batas waktu (timeout) - kemungkinan server down atau sangat lambat.',
            $has('refused', 'cURL error 7', "Couldn't connect", 'Failed to connect') => 'Koneksi ke server ditolak - layanan web kemungkinan sedang mati atau port diblokir.',
            $has('SSL', 'certificate', 'cURL error 35', 'cURL error 60') => 'Terjadi masalah pada sertifikat/koneksi SSL website.',
            $has('reset', 'cURL error 52', 'cURL error 56', 'Empty reply') => 'Koneksi diputus oleh server sebelum halaman terkirim.',
            default => 'Website tidak dapat diakses.',
        };
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
