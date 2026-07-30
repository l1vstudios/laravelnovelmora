<?php

namespace App\Http\Controllers;

use App\Models\FiturStore;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class FiturStoreController extends Controller
{
    public function index(Request $request)
    {
        $query = FiturStore::query();

        if ($request->filled('status')) {
            $query->where('status', $request->status === '1');
        }

        $this->applyGridSort($query, $request, FiturStore::class);

        $fiturStores = $query->paginate(10)->withQueryString();

        return view('content.fitur-store.index', compact('fiturStores'));
    }

    public function create()
    {
        $fiturStore = FiturStore::query()->oldest('id')->first();
        $features = $this->featureItems($fiturStore);

        return view('content.fitur-store.create', compact('fiturStore', 'features'));
    }

    public function store(Request $request)
    {
        $data = $this->validatedData($request);
        $fiturStore = FiturStore::query()->oldest('id')->first();

        if ($fiturStore) {
            $fiturStore->update($data);

            return redirect()->route('fitur-store.index')->with('success', 'Fitur store berhasil diperbarui.');
        }

        FiturStore::create($data);

        return redirect()->route('fitur-store.index')->with('success', 'Fitur store berhasil disimpan.');
    }

    public function show(FiturStore $fiturStore)
    {
        $features = $this->featureItems($fiturStore);

        return view('content.fitur-store.show', compact('fiturStore', 'features'));
    }

    public function edit(FiturStore $fiturStore)
    {
        $features = $this->featureItems($fiturStore);

        return view('content.fitur-store.edit', compact('fiturStore', 'features'));
    }

    public function update(Request $request, FiturStore $fiturStore)
    {
        $data = $this->validatedData($request);

        $fiturStore->update($data);

        return redirect()->route('fitur-store.index')->with('success', 'Fitur store berhasil diperbarui.');
    }

    public function destroy(FiturStore $fiturStore)
    {
        $fiturStore->delete();

        return redirect()->route('fitur-store.index')->with('success', 'Fitur store berhasil dihapus.');
    }

    private function validatedData(Request $request): array
    {
        $data = $request->validate([
            'features' => 'required|array|min:1',
            'features.*' => 'nullable|string|max:255',
            'status' => 'nullable|boolean',
        ]);

        $features = collect($data['features'])
            ->map(fn ($feature) => trim((string) $feature))
            ->filter()
            ->values();

        if ($features->isEmpty()) {
            throw ValidationException::withMessages([
                'features' => 'Minimal isi satu fitur.',
            ]);
        }

        return [
            'konten' => [
                'data' => $features
                    ->mapWithKeys(fn ($feature, $index) => [(string) ($index + 1) => $feature])
                    ->all(),
            ],
            'status' => $request->boolean('status'),
        ];
    }

    private function featureItems(?FiturStore $fiturStore): array
    {
        if (! $fiturStore) {
            return [''];
        }

        return $fiturStore->feature_items ?: [''];
    }
}
