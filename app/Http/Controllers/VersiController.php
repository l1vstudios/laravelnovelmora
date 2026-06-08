<?php

namespace App\Http\Controllers;

use App\Models\Versi;
use Illuminate\Http\Request;

class VersiController extends Controller
{
    public function index()
    {
        $versis = Versi::latest()->paginate(10);
        return view('content.versi.index', compact('versis'));
    }

    public function create()
    {
        return view('content.versi.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'version_name' => 'required|string|max:50',
            'version_code' => 'required|integer|min:1',
        ]);

        Versi::create($request->only('version_name', 'version_code'));

        return redirect()->route('versi.index')->with('success', 'Versi berhasil ditambahkan.');
    }

    public function show(Versi $versi)
    {
        return view('content.versi.show', compact('versi'));
    }

    public function edit(Versi $versi)
    {
        return view('content.versi.edit', compact('versi'));
    }

    public function update(Request $request, Versi $versi)
    {
        $request->validate([
            'version_name' => 'required|string|max:50',
            'version_code' => 'required|integer|min:1',
        ]);

        $versi->update($request->only('version_name', 'version_code'));

        return redirect()->route('versi.index')->with('success', 'Versi berhasil diperbarui.');
    }

    public function destroy(Versi $versi)
    {
        $versi->delete();
        return redirect()->route('versi.index')->with('success', 'Versi berhasil dihapus.');
    }
}
