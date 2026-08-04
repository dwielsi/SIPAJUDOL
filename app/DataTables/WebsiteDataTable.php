<?php

namespace App\DataTables;

use App\Models\Website;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Yajra\DataTables\Facades\DataTables;

class WebsiteDataTable
{
    public function query(): Builder
    {
        return Website::query();
    }

    public function ajax(): JsonResponse
    {
        $user = auth()->user();

        return DataTables::eloquent($this->query())
            ->addColumn('badge', fn (Website $website) => [
                'color' => $website->statusColor(),
                'label' => $website->statusLabel(),
            ])
            ->addColumn('actions', fn (Website $website) => [
                'can_update' => $user->can('update', $website),
                'can_delete' => $user->can('delete', $website),
                'show_url' => route('websites.show', $website),
                'edit_url' => route('websites.edit', $website),
                'destroy_url' => route('websites.destroy', $website),
            ])
            ->rawColumns(['badge', 'actions'])
            ->make(true);
    }
}
