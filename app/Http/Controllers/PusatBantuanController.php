<?php

namespace App\Http\Controllers;

use App\Models\PusatBantuan;
use Illuminate\Http\Request;

class PusatBantuanController extends Controller
{
    public function index()
    {
        $pusatBantuans = PusatBantuan::latest()->paginate(10);

        return view('content.pusat-bantuan.index', compact('pusatBantuans'));
    }

    public function create()
    {
        return view('content.pusat-bantuan.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nama_layanan' => 'required|string|max:255',
            'isi_layanan' => 'required|string',
        ]);

        PusatBantuan::create($data);

        return redirect()->route('pusat-bantuan.index')->with('success', 'Pusat bantuan berhasil ditambahkan.');
    }

    public function show(PusatBantuan $pusatBantuan)
    {
        return view('content.pusat-bantuan.show', compact('pusatBantuan'));
    }

    public function edit(PusatBantuan $pusatBantuan)
    {
        return view('content.pusat-bantuan.edit', compact('pusatBantuan'));
    }

    public function update(Request $request, PusatBantuan $pusatBantuan)
    {
        $data = $request->validate([
            'nama_layanan' => 'required|string|max:255',
            'isi_layanan' => 'required|string',
        ]);

        $pusatBantuan->update($data);

        return redirect()->route('pusat-bantuan.index')->with('success', 'Pusat bantuan berhasil diperbarui.');
    }

    public function destroy(PusatBantuan $pusatBantuan)
    {
        $pusatBantuan->delete();

        return redirect()->route('pusat-bantuan.index')->with('success', 'Pusat bantuan berhasil dihapus.');
    }
}
