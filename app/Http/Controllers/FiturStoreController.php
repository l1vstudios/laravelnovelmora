<?php

namespace App\Http\Controllers;

use App\Models\FiturStore;
use Illuminate\Http\Request;

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

        return view('content.fitur-store.create', compact('fiturStore'));
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
        return view('content.fitur-store.show', compact('fiturStore'));
    }

    public function edit(FiturStore $fiturStore)
    {
        return view('content.fitur-store.edit', compact('fiturStore'));
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
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'nullable|boolean',
        ]);

        return [
            'konten' => [
                'title' => $data['title'],
                'description' => $data['description'] ?? '',
            ],
            'status' => $request->boolean('status'),
        ];
    }
}
