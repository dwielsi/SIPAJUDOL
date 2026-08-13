<?php

namespace App\Http\Controllers;

use App\DataTables\ScanResultDataTable;
use App\Http\Requests\StoreScanResultRequest;
use App\Jobs\ScanWebsiteJob;
use App\Models\ScanResult;
use App\Models\Website;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class ScanResultController extends Controller
{
    public function index(Request $request, ScanResultDataTable $dataTable): View|JsonResponse
    {
        Gate::authorize('viewAny', ScanResult::class);

        if ($request->ajax()) {
            return $dataTable->ajax($request);
        }

        return view('scan-results.index');
    }

    public function store(StoreScanResultRequest $request): RedirectResponse
    {
        $website = Website::findOrFail($request->validated('website_id'));

        $scanResult = ScanResult::create([
            'website_id' => $website->id,
            'scan_date' => now(),
            'scan_state' => 'queued',
        ]);

        ScanWebsiteJob::dispatch($website, $scanResult);

        return redirect()->route('scan-results.show', $scanResult)
            ->with('success', "Pemindaian {$website->website_name} telah dimulai.");
    }

    public function scanAll(): RedirectResponse
    {
        Gate::authorize('create', ScanResult::class);

        $websites = Website::query()
            ->with(['scanResults' => fn ($query) => $query->latest('scan_date')->limit(1)])
            ->get()
            ->reject(function (Website $website) {
                $latest = $website->scanResults->first();

                return $latest && in_array($latest->scan_state, ['queued', 'running'], true);
            });

        foreach ($websites as $website) {
            $scanResult = ScanResult::create([
                'website_id' => $website->id,
                'scan_date' => now(),
                'scan_state' => 'queued',
            ]);

            ScanWebsiteJob::dispatch($website, $scanResult);
        }

        $skipped = Website::count() - $websites->count();
        $message = "Memulai pemindaian untuk {$websites->count()} website.";

        if ($skipped > 0) {
            $message .= " {$skipped} website dilewati karena sedang dipindai.";
        }

        return redirect()->route('websites.index')->with('success', $message);
    }

    public function show(ScanResult $scanResult): View
    {
        Gate::authorize('view', $scanResult);

        $scanResult->load(['website', 'findings' => fn ($query) => $query->latest()]);

        return view('scan-results.show', ['scanResult' => $scanResult]);
    }

    public function destroy(ScanResult $scanResult): RedirectResponse
    {
        Gate::authorize('delete', $scanResult);

        $scanResult->delete();

        return redirect()->route('scan-results.index')->with('success', 'Riwayat pemindaian berhasil dihapus.');
    }
}
