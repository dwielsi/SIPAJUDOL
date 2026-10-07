<?php

namespace App\Http\Controllers;

use App\DataTables\WebsiteDataTable;
use App\Http\Requests\StoreWebsiteRequest;
use App\Http\Requests\UpdateWebsiteRequest;
use App\Jobs\ScanWebsiteJob;
use App\Models\ScanResult;
use App\Models\Website;
use App\Services\WebsiteService;
use Illuminate\Database\Eloquent\Builder;
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

        $search = trim((string) $request->query('q', ''));

        $monitoredWebsites = auth()->user()->can('monitoring.view')
            ? Website::query()
                ->withScanningState()
                ->with(['scanResults' => fn ($query) => $query->latest('scan_date')->orderByDesc('id')->limit(1)])
                ->when($search !== '', fn (Builder $query) => $query->where(function (Builder $sub) use ($search) {
                    $sub->where('website_name', 'like', "%{$search}%")
                        ->orWhere('domain', 'like', "%{$search}%")
                        ->orWhere('opd_name', 'like', "%{$search}%");
                }))
                ->orderBy('website_name')
                ->get()
            : collect();

        $scannableWebsites = Gate::allows('create', ScanResult::class)
            ? Website::orderBy('website_name')->get()
            : collect();

        return view('websites.index', [
            'monitoredWebsites' => $monitoredWebsites,
            'scannableWebsites' => $scannableWebsites,
            'monitoringSearch' => $search,
        ]);
    }

    public function create(): View
    {
        Gate::authorize('create', Website::class);

        return view('websites.create');
    }

    public function store(StoreWebsiteRequest $request): RedirectResponse
    {
        $website = $this->websites->create($request->validated());

        $scanResult = ScanWebsiteJob::start($website);

        return redirect()->route('scan-results.show', $scanResult)
            ->with('success', "Website {$website->domain} berhasil ditambahkan. Analisis otomatis sedang berjalan untuk memeriksa indikasi konten ilegal.");
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

        $redirect = $request->query('from') === 'website'
            ? redirect()->route('websites.show', array_filter([$website, 'from' => $request->query('origin') === 'monitoring' ? 'monitoring' : null]))
            : redirect()->route('websites.index', ['tab' => 'daftar']);

        return $redirect->with('success', 'Website berhasil diperbarui.');
    }

    public function destroy(Website $website): RedirectResponse
    {
        Gate::authorize('delete', $website);

        $this->websites->delete($website);

        return redirect()->route('websites.index')->with('success', 'Website berhasil dihapus.');
    }
}
