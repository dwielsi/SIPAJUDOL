<?php

namespace App\DataTables;

use App\Models\ScanResult;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class ScanResultDataTable
{
    public function query(Request $request): Builder
    {
        return ScanResult::query()
            ->with('website')
            ->latest('scan_date')
            ->when(
                $request->query('state') === 'in_progress',
                fn (Builder $query) => $query->inProgress(),
            );
    }

    public function ajax(Request $request): JsonResponse
    {
        $user = auth()->user();

        return DataTables::eloquent($this->query($request))
            ->addColumn('website_name', fn (ScanResult $scanResult) => $scanResult->website?->website_name ?? '—')
            ->addColumn('domain', fn (ScanResult $scanResult) => $scanResult->website?->domain ?? '—')
            ->addColumn('scan_date_label', fn (ScanResult $scanResult) => $scanResult->scan_date->translatedFormat('d M Y'))
            ->addColumn('badge', fn (ScanResult $scanResult) => [
                'color' => $scanResult->riskLevelColor(),
                'label' => $scanResult->riskLevelLabel(),
            ])
            ->addColumn('state_badge', fn (ScanResult $scanResult) => [
                'color' => match ($scanResult->scan_state) {
                    'completed' => 'success',
                    'running' => 'primary',
                    'failed' => 'danger',
                    default => 'slate',
                },
                'label' => match ($scanResult->scan_state) {
                    'completed' => 'Selesai',
                    'running' => 'Berjalan',
                    'failed' => 'Gagal',
                    default => 'Antrian',
                },
            ])
            ->addColumn('actions', fn (ScanResult $scanResult) => [
                'can_delete' => $user->can('delete', $scanResult),
                'show_url' => route('scan-results.show', $scanResult),
                'destroy_url' => route('scan-results.destroy', $scanResult),
            ])
            ->rawColumns(['badge', 'state_badge', 'actions'])
            ->make(true);
    }
}
