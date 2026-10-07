<?php

namespace App\Services\Scanner;

use App\Services\Llm\SumopodClient;
use App\Services\Scanner\DTO\Finding;
use App\Services\Scanner\DTO\PageContent;
use Illuminate\Support\Str;

/**
 * Screening konten ilegal pada halaman website menggunakan LLM
 * (menggantikan pencocokan database keyword manual).
 */
class LlmContentScreener
{
    public const CATEGORIES = [
        'judol' => 'Judi Online',
        'pornografi' => 'Pornografi',
        'penipuan' => 'Penipuan/Scam',
        'phishing' => 'Phishing',
        'narkoba' => 'Narkoba',
        'lainnya' => 'Konten Ilegal Lainnya',
    ];

    private const SEVERITIES = ['low', 'medium', 'high', 'critical'];

    private const SYSTEM_PROMPT = <<<'PROMPT'
Anda adalah analis keamanan siber yang menyaring konten website resmi pemerintah daerah (OPD) di Indonesia.
Tugas Anda: temukan konten ilegal yang disisipkan atau dipromosikan pada halaman, dengan kategori:
- judol (judi online: slot, togel, casino, situs gacor, link alternatif, deposit, maxwin, dll.)
- pornografi
- penipuan (scam, investasi bodong, pinjol ilegal)
- phishing (meminta kredensial/data pribadi dengan menyamar)
- narkoba (penjualan/promosi narkotika)
- lainnya (konten ilegal lain)

Aturan:
- Hanya tandai konten yang MEMPROMOSIKAN atau DISISIPKAN secara tidak sah. Jangan tandai konten resmi pemerintah
  seperti berita, sosialisasi bahaya judi online/narkoba, imbauan, atau penegakan hukum.
- "evidence" harus kutipan persis dari teks halaman (maksimal 150 karakter).
- "severity": critical (konten ilegal masif/halaman diambil alih), high (promosi jelas), medium (indikasi kuat), low (indikasi lemah).
- "page" adalah nomor halaman sesuai penanda [HALAMAN n] pada input.
- Jawab HANYA dalam JSON dengan format:
{"findings":[{"page":1,"category":"judol","severity":"high","evidence":"...","reason":"penjelasan singkat dalam Bahasa Indonesia"}]}
- Jika tidak ada konten ilegal, jawab {"findings":[]}.
PROMPT;

    public function __construct(
        private readonly SumopodClient $llm,
        private readonly int $maxCharsPerPage = 4000,
        private readonly int $pagesPerRequest = 5,
    ) {}

    public function enabled(): bool
    {
        return $this->llm->enabled();
    }

    /**
     * @param  PageContent[]  $pages
     * @return array<string, Finding[]> temuan dikelompokkan per URL halaman
     */
    public function screen(array $pages, ?callable $onBatch = null): array
    {
        if (! $this->enabled()) {
            return [];
        }

        $findings = [];
        $batches = array_chunk($pages, max(1, $this->pagesPerRequest));

        foreach ($batches as $batchIndex => $batch) {
            $onBatch && $onBatch($batchIndex + 1, count($batches));

            $prompt = '';
            $indexed = [];

            foreach ($batch as $i => $page) {
                $text = $this->readableText($page->html);

                if ($text === '') {
                    continue;
                }

                $number = $i + 1;
                $indexed[$number] = $page;
                $prompt .= "[HALAMAN {$number}] URL: {$page->url}\n{$text}\n\n";
            }

            if ($indexed === []) {
                continue;
            }

            $result = $this->llm->chatJson(self::SYSTEM_PROMPT, trim($prompt));

            foreach ((array) ($result['findings'] ?? []) as $item) {
                if (! is_array($item) || blank($item['evidence'] ?? null)) {
                    continue;
                }

                $page = $indexed[(int) ($item['page'] ?? 0)] ?? null;

                if (! $page) {
                    continue;
                }

                $category = array_key_exists((string) ($item['category'] ?? ''), self::CATEGORIES) ? $item['category'] : 'lainnya';
                $severity = in_array($item['severity'] ?? '', self::SEVERITIES, true) ? $item['severity'] : 'medium';
                $reason = Str::limit(trim((string) ($item['reason'] ?? '')), 250);

                $findings[$page->url][] = new Finding(
                    category: 'illegal_content',
                    severity: $severity,
                    message: 'Konten ilegal ('.self::CATEGORIES[$category].') terdeteksi oleh AI'.($reason !== '' ? ": {$reason}" : ''),
                    evidence: Str::limit(trim((string) $item['evidence']), 200),
                    pageUrl: $page->url,
                    location: self::CATEGORIES[$category],
                );
            }
        }

        return $findings;
    }

    /**
     * Ambil teks yang terbaca dari HTML, termasuk teks elemen tersembunyi (tempat konten
     * ilegal sering disisipkan) serta isi meta description/keywords.
     */
    private function readableText(string $html): string
    {
        $meta = '';
        if (preg_match_all('#<meta[^>]+(?:name|property)=["\'](?:description|keywords|og:title|og:description)["\'][^>]*content=["\']([^"\']*)["\']#i', $html, $m)) {
            $meta = implode(' | ', $m[1]);
        }

        $html = preg_replace('#<(script|style|noscript|svg)\b[^>]*>.*?</\1>#is', ' ', $html) ?? $html;
        $html = preg_replace('#<(br|p|div|li|h[1-6]|tr|td|a|span)\b#i', ' <$1', $html) ?? $html;

        $text = html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = trim(preg_replace('/\s+/u', ' ', $meta.' '.$text) ?? '');

        return Str::limit($text, $this->maxCharsPerPage, '');
    }
}
