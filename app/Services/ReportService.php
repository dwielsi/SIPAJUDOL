<?php

namespace App\Services;

use App\Models\Report;
use App\Models\Setting;
use App\Repositories\Contracts\ReportRepositoryInterface;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class ReportService
{
    public function __construct(
        private readonly ReportRepositoryInterface $reports,
    ) {}

    public function create(array $data): Report
    {
        $data['report_number'] = $this->generateReportNumber();
        $data['status'] = $data['status'] ?? 'draft';

        $report = $this->reports->create($data);

        $this->generatePdf($report);

        return $report;
    }

    public function update(Report $report, array $data): Report
    {
        $report = $this->reports->update($report, $data);

        $this->generatePdf($report);

        return $report;
    }

    public function delete(Report $report): bool
    {
        if ($report->pdf_path) {
            Storage::delete($report->pdf_path);
        }

        return $this->reports->delete($report);
    }

    public function generatePdf(Report $report): string
    {
        $report->loadMissing(['scanResult.website', 'scanResult.findings']);

        $pdf = Pdf::loadView('reports.pdf', [
            'report' => $report,
            'setting' => Setting::first(),
        ])->setPaper('a4');

        $path = "reports/report-{$report->id}.pdf";

        Storage::put($path, $pdf->output());

        $report->forceFill(['pdf_path' => $path])->saveQuietly();

        return $path;
    }

    private function generateReportNumber(): string
    {
        $year = now()->year;
        $sequence = Report::withTrashed()->whereYear('created_at', $year)->count() + 1;

        $romanMonths = ['I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII'];

        return sprintf('%03d/LAP-JUDOL/%s/%d', $sequence, $romanMonths[now()->month - 1], $year);
    }
}
