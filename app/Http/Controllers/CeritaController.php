<?php
namespace App\Http\Controllers;
use App\Models\Cerita;
use App\Models\Kategori;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
class CeritaController extends Controller
{
    public function index(Request $request)
    {
        $query = Cerita::with('kategori')->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status === '1');
        }
        if ($request->filled('recomendation')) {
            $query->where('recomendation', $request->recomendation === '1');
        }
        if ($request->filled('wajib_dibaca')) {
            $query->where('wajib_dibaca', $request->wajib_dibaca === '1');
        }

        $ceritas = $query->paginate(10)->withQueryString();
        return view('content.cerita.index', compact('ceritas'));
    }
    public function create()
    {
        $kategoris = Kategori::orderBy('default_title')->get();
        return view('content.cerita.create', compact('kategoris'));
    }
    public function store(Request $request)
    {
        $request->validate([
            'judul'       => 'required|string|max:255',
            'id_kategori' => 'nullable|exists:mst_kategori,id',
            'cover'       => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);
        $isiCerita = [];
        $lock = [];
        foreach ($request->input('chapters', []) as $i => $content) {
            $key = 'chapter ' . ($i + 1);
            $isiCerita[$key] = $content;
            $lock[$key]      = in_array((string)($i + 1), $request->input('locked_chapters', []));
        }
        $coverPath = null;
        if ($request->hasFile('cover')) {
            $coverPath = $request->file('cover')->store('covers', 'public');
        }
        Cerita::create([
            'judul'         => $request->judul,
            'cover'         => $coverPath,
            'id_kategori'   => $request->id_kategori,
            'status'        => $request->boolean('status'),
            'recomendation' => $request->boolean('recomendation'),
            'wajib_dibaca'  => $request->boolean('wajib_dibaca'),
            'isi_cerita'    => $isiCerita ?: null,
            'lock'          => $lock ?: null,
            'parts'         => count($isiCerita),
        ]);
        return redirect()->route('cerita.index')->with('success', 'Cerita berhasil ditambahkan.');
    }
    public function show(Cerita $cerita)
    {
        return view('content.cerita.show', compact('cerita'));
    }
    public function edit(Cerita $cerita)
    {
        $kategoris = Kategori::orderBy('default_title')->get();
        return view('content.cerita.edit', compact('cerita', 'kategoris'));
    }
    public function update(Request $request, Cerita $cerita)
    {
        $request->validate([
            'judul'       => 'required|string|max:255',
            'id_kategori' => 'nullable|exists:mst_kategori,id',
            'cover'       => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);
        $isiCerita = [];
        $lock = [];
        foreach ($request->input('chapters', []) as $i => $content) {
            $key = 'chapter ' . ($i + 1);
            $isiCerita[$key] = $content;
            $lock[$key]      = in_array((string)($i + 1), $request->input('locked_chapters', []));
        }
        $data = [
            'judul'         => $request->judul,
            'id_kategori'   => $request->id_kategori,
            'status'        => $request->boolean('status'),
            'recomendation' => $request->boolean('recomendation'),
            'wajib_dibaca'  => $request->boolean('wajib_dibaca'),
            'isi_cerita'    => $isiCerita ?: null,
            'lock'          => $lock ?: null,
            'parts'         => count($isiCerita),
        ];
        if ($request->hasFile('cover')) {
            if ($cerita->cover) {
                Storage::disk('public')->delete($cerita->cover);
            }
            $data['cover'] = $request->file('cover')->store('covers', 'public');
        }
        $cerita->update($data);
        return redirect()->route('cerita.index')->with('success', 'Cerita berhasil diperbarui.');
    }
    public function destroy(Cerita $cerita)
    {
        if ($cerita->cover) {
            Storage::disk('public')->delete($cerita->cover);
        }
        $cerita->delete();
        return redirect()->route('cerita.index')->with('success', 'Cerita berhasil dihapus.');
    }
}
