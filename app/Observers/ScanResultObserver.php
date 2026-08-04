<?php

namespace App\Observers;

use App\Models\ActivityLog;
use App\Models\ScanResult;

class ScanResultObserver
{
    public function created(ScanResult $scanResult): void
    {
        $this->log('scan.started', $scanResult, "Memulai pemindaian {$scanResult->website->domain}");
    }

    public function updated(ScanResult $scanResult): void
    {
        if (! $scanResult->isDirty('scan_state')) {
            return;
        }

        match ($scanResult->scan_state) {
            'completed' => $this->log(
                'scan.completed',
                $scanResult,
                "Pemindaian {$scanResult->website->domain} selesai dengan skor risiko {$scanResult->risk_score}/100",
            ),
            'failed' => $this->log(
                'scan.failed',
                $scanResult,
                "Pemindaian {$scanResult->website->domain} gagal",
            ),
            default => null,
        };
    }

    private function log(string $action, ScanResult $scanResult, string $description): void
    {
        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => $action,
            'subject_type' => ScanResult::class,
            'subject_id' => $scanResult->id,
            'description' => $description,
            'ip_address' => request()->ip(),
        ]);
    }
}
