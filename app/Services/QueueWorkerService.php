<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Memastikan selalu ada queue worker yang memproses job pemindaian.
 *
 * Worker yang hidup mengirim "heartbeat" ke cache (lihat AppServiceProvider). Jika heartbeat
 * tidak ada saat pemindaian dibuat, worker dijalankan otomatis di latar belakang dan akan
 * berhenti sendiri ketika antrean kosong.
 */
class QueueWorkerService
{
    public const HEARTBEAT_KEY = 'queue-worker:heartbeat';

    public const HEARTBEAT_TTL_SECONDS = 15;

    private const LAUNCH_LOCK_KEY = 'queue-worker:launching';

    private const LAUNCH_LOCK_SECONDS = 20;

    public function beat(): void
    {
        Cache::put(self::HEARTBEAT_KEY, now()->timestamp, self::HEARTBEAT_TTL_SECONDS);
    }

    public function stopped(): void
    {
        Cache::forget(self::HEARTBEAT_KEY);
    }

    public function isRunning(): bool
    {
        return Cache::has(self::HEARTBEAT_KEY);
    }

    public function ensureRunning(): void
    {
        if (config('queue.default') === 'sync' || app()->runningUnitTests()) {
            return;
        }

        if ($this->isRunning() || ! Cache::add(self::LAUNCH_LOCK_KEY, true, self::LAUNCH_LOCK_SECONDS)) {
            return;
        }

        try {
            $this->launch();
        } catch (Throwable $e) {
            Log::warning("Gagal menjalankan queue worker otomatis: {$e->getMessage()}");
        }
    }

    private function launch(): void
    {
        $php = $this->phpBinary();
        $artisan = base_path('artisan');
        $log = storage_path('logs/queue-worker.log');
        $args = 'queue:work --stop-when-empty --tries=1 --sleep=1';

        if (PHP_OS_FAMILY === 'Windows') {
            pclose(popen(sprintf('start "" /B "%s" "%s" %s >> "%s" 2>&1', $php, $artisan, $args, $log), 'r'));

            return;
        }

        exec(sprintf('nohup %s %s %s >> %s 2>&1 &', escapeshellarg($php), escapeshellarg($artisan), $args, escapeshellarg($log)));
    }

    private function phpBinary(): string
    {
        if ($configured = config('queue.worker_php_binary')) {
            return $configured;
        }

        // Di bawah Apache/FPM, PHP_BINARY bisa menunjuk ke httpd/php-cgi, bukan PHP CLI.
        if (PHP_BINARY && preg_match('/php(\.exe)?$/i', PHP_BINARY)) {
            return PHP_BINARY;
        }

        return 'php';
    }
}
