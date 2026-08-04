<?php

namespace App\Observers;

use App\Models\ActivityLog;
use App\Models\Report;

class ReportObserver
{
    public function created(Report $report): void
    {
        $this->log('report.created', $report, "Membuat laporan {$report->report_number}");
    }

    public function updated(Report $report): void
    {
        $this->log('report.updated', $report, "Memperbarui laporan {$report->report_number}");
    }

    public function deleted(Report $report): void
    {
        $this->log('report.deleted', $report, "Menghapus laporan {$report->report_number}");
    }

    private function log(string $action, Report $report, string $description): void
    {
        if (app()->runningInConsole()) {
            return;
        }

        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => $action,
            'subject_type' => Report::class,
            'subject_id' => $report->id,
            'description' => $description,
            'ip_address' => request()->ip(),
        ]);
    }
}
