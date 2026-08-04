<?php

namespace App\DataTables;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Yajra\DataTables\Facades\DataTables;

class ActivityLogDataTable
{
    public function query(): Builder
    {
        return ActivityLog::query()->with('user')->latest();
    }

    public function ajax(): JsonResponse
    {
        return DataTables::eloquent($this->query())
            ->addColumn('user_name', fn (ActivityLog $log) => $log->user?->name ?? 'Sistem')
            ->addColumn('created_at_label', fn (ActivityLog $log) => $log->created_at->translatedFormat('d M Y H:i'))
            ->make(true);
    }
}
