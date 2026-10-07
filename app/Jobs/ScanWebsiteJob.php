<?php

namespace App\Jobs;

use App\Models\ScanResult;
use App\Models\Website;
use App\Services\QueueWorkerService;
use App\Services\Scanner\WebsiteScannerService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Throwable;

class ScanWebsiteJob implements ShouldQueue
{
    use Queueable;

    /** Batas waktu (detik) tanpa progres sebelum scan yang belum selesai dianggap macet. */
    public const TIMEOUT_BUFFER_SECONDS = 420;

    public $tries = 1;

    public $timeout = 600;

    public $deleteWhenMissingModels = true;

    public function __construct(
        public readonly Website $website,
        public readonly ScanResult $scanResult,
    ) {}

    /**
     * Membuat hasil scan baru untuk website, menggantikan scan lama yang belum selesai,
     * lalu memasukkannya ke antrean dan memastikan ada worker yang memprosesnya.
     */
    public static function start(Website $website): ScanResult
    {
        ScanResult::query()
            ->where('website_id', $website->id)
            ->inProgress()
            ->get()
            ->each->markFailed('Digantikan oleh pemindaian yang lebih baru.');

        $scanResult = ScanResult::create([
            'website_id' => $website->id,
            'scan_date' => now(),
            'scan_state' => 'queued',
        ]);

        static::dispatch($website, $scanResult);

        app(QueueWorkerService::class)->ensureRunning();

        return $scanResult;
    }

    public function handle(WebsiteScannerService $scanner): void
    {
        // Scan yang sudah ditandai gagal/kedaluwarsa atau digantikan tidak perlu dijalankan lagi.
        if ($this->scanResult->fresh()?->scan_state !== 'queued') {
            return;
        }

        set_time_limit(0);

        $scanner->run($this->website, $this->scanResult);
    }

    public function failed(?Throwable $exception): void
    {
        $scanResult = $this->scanResult->fresh();

        if ($scanResult && in_array($scanResult->scan_state, ['queued', 'running'], true)) {
            $reason = 'Website gagal dipindai: '.($exception?->getMessage() ?: 'pemindaian melebihi batas waktu.');

            $scanResult->markFailed($reason);
            $this->website->fresh()?->markScanFailed($reason);
        }
    }
}
