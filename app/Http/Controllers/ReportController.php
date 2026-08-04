<?php

namespace App\Http\Controllers;

use App\DataTables\ReportDataTable;
use App\Http\Requests\StoreReportRequest;
use App\Http\Requests\UpdateReportRequest;
use App\Models\Report;
use App\Models\ScanResult;
use App\Services\ReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function __construct(
        private readonly ReportService $reports,
    ) {}

    public function index(Request $request, ReportDataTable $dataTable): View|JsonResponse
    {
        Gate::authorize('viewAny', Report::class);

        if ($request->ajax()) {
            return $dataTable->ajax();
        }

        return view('reports.index');
    }

    public function create(Request $request): View
    {
        Gate::authorize('create', Report::class);

        return view('reports.create', [
            'scanResults' => $this->scanResultOptions(),
            'selectedScanResultId' => $request->integer('scan_result_id') ?: null,
        ]);
    }

    public function store(StoreReportRequest $request): RedirectResponse
    {
        $report = $this->reports->create($request->validated());

        return redirect()->route('reports.show', $report)->with('success', 'Laporan berhasil dibuat.');
    }

    public function show(Report $report): View
    {
        Gate::authorize('view', $report);

        $report->load('scanResult.website', 'scanResult.findings');

        return view('reports.show', ['report' => $report]);
    }

    public function edit(Report $report): View
    {
        Gate::authorize('update', $report);

        return view('reports.edit', [
            'report' => $report,
            'scanResults' => $this->scanResultOptions(),
        ]);
    }

    public function update(UpdateReportRequest $request, Report $report): RedirectResponse
    {
        $this->reports->update($report, $request->validated());

        return redirect()->route('reports.show', $report)->with('success', 'Laporan berhasil diperbarui.');
    }

    public function destroy(Report $report): RedirectResponse
    {
        Gate::authorize('delete', $report);

        $this->reports->delete($report);

        return redirect()->route('reports.index')->with('success', 'Laporan berhasil dihapus.');
    }

    public function pdf(Report $report): Response
    {
        Gate::authorize('print', $report);

        if (! $report->pdf_path || ! Storage::exists($report->pdf_path)) {
            $this->reports->generatePdf($report);
        }

        return response(Storage::get($report->pdf_path), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.$report->report_number.'.pdf"',
        ]);
    }

    private function scanResultOptions()
    {
        return ScanResult::query()
            ->with('website')
            ->latest('scan_date')
            ->get();
    }
}
