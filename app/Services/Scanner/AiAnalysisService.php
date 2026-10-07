<?php

namespace App\Services\Scanner;

use App\Models\ScanResult;
use App\Services\Llm\SumopodClient;
use App\Services\Scanner\DTO\Finding;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class AiAnalysisService
{
    private const SYSTEM_PROMPT = <<<'PROMPT'
Anda adalah analis keamanan siber Diskominfo yang menyusun analisis hasil pemindaian website OPD pemerintah daerah.
Berdasarkan data pemindaian yang diberikan, tulis dalam Bahasa Indonesia formal:
- "summary": ringkasan temuan (2-4 kalimat), sebutkan jenis ancaman utama dan halaman terdampak bila relevan.
- "recommendation": langkah penanganan konkret dan berurutan (3-6 langkah dalam satu paragraf, pisahkan dengan nomor).
Jangan mengarang temuan yang tidak ada pada data. Jawab HANYA dalam JSON:
{"summary":"...","recommendation":"..."}
PROMPT;

    private const STATUS_LABELS = [
        'flagged' => 'Terindikasi',
        'needs_review' => 'Perlu Pemeriksaan',
        'safe' => 'Aman',
    ];

    private const RECOMMENDATIONS = [
        'backdoor' => 'Hapus file backdoor yang ditemukan dan ganti seluruh kredensial akses (FTP, cPanel, CMS admin).',
        'php_shell' => 'Hapus file shell PHP mencurigakan dan audit direktori upload untuk file eksekusi asing.',
        'foreign_file' => 'Periksa dan hapus file asing yang tidak dikenal pada direktori aset/upload.',
        'malware_signature' => 'Jalankan pemindaian malware menyeluruh pada server dan perbarui seluruh plugin/tema CMS.',
        'defacement' => 'Pulihkan tampilan situs dari cadangan terakhir yang bersih dan tingkatkan keamanan panel admin.',
        'illegal_content' => 'Telusuri dan hapus konten/tautan ilegal yang disisipkan, lalu audit akun admin yang memiliki akses tulis.',
        'redirect' => 'Periksa konfigurasi pengalihan (redirect) pada server dan hapus aturan yang tidak sah.',
        'iframe' => 'Hapus elemen iframe mencurigakan dan batasi sumber konten yang dapat disisipkan.',
        'eval_base64' => 'Audit skrip yang menggunakan eval()/base64 untuk memastikan tidak ada payload berbahaya.',
        'hidden_link' => 'Hapus tautan tersembunyi yang berpotensi digunakan untuk SEO spam/konten ilegal.',
        'external_link' => 'Audit tautan eksternal yang tidak relevan dengan konten resmi OPD.',
        'script_injection' => 'Hapus skrip pihak ketiga yang tidak dikenal dan verifikasi integritas berkas tema/plugin.',
        'js_injection' => 'Bersihkan skrip JavaScript yang terobfuskasi dan pastikan sumber skrip berasal dari domain tepercaya.',
        'seo_spam' => 'Bersihkan konten yang disisipi kata kunci spam.',
        'meta_tag_spam' => 'Perbarui meta tag halaman agar sesuai dengan konten resmi OPD.',
    ];

    private const CATEGORY_LABELS = [
        'backdoor' => 'backdoor',
        'php_shell' => 'shell PHP',
        'foreign_file' => 'file asing',
        'malware_signature' => 'signature malware',
        'defacement' => 'defacement',
        'illegal_content' => 'konten ilegal',
        'redirect' => 'pengalihan (redirect) mencurigakan',
        'iframe' => 'iframe mencurigakan',
        'eval_base64' => 'skrip eval/base64',
        'hidden_link' => 'tautan tersembunyi',
        'external_link' => 'tautan eksternal tidak wajar',
        'script_injection' => 'injeksi skrip',
        'js_injection' => 'injeksi JavaScript',
        'seo_spam' => 'SEO spam',
        'meta_tag_spam' => 'meta tag spam',
    ];

    /** Kategori berlabel bebas (data lama/seeder) dipetakan ke kategori baku. */
    private const CATEGORY_ALIASES = [
        'Konten Tersisipi' => 'illegal_content',
        'Komentar Pengunjung' => 'illegal_content',
        'Tautan Mencurigakan' => 'hidden_link',
        'Halaman Tersembunyi' => 'foreign_file',
    ];

    public function __construct(private readonly SumopodClient $llm) {}

    /**
     * Narasi untuk laporan dari hasil scan yang tersimpan. Narasi AI dipakai bila ada;
     * bila kosong, disusun dari temuan scan dengan template bawaan (tanpa memanggil LLM).
     *
     * @return array{summary: string, conclusion: string, recommendation: string}
     */
    public function narrativeFor(ScanResult $scanResult): array
    {
        $scanResult->loadMissing(['website', 'findings']);

        $websiteName = $scanResult->website?->displayName() ?? 'Website';
        $findings = $scanResult->findings;

        $categories = $findings->pluck('category')
            ->filter()
            ->map(fn (string $category) => self::CATEGORY_ALIASES[$category] ?? $category)
            ->unique()
            ->values();

        if ($categories->isEmpty()) {
            $categories = collect([
                'illegal_content' => $scanResult->judol_link_count,
                'redirect' => $scanResult->redirect_count,
                'malware_signature' => $scanResult->malware_count,
                'external_link' => $scanResult->external_link_count,
            ])->filter(fn ($count) => $count > 0)->keys();
        }

        $count = $findings->count() ?: (int) ($scanResult->judol_link_count
            + $scanResult->redirect_count + $scanResult->malware_count + $scanResult->external_link_count);
        $status = $scanResult->status ?? 'safe';
        $riskScore = (int) $scanResult->risk_score;

        return [
            'summary' => $scanResult->ai_summary
                ?: $this->summary($websiteName, $count, $categories, $findings->pluck('page_url')->filter()->unique()->values()),
            'conclusion' => $this->conclusion($websiteName, $status, $riskScore),
            'recommendation' => $scanResult->ai_recommendation ?: $this->recommendation($categories, $status),
        ];
    }

    /**
     * Analisis naratif hasil scan. Narasi disusun oleh LLM; template bawaan dipakai
     * sebagai cadangan bila LLM tidak aktif atau gagal merespons.
     *
     * @param  Finding[]  $findings
     * @return array{summary: string, conclusion: string, recommendation: string, priority: string}
     */
    public function analyze(array $findings, int $riskScore, string $status, string $websiteName): array
    {
        $categories = collect($findings)->pluck('category')->unique()->values();
        $count = count($findings);

        $result = [
            'summary' => $this->summary($websiteName, $count, $categories),
            'conclusion' => $this->conclusion($websiteName, $status, $riskScore),
            'recommendation' => $this->recommendation($categories, $status),
            'priority' => match (true) {
                $riskScore >= 61 => 'Tinggi',
                $riskScore >= 31 => 'Sedang',
                default => 'Rendah',
            },
        ];

        $narrative = $this->llm->chatJson(self::SYSTEM_PROMPT, $this->llmPrompt($findings, $riskScore, $status, $websiteName));

        // Kesimpulan selalu memakai template baku agar formatnya seragam; LLM hanya menyusun
        // ringkasan dan rekomendasi.
        foreach (['summary', 'recommendation'] as $key) {
            if (is_string($narrative[$key] ?? null) && trim($narrative[$key]) !== '') {
                $result[$key] = trim($narrative[$key]);
            }
        }

        return $result;
    }

    /**
     * @param  Finding[]  $findings
     */
    private function llmPrompt(array $findings, int $riskScore, string $status, string $websiteName): string
    {
        $lines = collect($findings)
            ->unique(fn ($f) => "{$f->category}|{$f->evidence}|{$f->pageUrl}")
            ->take(40)
            ->map(fn ($f) => "- [{$f->severity}] {$f->category}: {$f->message}"
                .($f->evidence ? ' | bukti: '.Str::limit($f->evidence, 150) : '')
                .($f->pageUrl ? " | halaman: {$f->pageUrl}" : ''))
            ->implode('
');

        return "Website: {$websiteName}
"
            .'Status: '.(self::STATUS_LABELS[$status] ?? $status).'
'
            ."Skor risiko: {$riskScore}/100
"
            .'Jumlah temuan: '.count($findings).'
'
            .'Daftar temuan:
'.($lines !== '' ? $lines : '- Tidak ada temuan.');
    }

    private function summary(string $websiteName, int $count, Collection $categories, ?Collection $pages = null): string
    {
        if ($count === 0) {
            return "Pemindaian terhadap {$websiteName} tidak menemukan indikasi ancaman pada seluruh halaman yang diperiksa.";
        }

        $labels = $categories->map(fn (string $category) => self::CATEGORY_LABELS[$category] ?? $category);

        $text = "Pemindaian terhadap {$websiteName} menemukan {$count} temuan pada ".
            $labels->count().' kategori ancaman: '.$labels->implode(', ').'.';

        if ($pages && $pages->isNotEmpty()) {
            $text .= " Temuan terdeteksi pada {$pages->count()} halaman, antara lain ".$pages->take(3)->implode(', ').'.';
        }

        return $text;
    }

    /**
     * Kesimpulan baku: "<Website> <STATUS> ... dengan skor risiko N/100 dan <tindak lanjut>."
     */
    public function conclusion(string $websiteName, string $status, int $riskScore): string
    {
        $websiteName = Str::startsWith(Str::lower($websiteName), 'website') ? $websiteName : "Website {$websiteName}";

        return match ($status) {
            'flagged' => "{$websiteName} TERINDIKASI disusupi dengan skor risiko {$riskScore}/100 dan memerlukan penanganan segera.",
            'needs_review' => "{$websiteName} PERLU PEMERIKSAAN lebih lanjut dengan skor risiko {$riskScore}/100 dan memerlukan verifikasi oleh pengelola website.",
            default => "{$websiteName} dinyatakan AMAN dengan skor risiko {$riskScore}/100 dan cukup dipantau secara rutin.",
        };
    }

    private function recommendation(Collection $categories, string $status): string
    {
        $items = $categories
            ->map(fn (string $category) => self::RECOMMENDATIONS[$category] ?? null)
            ->filter()
            ->values();

        $items->push(match ($status) {
            'flagged' => 'Segera nonaktifkan sementara akses publik dan lakukan investigasi menyeluruh.',
            'needs_review' => 'Lakukan pemeriksaan manual terhadap temuan dan pantau pada pemindaian berikutnya.',
            default => 'Lanjutkan pemantauan rutin sesuai jadwal.',
        });

        return $items->unique()
            ->values()
            ->map(fn (string $item, int $index) => ($index + 1).'. '.$item)
            ->implode(' ');
    }
}
