<?php

namespace App\Http\Controllers;

use App\DataTables\ActivityLogDataTable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class ActivityLogController extends Controller
{
    public function index(Request $request, ActivityLogDataTable $dataTable): View|JsonResponse
    {
        Gate::authorize('activity_logs.viewAny');

        if ($request->ajax()) {
            return $dataTable->ajax();
        }

        return view('activity-logs.index');
    }
}
