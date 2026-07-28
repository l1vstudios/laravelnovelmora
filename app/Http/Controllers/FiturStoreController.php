<?php

namespace App\Http\Controllers;

use App\Models\FiturStore;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class FiturStoreController extends Controller
{
    public function index(Request $request)
    {
        $query = FiturStore::latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status === '1');
        }

        $fiturStores = $query->paginate(10)->withQueryString();

        return view('content.fitur-store.index', compact('fiturStores'));
    }

    public function create()
    {
        return view('content.fitur-store.create');
    }

    public function store(Request $request)
    {
        $data = $this->validatedData($request);

        FiturStore::create($data);

        return redirect()->route('fitur-store.index')->with('success', 'Fitur store berhasil ditambahkan.');
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
            'konten' => 'required|string',
            'status' => 'nullable|boolean',
        ]);

        $decoded = json_decode($data['konten'], true);

        if (json_last_error() !== JSON_ERROR_NONE || ! is_array($decoded)) {
            throw ValidationException::withMessages([
                'konten' => 'Konten harus berupa JSON object atau array yang valid.',
            ]);
        }

        return [
            'konten' => $decoded,
            'status' => $request->boolean('status'),
        ];
    }
}
