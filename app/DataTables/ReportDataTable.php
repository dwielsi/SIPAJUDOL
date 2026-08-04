<?php

namespace App\DataTables;

use App\Models\Report;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Yajra\DataTables\Facades\DataTables;

class ReportDataTable
{
    public function query(): Builder
    {
        return Report::query()->with('scanResult.website')->latest('report_date');
    }

    public function ajax(): JsonResponse
    {
        $user = auth()->user();

        return DataTables::eloquent($this->query())
            ->addColumn('website_name', fn (Report $report) => $report->scanResult?->website?->website_name ?? '—')
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
