<?php

namespace App\Http\Controllers;

use App\Models\Notifikasi;
use Illuminate\Http\Request;

class NotifikasiController extends Controller
{
    public function index()
    {
        $notifikasis = Notifikasi::latest()->paginate(10);
        return view('content.notifikasi.index', compact('notifikasis'));
    }

    public function create()
    {
        return view('content.notifikasi.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title'   => 'required|string|max:255',
            'message' => 'required|string',
        ]);

        Notifikasi::create($request->only('title', 'message'));

        return redirect()->route('notifikasi.index')->with('success', 'Notifikasi berhasil ditambahkan.');
    }

    public function show(Notifikasi $notifikasi)
    {
        return view('content.notifikasi.show', compact('notifikasi'));
    }

    public function edit(Notifikasi $notifikasi)
    {
        return view('content.notifikasi.edit', compact('notifikasi'));
    }

    public function update(Request $request, Notifikasi $notifikasi)
    {
        $request->validate([
            'title'   => 'required|string|max:255',
            'message' => 'required|string',
        ]);

        $notifikasi->update($request->only('title', 'message'));

        return redirect()->route('notifikasi.index')->with('success', 'Notifikasi berhasil diperbarui.');
    }

    public function destroy(Notifikasi $notifikasi)
    {
        $notifikasi->delete();
        return redirect()->route('notifikasi.index')->with('success', 'Notifikasi berhasil dihapus.');
    }
}
