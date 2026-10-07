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

        $scanResult = ScanWebsiteJob::start($website);

        return redirect()->route('scan-results.show', array_filter([$scanResult, 'from' => $request->input('from'), 'origin' => $request->input('origin')]))
            ->with('success', "Pemindaian {$website->website_name} telah dimulai.");
    }

    public function scanAll(): RedirectResponse
    {
        Gate::authorize('create', ScanResult::class);

        ScanResult::expireStale();

        // Website yang masih punya scan aktif (belum macet) dilewati agar tidak dipindai ganda.
        $websites = Website::query()
            ->whereDoesntHave('scanResults', fn ($query) => $query->inProgress())
            ->get();

        foreach ($websites as $website) {
            ScanWebsiteJob::start($website);
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
