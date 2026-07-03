<?php

namespace App\Http\Controllers;

use App\Models\Versi;
use Illuminate\Http\Request;

class VersiController extends Controller
{
    private const MAX_VERSION_CODE = 2147483647;

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
            'version_code' => 'required|integer|min:1|max:' . self::MAX_VERSION_CODE,
        ], [
            'version_code.max' => 'Maaf, angka yang dimasukkan terlalu besar.',
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
            'version_code' => 'required|integer|min:1|max:' . self::MAX_VERSION_CODE,
        ], [
            'version_code.max' => 'Maaf, angka yang dimasukkan terlalu besar.',
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
