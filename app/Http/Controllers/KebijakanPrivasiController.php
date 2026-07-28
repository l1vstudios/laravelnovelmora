<?php

namespace App\Http\Controllers;

use App\Models\KebijakanPrivasi;
use Illuminate\Http\Request;

class KebijakanPrivasiController extends Controller
{
    public function index()
    {
        $kebijakanPrivasis = KebijakanPrivasi::latest()->paginate(10);

        return view('content.kebijakan-privasi.index', compact('kebijakanPrivasis'));
    }

    public function create()
    {
        return view('content.kebijakan-privasi.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nama' => 'required|string|max:255',
            'isi_konten' => 'required|string',
        ]);

        KebijakanPrivasi::create($data);

        return redirect()->route('kebijakan-privasi.index')->with('success', 'Kebijakan privasi berhasil ditambahkan.');
    }

    public function show(KebijakanPrivasi $kebijakanPrivasi)
    {
        return view('content.kebijakan-privasi.show', compact('kebijakanPrivasi'));
    }

    public function edit(KebijakanPrivasi $kebijakanPrivasi)
    {
        return view('content.kebijakan-privasi.edit', compact('kebijakanPrivasi'));
    }

    public function update(Request $request, KebijakanPrivasi $kebijakanPrivasi)
    {
        $data = $request->validate([
            'nama' => 'required|string|max:255',
            'isi_konten' => 'required|string',
        ]);

        $kebijakanPrivasi->update($data);

        return redirect()->route('kebijakan-privasi.index')->with('success', 'Kebijakan privasi berhasil diperbarui.');
    }

    public function destroy(KebijakanPrivasi $kebijakanPrivasi)
    {
        $kebijakanPrivasi->delete();

        return redirect()->route('kebijakan-privasi.index')->with('success', 'Kebijakan privasi berhasil dihapus.');
    }
}
