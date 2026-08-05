<?php

namespace App\Services;

use App\Models\Report;
use App\Models\ScanResult;
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
        $data = $this->fillFromScanResult($data);

        $report = $this->reports->create($data);

        $this->generatePdf($report);

        return $report;
    }

    public function update(Report $report, array $data): Report
    {
        $data = $this->fillFromScanResult($data);

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

    private function fillFromScanResult(array $data): array
    {
        if (empty($data['scan_result_id'])) {
            return $data;
        }

        $needsSummary = empty($data['summary']);
        $needsConclusion = empty($data['conclusion']);
        $needsRecommendation = empty($data['recommendation']);

        if (! $needsSummary && ! $needsConclusion && ! $needsRecommendation) {
            return $data;
        }

        $scanResult = ScanResult::find($data['scan_result_id']);

        if (! $scanResult) {
            return $data;
        }

        if ($needsSummary && $scanResult->ai_summary) {
            $data['summary'] = $scanResult->ai_summary;
        }

        if ($needsConclusion && $scanResult->ai_conclusion) {
            $data['conclusion'] = $scanResult->ai_conclusion;
        }

        if ($needsRecommendation && $scanResult->ai_recommendation) {
            $data['recommendation'] = $scanResult->ai_recommendation;
        }

        return $data;
    }

    private function generateReportNumber(): string
    {
        $year = now()->year;
        $sequence = Report::withTrashed()->whereYear('created_at', $year)->count() + 1;

        $romanMonths = ['I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII'];

        return sprintf('%03d/LAP-JUDOL/%s/%d', $sequence, $romanMonths[now()->month - 1], $year);
    }
}
