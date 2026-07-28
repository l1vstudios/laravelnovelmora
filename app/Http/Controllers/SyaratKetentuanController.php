<?php

namespace App\Http\Controllers;

use App\Models\SyaratKetentuan;
use Illuminate\Http\Request;

class SyaratKetentuanController extends Controller
{
    public function index()
    {
        $syaratKetentuans = SyaratKetentuan::latest()->paginate(10);

        return view('content.syarat-ketentuan.index', compact('syaratKetentuans'));
    }

    public function create()
    {
        return view('content.syarat-ketentuan.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nama' => 'required|string|max:255',
            'isi_konten' => 'required|string',
        ]);

        SyaratKetentuan::create($data);

        return redirect()->route('syarat-ketentuan.index')->with('success', 'Syarat ketentuan berhasil ditambahkan.');
    }

    public function show(SyaratKetentuan $syaratKetentuan)
    {
        return view('content.syarat-ketentuan.show', compact('syaratKetentuan'));
    }

    public function edit(SyaratKetentuan $syaratKetentuan)
    {
        return view('content.syarat-ketentuan.edit', compact('syaratKetentuan'));
    }

    public function update(Request $request, SyaratKetentuan $syaratKetentuan)
    {
        $data = $request->validate([
            'nama' => 'required|string|max:255',
            'isi_konten' => 'required|string',
        ]);

        $syaratKetentuan->update($data);

        return redirect()->route('syarat-ketentuan.index')->with('success', 'Syarat ketentuan berhasil diperbarui.');
    }

    public function destroy(SyaratKetentuan $syaratKetentuan)
    {
        $syaratKetentuan->delete();

        return redirect()->route('syarat-ketentuan.index')->with('success', 'Syarat ketentuan berhasil dihapus.');
    }
}
