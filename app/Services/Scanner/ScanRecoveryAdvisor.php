<?php

namespace App\Services\Scanner;

use App\Services\Llm\SumopodClient;
use Illuminate\Support\Str;

/**
 * Meminta LLM menganalisis penyebab website gagal dipindai dan memilih strategi akses
 * berikutnya (skema, varian host, user agent, SSL, dll.) sampai website berhasil diakses.
 * Strategi dari LLM selalu disaring ke daftar opsi yang diizinkan; bila LLM tidak aktif
 * atau sarannya sudah pernah dicoba, dipakai daftar strategi cadangan.
 */
class ScanRecoveryAdvisor
{
    public const USER_AGENTS = [
        'default' => null,
        'chrome' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126.0.0.0 Safari/537.36',
        'firefox' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:127.0) Gecko/20100101 Firefox/127.0',
        'mobile' => 'Mozilla/5.0 (Linux; Android 14; Pixel 8) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126.0.0.0 Mobile Safari/537.36',
    ];

    private const SYSTEM_PROMPT = <<<'PROMPT'
Anda adalah teknisi jaringan yang membantu pemindai keamanan SIDEPSIL mengakses website OPD pemerintah daerah
yang gagal dipindai. Analisis pesan kesalahan dari percobaan sebelumnya, tentukan penyebab paling mungkin,
lalu pilih SATU strategi akses baru yang belum pernah dicoba.

Opsi yang tersedia:
- "scheme": "https" | "http"
- "host_variant": "original" | "www" | "non_www"
- "path": path awal halaman, diawali "/" (mis. "/", "/index.php", "/beranda")
- "user_agent": "default" | "chrome" | "firefox" | "mobile" (gunakan browser bila situs memblokir bot/WAF, HTTP 403/406/429)
- "verify_ssl": true | false (false bila sertifikat SSL kedaluwarsa/tidak valid/self-signed)
- "force_ipv4": true | false (true bila masalah resolusi/koneksi IPv6)
- "http_1_1": true | false (true bila ada error protokol HTTP/2 atau koneksi terputus)
- "browser_headers": true | false (kirim header Accept/Accept-Language layaknya browser)
- "timeout": 5-30 detik (naikkan bila terjadi timeout)

Jawab HANYA dalam JSON:
{"diagnosis":"penyebab singkat dalam Bahasa Indonesia","strategy":{"scheme":"https","host_variant":"original","path":"/","user_agent":"chrome","verify_ssl":true,"force_ipv4":false,"http_1_1":false,"browser_headers":true,"timeout":15}}
PROMPT;

    /** Strategi cadangan bila LLM tidak aktif atau sarannya sudah pernah dicoba. */
    private const FALLBACK_STRATEGIES = [
        ['scheme' => 'https', 'user_agent' => 'chrome', 'browser_headers' => true, 'timeout' => 20, 'reason' => 'Menyamar sebagai browser untuk melewati pemblokiran bot'],
        ['scheme' => 'https', 'user_agent' => 'chrome', 'browser_headers' => true, 'verify_ssl' => false, 'timeout' => 20, 'reason' => 'Mengabaikan sertifikat SSL yang tidak valid'],
        ['scheme' => 'https', 'host_variant' => 'www', 'user_agent' => 'chrome', 'browser_headers' => true, 'verify_ssl' => false, 'timeout' => 20, 'reason' => 'Mencoba varian host dengan www'],
        ['scheme' => 'https', 'host_variant' => 'non_www', 'user_agent' => 'chrome', 'browser_headers' => true, 'verify_ssl' => false, 'timeout' => 20, 'reason' => 'Mencoba varian host tanpa www'],
        ['scheme' => 'http', 'host_variant' => 'www', 'user_agent' => 'chrome', 'browser_headers' => true, 'timeout' => 20, 'reason' => 'Mencoba HTTP dengan varian host www'],
        ['scheme' => 'https', 'user_agent' => 'chrome', 'browser_headers' => true, 'verify_ssl' => false, 'force_ipv4' => true, 'http_1_1' => true, 'timeout' => 30, 'reason' => 'Memaksa IPv4 dan HTTP/1.1 dengan batas waktu lebih lama'],
        ['scheme' => 'http', 'user_agent' => 'mobile', 'browser_headers' => true, 'force_ipv4' => true, 'http_1_1' => true, 'timeout' => 30, 'reason' => 'Mencoba HTTP dengan user agent perangkat seluler'],
        ['scheme' => 'https', 'path' => '/index.php', 'user_agent' => 'firefox', 'browser_headers' => true, 'verify_ssl' => false, 'timeout' => 30, 'reason' => 'Mengakses langsung /index.php'],
    ];

    public function __construct(private readonly SumopodClient $llm) {}

    /**
     * @param  array<int, array{strategy: array, error: string}>  $history  percobaan sebelumnya
     * @return array|null strategi berikutnya (sudah disaring), atau null bila semua opsi sudah dicoba
     */
    public function next(string $domain, array $history): ?array
    {
        $tried = array_map(fn (array $attempt) => $this->signature($attempt['strategy']), $history);

        $advice = $this->llm->chatJson(self::SYSTEM_PROMPT, $this->prompt($domain, $history));

        if (is_array($advice['strategy'] ?? null)) {
            $strategy = $this->sanitize($advice['strategy']);
            $diagnosis = trim((string) ($advice['diagnosis'] ?? ''));
            $strategy['reason'] = Str::limit($diagnosis !== '' ? "AI: {$diagnosis}" : 'Strategi dari AI', 150);

            if (! in_array($this->signature($strategy), $tried, true)) {
                return $strategy;
            }
        }

        foreach (self::FALLBACK_STRATEGIES as $fallback) {
            $strategy = $this->sanitize($fallback);

            if (! in_array($this->signature($strategy), $tried, true)) {
                return $strategy;
            }
        }

        return null;
    }

    public function describe(array $strategy): string
    {
        if ($strategy === []) {
            return 'bawaan (https lalu http, user agent SIDEPSIL)';
        }

        return collect($strategy)->except('reason')
            ->map(fn ($value, $key) => $key.'='.(is_bool($value) ? ($value ? 'true' : 'false') : $value))
            ->implode(', ');
    }

    /**
     * Batasi saran LLM ke opsi yang diizinkan agar pemindai tidak diarahkan ke host lain.
     */
    private function sanitize(array $input): array
    {
        $path = (string) ($input['path'] ?? '/');
        $bool = fn (string $key, bool $default) => filter_var($input[$key] ?? $default, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? $default;

        return [
            'scheme' => in_array($input['scheme'] ?? null, ['https', 'http'], true) ? $input['scheme'] : 'https',
            'host_variant' => in_array($input['host_variant'] ?? null, ['original', 'www', 'non_www'], true) ? $input['host_variant'] : 'original',
            'path' => strlen($path) <= 100 && preg_match('#^/(?!/)[A-Za-z0-9._~\-/]*$#', $path) ? $path : '/',
            'user_agent' => array_key_exists((string) ($input['user_agent'] ?? ''), self::USER_AGENTS) ? $input['user_agent'] : 'default',
            'verify_ssl' => $bool('verify_ssl', true),
            'force_ipv4' => $bool('force_ipv4', false),
            'http_1_1' => $bool('http_1_1', false),
            'browser_headers' => $bool('browser_headers', false),
            'timeout' => max(5, min(30, (int) ($input['timeout'] ?? 15))),
            'reason' => Str::limit(trim((string) ($input['reason'] ?? '')), 150),
        ];
    }

    private function signature(array $strategy): string
    {
        return json_encode(collect($strategy)->except(['reason', 'timeout'])->sortKeys()->all());
    }

    private function prompt(string $domain, array $history): string
    {
        $lines = collect($history)->map(fn (array $attempt, int $i) => '#'.($i + 1)
            .' strategi: '.$this->describe($attempt['strategy'])
            ."\n   hasil: {$attempt['error']}")
            ->implode("\n");

        return "Domain: {$domain}\nPercobaan sebelumnya yang gagal:\n{$lines}";
    }
}
