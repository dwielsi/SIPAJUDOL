<?php

namespace App\Http\Controllers;

use App\Models\Website;
use App\Services\Spk\SawPriorityService;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class SpkController extends Controller
{
    public function index(SawPriorityService $sawPriorityService): View
    {
        Gate::authorize('spk.view');

        $websites = Website::query()
            ->with(['scanResults' => fn ($query) => $query->latest('scan_date')->limit(1)])
            ->orderBy('website_name')
            ->get();

        return view('spk.index', [
            'ranked' => $sawPriorityService->rank($websites),
            'unscanned' => $sawPriorityService->unscanned($websites),
            'criteria' => config('spk.criteria'),
        ]);
    }
}
