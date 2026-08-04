<?php

namespace App\Services\Scanner;

use Illuminate\Support\Collection;

class AiAnalysisService
{
    private const RECOMMENDATIONS = [
        'backdoor' => 'Hapus file backdoor yang ditemukan dan ganti seluruh kredensial akses (FTP, cPanel, CMS admin).',
        'php_shell' => 'Hapus file shell PHP mencurigakan dan audit direktori upload untuk file eksekusi asing.',
        'foreign_file' => 'Periksa dan hapus file asing yang tidak dikenal pada direktori aset/upload.',
        'malware_signature' => 'Jalankan pemindaian malware menyeluruh pada server dan perbarui seluruh plugin/tema CMS.',
        'defacement' => 'Pulihkan tampilan situs dari cadangan terakhir yang bersih dan tingkatkan keamanan panel admin.',
        'keyword_judol' => 'Telusuri dan hapus konten/tautan judi online yang disisipkan, lalu audit akun admin yang memiliki akses tulis.',
        'redirect' => 'Periksa konfigurasi pengalihan (redirect) pada server dan hapus aturan yang tidak sah.',
        'iframe' => 'Hapus elemen iframe mencurigakan dan batasi sumber konten yang dapat disisipkan.',
        'eval_base64' => 'Audit skrip yang menggunakan eval()/base64 untuk memastikan tidak ada payload berbahaya.',
        'hidden_link' => 'Hapus tautan tersembunyi yang berpotensi digunakan untuk SEO spam/judi online.',
        'external_link' => 'Audit tautan eksternal yang tidak relevan dengan konten resmi OPD.',
        'script_injection' => 'Hapus skrip pihak ketiga yang tidak dikenal dan verifikasi integritas berkas tema/plugin.',
        'js_injection' => 'Bersihkan skrip JavaScript yang terobfuskasi dan pastikan sumber skrip berasal dari domain tepercaya.',
        'seo_spam' => 'Bersihkan konten yang disisipi kata kunci spam.',
        'meta_tag_spam' => 'Perbarui meta tag halaman agar sesuai dengan konten resmi OPD.',
    ];

    /**
     * @param  \App\Services\Scanner\DTO\Finding[]  $findings
     * @return array{summary: string, conclusion: string, recommendation: string, priority: string}
     */
    public function analyze(array $findings, int $riskScore, string $status, string $websiteName): array
    {
        $categories = collect($findings)->pluck('category')->unique()->values();
        $count = count($findings);

        return [
            'summary' => $this->summary($websiteName, $count, $categories),
            'conclusion' => $this->conclusion($websiteName, $status, $riskScore),
            'recommendation' => $this->recommendation($categories, $status),
            'priority' => match (true) {
                $riskScore >= 61 => 'Tinggi',
                $riskScore >= 31 => 'Sedang',
                default => 'Rendah',
            },
        ];
    }

    private function summary(string $websiteName, int $count, Collection $categories): string
    {
        if ($count === 0) {
            return "Pemindaian terhadap {$websiteName} tidak menemukan indikasi ancaman pada seluruh halaman yang diperiksa.";
        }

        return "Pemindaian terhadap {$websiteName} menemukan {$count} temuan pada ".
            $categories->count().' kategori ancaman: '.$categories->implode(', ').'.';
    }

    private function conclusion(string $websiteName, string $status, int $riskScore): string
    {
        return match ($status) {
            'flagged' => "Website {$websiteName} TERINDIKASI mengandung konten atau skrip berbahaya dengan skor risiko {$riskScore}/100.",
            'needs_review' => "Website {$websiteName} PERLU PEMERIKSAAN lebih lanjut dengan skor risiko {$riskScore}/100.",
            default => "Website {$websiteName} dinyatakan AMAN dengan skor risiko {$riskScore}/100.",
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

        return $items->unique()->implode(' ');
    }
}
