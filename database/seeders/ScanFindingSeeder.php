<?php

namespace Database\Seeders;

use App\Models\ScanFinding;
use App\Models\ScanResult;
use App\Models\Website;
use Illuminate\Database\Seeder;

class ScanFindingSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedFor('dinsos.kuburayakab.go.id', [
            ['category' => 'Konten Tersisipi', 'severity' => 'critical', 'message' => 'Ditemukan teks promosi judi online pada halaman beranda.', 'evidence' => 'https://dinsos.kuburayakab.go.id/index.php?slot=xyz123'],
            ['category' => 'Tautan Mencurigakan', 'severity' => 'high', 'message' => 'Terdapat 6 tautan keluar menuju domain judi online yang dikenal.', 'evidence' => 'situs-judol-abc.example'],
            ['category' => 'Halaman Tersembunyi', 'severity' => 'medium', 'message' => 'Ditemukan halaman baru yang tidak terdaftar di sitemap resmi.', 'evidence' => '/wp-content/uploads/2026/promo-slot/'],
        ]);

        $this->seedFor('disdikbud.kuburayakab.go.id', [
            ['category' => 'Komentar Pengunjung', 'severity' => 'low', 'message' => 'Kata kunci terkait judi online muncul pada kolom komentar berita.', 'evidence' => 'Komentar pada artikel ID #482'],
        ]);
    }

    private function seedFor(string $domain, array $findings): void
    {
        $website = Website::where('domain', $domain)->first();

        if (! $website) {
            return;
        }

        $scanResult = ScanResult::where('website_id', $website->id)->latest('scan_date')->first();

        if (! $scanResult) {
            return;
        }

        foreach ($findings as $finding) {
            ScanFinding::create([
                'scan_result_id' => $scanResult->id,
                ...$finding,
            ]);
        }
    }
}
