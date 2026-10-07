<?php

namespace App\DataTables;

use App\Models\Report;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class ReportDataTable
{
    /** Hasil pemeriksaan yang dapat difilter, diambil dari status hasil scan laporan. */
    public const RESULTS = [
        'flagged' => 'Terindikasi',
        'needs_review' => 'Perlu Pemeriksaan',
        'safe' => 'Aman',
    ];

    public function query(Request $request): Builder
    {
        $result = $request->query('result');

        return Report::query()
            ->with('scanResult.website')
            ->when(
                is_string($result) && array_key_exists($result, self::RESULTS),
                fn (Builder $query) => $query->whereHas('scanResult', fn (Builder $scan) => $scan->where('status', $result)),
            )
            ->latest('report_date')
            ->orderByDesc('id');
    }

    public function ajax(Request $request): JsonResponse
    {
        $user = auth()->user();

        return DataTables::eloquent($this->query($request))
            ->addColumn('website_name', fn (Report $report) => $report->scanResult?->website?->website_name ?? '—')
            ->addColumn('result_badge', fn (Report $report) => [
                'color' => $report->scanResult?->riskLevelColor() ?? 'slate',
                'label' => $report->scanResult?->riskLevelLabel() ?? '—',
            ])
            ->addColumn('status_badge', fn (Report $report) => [
                'color' => match ($report->status) {
                    'final' => 'success',
                    'submitted' => 'primary',
                    default => 'slate',
                },
                'label' => match ($report->status) {
                    'final' => 'Final',
                    'submitted' => 'Diserahkan',
                    default => 'Draf',
                },
            ])
            ->addColumn('report_date_label', fn (Report $report) => $report->report_date->translatedFormat('d M Y'))
            ->addColumn('actions', fn (Report $report) => [
                'can_update' => $user->can('update', $report),
                'can_delete' => $user->can('delete', $report),
                'can_print' => $user->can('print', $report),
                'show_url' => route('reports.show', $report),
                'edit_url' => route('reports.edit', $report),
                'destroy_url' => route('reports.destroy', $report),
                'pdf_url' => route('reports.pdf', $report),
            ])
            ->rawColumns(['status_badge', 'actions'])
            ->make(true);
    }
}
