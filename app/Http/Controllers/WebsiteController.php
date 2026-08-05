<?php

namespace App\Http\Controllers;

use App\DataTables\WebsiteDataTable;
use App\Http\Requests\StoreWebsiteRequest;
use App\Http\Requests\UpdateWebsiteRequest;
use App\Jobs\ScanWebsiteJob;
use App\Models\ScanResult;
use App\Models\Website;
use App\Services\WebsiteService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class WebsiteController extends Controller
{
    public function __construct(
        private readonly WebsiteService $websites,
    ) {}

    public function index(Request $request, WebsiteDataTable $dataTable): View|JsonResponse
    {
        Gate::authorize('viewAny', Website::class);

        if ($request->ajax()) {
            return $dataTable->ajax($request);
        }

        return view('websites.index');
    }

    public function create(): View
    {
        Gate::authorize('create', Website::class);

        return view('websites.create');
    }

    public function store(StoreWebsiteRequest $request): RedirectResponse
    {
        $website = $this->websites->create($request->validated());

        $scanResult = ScanResult::create([
            'website_id' => $website->id,
            'scan_date' => now(),
            'scan_state' => 'queued',
        ]);

        ScanWebsiteJob::dispatch($website, $scanResult);

        return redirect()->route('scan-results.show', $scanResult)
            ->with('success', "Website {$website->domain} berhasil ditambahkan. Analisis otomatis sedang berjalan untuk memeriksa indikasi judi online.");
    }

    public function show(Website $website): View
    {
        Gate::authorize('view', $website);

        $website->load(['scanResults' => fn ($query) => $query->latest('scan_date')->with('reports')]);

        return view('websites.show', ['website' => $website]);
    }

    public function edit(Website $website): View
    {
        Gate::authorize('update', $website);

        return view('websites.edit', ['website' => $website]);
    }

    public function update(UpdateWebsiteRequest $request, Website $website): RedirectResponse
    {
        $this->websites->update($website, $request->validated());

        return redirect()->route('websites.index')->with('success', 'Website berhasil diperbarui.');
    }

    public function destroy(Website $website): RedirectResponse
    {
        Gate::authorize('delete', $website);

        $this->websites->delete($website);

        return redirect()->route('websites.index')->with('success', 'Website berhasil dihapus.');
    }
}
