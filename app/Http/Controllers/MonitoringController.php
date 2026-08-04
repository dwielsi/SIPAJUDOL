<?php

namespace App\Http\Controllers;

use App\Models\Website;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class MonitoringController extends Controller
{
    public function index(): View
    {
        Gate::authorize('monitoring.view');

        $websites = Website::query()
            ->with(['scanResults' => fn ($query) => $query->latest('scan_date')->limit(1)])
            ->orderBy('website_name')
            ->get();

        return view('monitoring.index', ['websites' => $websites]);
    }
}
