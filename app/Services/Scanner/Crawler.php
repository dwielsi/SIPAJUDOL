<?php

namespace App\Services\Scanner;

use App\Services\Scanner\DTO\PageContent;
use App\Services\Scanner\Support\HtmlDom;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Throwable;

class Crawler
{
    private ?string $lastError = null;

    /**
     * @param  array  $strategy  strategi akses dari ScanRecoveryAdvisor; kosong berarti cara bawaan
     *                           (https lalu http dengan user agent SIDEPSIL).
     */
    public function __construct(
        private readonly array $config,
        private readonly array $strategy = [],
    ) {}

    /**
     * Pesan kesalahan terakhir saat mengambil halaman (mis. error koneksi/SSL/timeout).
     */
    public function lastError(): ?string
    {
        return $this->lastError;
    }

    /**
     * @return PageContent[]
     */
    public function crawl(string $domain, ?callable $onPage = null): array
    {
        $first = null;

        foreach ($this->startUrls($domain) as $startUrl) {
            if ($first = $this->fetch($startUrl)) {
                break;
            }
        }

        if (! $first) {
            return [];
        }

        $maxPages = (int) ($this->config['max_pages'] ?? 15);
        $maxDepth = (int) ($this->config['max_depth'] ?? 2);
        $host = parse_url($first->url, PHP_URL_HOST) ?: $domain;

        $pages = [$first];
        $visited = [rtrim($first->url, '/') => true];
        $queue = [];

        foreach ($this->extractSameDomainLinks($first->html, $first->url, $host) as $link) {
            $queue[] = [$link, 1];
        }

        $onPage && $onPage(count($pages), $maxPages, $first->url);

        while ($queue && count($pages) < $maxPages) {
            [$url, $depth] = array_shift($queue);
            $normalized = rtrim($url, '/');

            if (isset($visited[$normalized]) || $depth > $maxDepth) {
                continue;
            }
            $visited[$normalized] = true;

            $page = $this->fetch($url);

            if (! $page) {
                continue;
            }

            $pages[] = $page;
            $onPage && $onPage(count($pages), $maxPages, $url);

            if ($depth < $maxDepth) {
                foreach ($this->extractSameDomainLinks($page->html, $url, $host) as $link) {
                    $queue[] = [$link, $depth + 1];
                }
            }
        }

        return $pages;
    }

    /**
     * @return string[]
     */
    private function startUrls(string $domain): array
    {
        if ($this->strategy === []) {
            return ["https://{$domain}", "http://{$domain}"];
        }

        $bare = preg_replace('/^www\./i', '', $domain);
        $host = match ($this->strategy['host_variant'] ?? 'original') {
            'www' => "www.{$bare}",
            'non_www' => $bare,
            default => $domain,
        };

        return [($this->strategy['scheme'] ?? 'https')."://{$host}".($this->strategy['path'] ?? '/')];
    }

    private function request(): PendingRequest
    {
        $options = [
            'allow_redirects' => false,
            'verify' => (bool) ($this->strategy['verify_ssl'] ?? true),
        ];

        if ($this->strategy['force_ipv4'] ?? false) {
            $options['force_ip_resolve'] = 'v4';
        }

        if ($this->strategy['http_1_1'] ?? false) {
            $options['version'] = '1.1';
        }

        $userAgent = ScanRecoveryAdvisor::USER_AGENTS[$this->strategy['user_agent'] ?? 'default'] ?? null;

        $request = Http::withOptions($options)
            ->withUserAgent($userAgent ?? $this->config['user_agent'] ?? 'SIDEPSIL-Scanner/1.0')
            ->timeout((int) ($this->strategy['timeout'] ?? $this->config['timeout'] ?? 10));

        if ($this->strategy['browser_headers'] ?? false) {
            $request->withHeaders([
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                'Accept-Language' => 'id-ID,id;q=0.9,en-US;q=0.8,en;q=0.7',
            ]);
        }

        return $request;
    }

    private function fetch(string $url): ?PageContent
    {
        $chain = [$url];
        $current = $url;
        $start = microtime(true);
        $response = null;

        for ($hop = 0; $hop < 5; $hop++) {
            try {
                $response = $this->request()->get($current);
            } catch (Throwable $e) {
                $this->lastError = Str::limit($e->getMessage(), 300);

                return null;
            }

            if (in_array($response->status(), [301, 302, 303, 307, 308], true) && $response->header('Location')) {
                $current = $this->resolveUrl($response->header('Location'), $current);
                $chain[] = $current;

                continue;
            }

            break;
        }

        if (! $response) {
            return null;
        }

        return new PageContent(
            url: $chain[0],
            statusCode: $response->status(),
            headers: $response->headers(),
            html: (string) $response->body(),
            responseTimeMs: (int) round((microtime(true) - $start) * 1000),
            redirectChain: $chain,
        );
    }

    private function resolveUrl(string $location, string $base): string
    {
        if (Str::startsWith($location, ['http://', 'https://'])) {
            return $location;
        }

        $parts = parse_url($base);
        $scheme = $parts['scheme'] ?? 'https';
        $host = $parts['host'] ?? '';

        return Str::startsWith($location, '/')
            ? "{$scheme}://{$host}{$location}"
            : rtrim($base, '/').'/'.ltrim($location, '/');
    }

    private function extractSameDomainLinks(string $html, string $baseUrl, ?string $host): array
    {
        $dom = new HtmlDom($html);
        $links = [];

        foreach ($dom->links() as $link) {
            $href = $link['href'];

            if ($href === '' || Str::startsWith($href, ['#', 'mailto:', 'tel:', 'javascript:'])) {
                continue;
            }

            $resolved = $this->resolveUrl($href, $baseUrl);

            if (parse_url($resolved, PHP_URL_HOST) === $host) {
                $links[] = $resolved;
            }
        }

        return array_values(array_unique($links));
    }
}
