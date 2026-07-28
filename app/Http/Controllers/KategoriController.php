<?php

namespace App\Http\Controllers;

use App\Models\Kategori;
use Illuminate\Http\Request;

class KategoriController extends Controller
{
    public function index(Request $request)
    {
        $sort = $request->input('sort');

        $query = Kategori::withCount('ceritas');

        if ($sort === 'terbanyak') {
            $query->orderByDesc('ceritas_count');
        } elseif ($sort === 'terkecil') {
            $query->orderBy('ceritas_count');
        } else {
            $query->latest();
        }

        $kategoris = $query->paginate(10)->withQueryString();

        return view('content.kategori.index', compact('kategoris'));
    }

    public function create()
    {
        return view('content.kategori.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'default_title' => 'required|string|max:255|unique:mst_kategori,default_title',
            'has_popup' => 'nullable|boolean',
        ]);

        $data['has_popup'] = $request->boolean('has_popup');
        Kategori::create($data);

        return redirect()->route('kategori.index')->with('success', 'Kategori berhasil ditambahkan.');
    }

    public function show(Kategori $kategori)
    {
        $kategori->load('ceritas');

        return view('content.kategori.show', compact('kategori'));
    }

    public function edit(Kategori $kategori)
    {
        return view('content.kategori.edit', compact('kategori'));
    }

    public function update(Request $request, Kategori $kategori)
    {
        $data = $request->validate([
            'default_title' => 'required|string|max:255|unique:mst_kategori,default_title,'.$kategori->id,
            'has_popup' => 'nullable|boolean',
        ]);

        $data['has_popup'] = $request->boolean('has_popup');
        $kategori->update($data);

        return redirect()->route('kategori.index')->with('success', 'Kategori berhasil diperbarui.');
    }

    public function destroy(Kategori $kategori)
    {
        $kategori->delete();

        return redirect()->route('kategori.index')->with('success', 'Kategori berhasil dihapus.');
    }
}
