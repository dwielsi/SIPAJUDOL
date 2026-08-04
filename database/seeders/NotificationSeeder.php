<?php

namespace Database\Seeders;

use App\Models\Notification;
use Illuminate\Database\Seeder;

class NotificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $notifications = [
            ['title' => 'Website Terindikasi', 'message' => 'dinsos.kuburayakab.go.id terindikasi ancaman dengan skor risiko 87/100.', 'type' => 'threat'],
            ['title' => 'Website Perlu Pemeriksaan', 'message' => 'disdikbud.kuburayakab.go.id memerlukan pemeriksaan lebih lanjut (skor risiko 42/100).', 'type' => 'warning'],
            ['title' => 'Scan Berhasil', 'message' => 'Pemindaian rutin harian telah selesai dijalankan.', 'type' => 'success'],
        ];

        foreach ($notifications as $notification) {
            Notification::create($notification);
        }
    }
}
