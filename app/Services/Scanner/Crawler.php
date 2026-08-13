<?php

namespace App\Services\Scanner;

use App\Services\Scanner\DTO\PageContent;
use App\Services\Scanner\Support\HtmlDom;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Throwable;

class Crawler
{
    public function __construct(private readonly array $config) {}

    /**
     * @return PageContent[]
     */
    public function crawl(string $domain, ?callable $onPage = null): array
    {
        $first = $this->fetch("https://{$domain}");
        $scheme = 'https';

        if (! $first) {
            $first = $this->fetch("http://{$domain}");
            $scheme = 'http';
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

    private function fetch(string $url): ?PageContent
    {
        $chain = [$url];
        $current = $url;
        $start = microtime(true);
        $response = null;

        for ($hop = 0; $hop < 5; $hop++) {
            try {
                $response = Http::withOptions(['allow_redirects' => false])
                    ->withUserAgent($this->config['user_agent'] ?? 'SIDEPSIL-Scanner/1.0')
                    ->timeout((int) ($this->config['timeout'] ?? 10))
                    ->get($current);
            } catch (Throwable) {
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
