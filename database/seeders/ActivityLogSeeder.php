<?php

namespace Database\Seeders;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Database\Seeder;

class ActivityLogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $kabid = User::where('username', 'kabid')->first();

        $entries = [
            ['user' => $kabid, 'action' => 'auth.login', 'description' => 'Kepala Bidang masuk ke sistem'],
            ['user' => $kabid, 'action' => 'website.created', 'description' => 'Menambahkan website baru untuk dipantau'],
            ['user' => $kabid, 'action' => 'scan.completed', 'description' => 'Pemindaian selesai dijalankan'],
            ['user' => $kabid, 'action' => 'report.generated', 'description' => 'Membuat laporan pemindaian'],
        ];

        foreach ($entries as $entry) {
            if (! $entry['user']) {
                continue;
            }

            ActivityLog::create([
                'user_id' => $entry['user']->id,
                'action' => $entry['action'],
                'description' => $entry['description'],
                'ip_address' => '127.0.0.1',
            ]);
        }
    }
}
