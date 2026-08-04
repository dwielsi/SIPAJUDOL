<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreKeywordRequest;
use App\Http\Requests\UpdateKeywordRequest;
use App\Models\Keyword;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class KeywordController extends Controller
{
    public function index(): View
    {
        Gate::authorize('viewAny', Keyword::class);

        return view('keywords.index', [
            'keywords' => Keyword::orderBy('category')->orderBy('keyword')->paginate(20),
        ]);
    }

    public function store(StoreKeywordRequest $request): RedirectResponse
    {
        Keyword::create($request->validated());

        return redirect()->route('keywords.index')->with('success', 'Kata kunci berhasil ditambahkan.');
    }

    public function update(UpdateKeywordRequest $request, Keyword $keyword): RedirectResponse
    {
        $keyword->update($request->validated());

        return redirect()->route('keywords.index')->with('success', 'Kata kunci berhasil diperbarui.');
    }

    public function destroy(Keyword $keyword): RedirectResponse
    {
        Gate::authorize('delete', $keyword);

        $keyword->delete();

        return redirect()->route('keywords.index')->with('success', 'Kata kunci berhasil dihapus.');
    }
}
