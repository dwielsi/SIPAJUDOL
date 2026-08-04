<?php

namespace Database\Seeders;

use App\Models\Website;
use Illuminate\Database\Seeder;

class WebsiteSeeder extends Seeder
{
    public function run(): void
    {
        $fixtures = [
            ['opd_name' => 'Dinas Komunikasi dan Informatika', 'website_name' => 'Website Diskominfo Kubu Raya', 'domain' => 'diskominfo.kuburayakab.go.id', 'status' => 'safe'],
            ['opd_name' => 'Dinas Kesehatan', 'website_name' => 'Website Dinas Kesehatan', 'domain' => 'dinkes.kuburayakab.go.id', 'status' => 'safe'],
            ['opd_name' => 'Dinas Pendidikan dan Kebudayaan', 'website_name' => 'Website Disdikbud', 'domain' => 'disdikbud.kuburayakab.go.id', 'status' => 'needs_review'],
            ['opd_name' => 'Badan Pengelola Keuangan dan Aset Daerah', 'website_name' => 'Website BPKAD', 'domain' => 'bpkad.kuburayakab.go.id', 'status' => 'safe'],
            ['opd_name' => 'Dinas Pekerjaan Umum dan Penataan Ruang', 'website_name' => 'Website PUPR', 'domain' => 'pupr.kuburayakab.go.id', 'status' => 'safe'],
            ['opd_name' => 'Dinas Sosial', 'website_name' => 'Website Dinas Sosial', 'domain' => 'dinsos.kuburayakab.go.id', 'status' => 'flagged'],
            ['opd_name' => 'Kecamatan Sungai Raya', 'website_name' => 'Website Kecamatan Sungai Raya', 'domain' => 'sungairaya.kuburayakab.go.id', 'status' => 'safe'],
            ['opd_name' => 'Dinas Perhubungan', 'website_name' => 'Website Dishub', 'domain' => 'dishub.kuburayakab.go.id', 'status' => 'safe'],
            ['opd_name' => 'Dinas Perpustakaan dan Kearsipan', 'website_name' => 'Website Perpustakaan Daerah', 'domain' => 'perpustakaan.kuburayakab.go.id', 'status' => 'needs_review'],
            ['opd_name' => 'Badan Kepegawaian dan Pengembangan SDM', 'website_name' => 'Website BKPSDM', 'domain' => 'bkpsdm.kuburayakab.go.id', 'status' => 'safe'],
        ];

        foreach ($fixtures as $fixture) {
            Website::factory()->create([
                'name' => $fixture['website_name'],
                'opd_name' => $fixture['opd_name'],
                'website_name' => $fixture['website_name'],
                'domain' => $fixture['domain'],
                'status' => $fixture['status'],
            ]);
        }
    }
}
