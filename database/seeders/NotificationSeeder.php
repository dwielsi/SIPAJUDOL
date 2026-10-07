<?php

namespace Database\Seeders;

use App\Models\Notification;
use App\Models\Website;
use Illuminate\Database\Seeder;

class NotificationSeeder extends Seeder
{
    /**
     * Buat notifikasi dummy berdasarkan website dan hasil pemindaian yang ada.
     */
    public function run(): void
    {
        Notification::query()->delete();

        foreach (Website::with(['scanResults' => fn ($query) => $query->latest()])->get() as $website) {
            // Satu hasil pemindaian per hari, tiga hari terakhir.
            $scans = $website->scanResults
                ->unique(fn ($scan) => $scan->created_at->toDateString())
                ->take(3)
                ->reverse()
                ->values();

            foreach ($scans as $index => $scan) {
                $isLatest = $index === $scans->count() - 1;

                $this->notify(
                    'Scan Berhasil',
                    "Pemindaian {$website->name} selesai dengan skor risiko {$scan->risk_score}/100.",
                    'success',
                    $scan->created_at,
                    ! $isLatest,
                );

                match ($scan->status) {
                    'flagged' => $this->notify(
                        'Website Terindikasi',
                        "{$website->name} ({$website->domain}) terindikasi memuat konten ilegal dengan skor risiko {$scan->risk_score}/100.",
                        'threat',
                        $scan->created_at->copy()->addSecond(),
                        ! $isLatest,
                    ),
                    'needs_review' => $this->notify(
                        'Website Perlu Pemeriksaan',
                        "{$website->name} ({$website->domain}) memerlukan pemeriksaan lebih lanjut (skor risiko {$scan->risk_score}/100).",
                        'warning',
                        $scan->created_at->copy()->addSecond(),
                        ! $isLatest,
                    ),
                    default => null,
                };
            }

            if ($website->status === 'scan_failed') {
                $this->notify(
                    'Scan Gagal',
                    "Pemindaian {$website->name} ({$website->domain}) gagal: ".($website->scan_failure_reason ?? 'website tidak dapat dijangkau.'),
                    'warning',
                    $website->last_scanned_at ?? now(),
                    false,
                );
            }
        }
    }

    private function notify(string $title, string $message, string $type, $createdAt, bool $isRead): void
    {
        $notification = new Notification([
            'title' => $title,
            'message' => $message,
            'type' => $type,
            'is_read' => $isRead,
        ]);
        $notification->created_at = $createdAt;
        $notification->updated_at = $createdAt;
        $notification->save();
    }
}
