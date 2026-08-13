<?php

namespace Database\Seeders;

use App\Models\ScanResult;
use App\Models\Website;
use Illuminate\Database\Seeder;

class ScanResultSeeder extends Seeder
{
    public function run(): void
    {
        $fixtures = [
            [
                'domain' => 'dinsos.kuburayakab.go.id',
                'scan_date' => now()->subDays(2),
                'status' => 'flagged',
                'risk_score' => 87,
                'threat_type' => 'Judi Online',
                'keyword_count' => 14,
                'judol_link_count' => 6,
                'infected_pages' => 3,
                'findings_summary' => 'Ditemukan sisipan konten dan tautan yang mengarah ke situs judi online pada beberapa halaman.',
                'notes' => 'Perlu tindak lanjut segera oleh admin website.',
            ],
            [
                'domain' => 'disdikbud.kuburayakab.go.id',
                'scan_date' => now()->subDays(5),
                'status' => 'needs_review',
                'risk_score' => 42,
                'threat_type' => 'Indikasi Ringan',
                'keyword_count' => 3,
                'judol_link_count' => 0,
                'infected_pages' => 1,
                'findings_summary' => 'Terdapat kata kunci mencurigakan pada komentar pengunjung yang belum dimoderasi.',
                'notes' => null,
            ],
        ];

        foreach ($fixtures as $fixture) {
            $website = Website::where('domain', $fixture['domain'])->first();

            if (! $website) {
                continue;
            }

            ScanResult::create([
                'website_id' => $website->id,
                'scan_date' => $fixture['scan_date'],
                'status' => $fixture['status'],
                'scan_state' => 'completed',
                'progress_percent' => 100,
                'started_at' => $fixture['scan_date'],
                'completed_at' => $fixture['scan_date'],
                'risk_score' => $fixture['risk_score'],
                'threat_type' => $fixture['threat_type'],
                'keyword_count' => $fixture['keyword_count'],
                'judol_link_count' => $fixture['judol_link_count'],
                'infected_pages' => $fixture['infected_pages'],
                'findings_summary' => $fixture['findings_summary'],
                'notes' => $fixture['notes'],
            ]);

            $website->update([
                'last_scanned_at' => $fixture['scan_date'],
                'latest_risk_score' => $fixture['risk_score'],
            ]);
        }
    }
}
