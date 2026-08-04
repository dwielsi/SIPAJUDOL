<?php

namespace App\Http\Controllers;

use App\Models\ScanResult;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;

class ScanProgressController extends Controller
{
    public function status(ScanResult $scanResult): JsonResponse
    {
        Gate::authorize('view', $scanResult);

        return response()->json([
            'scan_state' => $scanResult->scan_state,
            'progress_percent' => $scanResult->progress_percent,
            'current_step' => $scanResult->current_step,
            'risk_score' => $scanResult->risk_score,
            'status' => $scanResult->status,
        ]);
    }
}
