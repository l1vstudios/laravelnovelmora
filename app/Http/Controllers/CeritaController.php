<?php
namespace App\Http\Controllers;
use App\Models\Ad;
use App\Models\Cerita;
use App\Models\CeritaAd;
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
        $ads = Ad::active()->orderBy('title')->get();
        return view('content.cerita.create', compact('kategoris', 'ads'));
    }
    public function store(Request $request)
    {
        $request->validate([
            'judul'                   => 'required|string|max:255',
            'sinopsis'                => 'nullable|string',
            'id_kategori'             => 'nullable|exists:mst_kategori,id',
            'cover'                   => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'ads_after_chapters'      => 'nullable|array',
            'ads_after_chapters.*'    => 'nullable|array',
            'ads_after_chapters.*.*'  => 'integer|exists:mst_ads,id',
            'chapter_titles'          => 'nullable|array',
            'chapter_titles.*'        => 'nullable|string|max:255',
            'chapters'                => 'nullable|array',
            'chapters.*'              => 'nullable|string',
        ]);
        $isiCerita = [];
        $lock = [];
        $chapterTitles = $request->input('chapter_titles', []);
        foreach ($request->input('chapters', []) as $i => $content) {
            $key = 'chapter ' . ($i + 1);
            $isiCerita[$key] = [
                'title' => $this->normalizeChapterTitle($chapterTitles[$i] ?? 'Chapter ' . ($i + 1)),
                'content' => $this->normalizeChapterContent($content),
            ];
            $lock[$key]      = in_array((string)($i + 1), $request->input('locked_chapters', []));
        }
        $coverPath = null;
        if ($request->hasFile('cover')) {
            $coverPath = $request->file('cover')->store('covers', 'public');
        }
        $cerita = Cerita::create([
            'judul'         => $request->judul,
            'sinopsis'      => $request->sinopsis,
            'cover'         => $coverPath,
            'id_kategori'   => $request->id_kategori,
            'status'        => $request->boolean('status'),
            'recomendation' => $request->boolean('recomendation'),
            'wajib_dibaca'  => $request->boolean('wajib_dibaca'),
            'isi_cerita'    => $isiCerita ?: null,
            'lock'          => $lock ?: null,
            'parts'         => count($isiCerita),
        ]);

        $this->syncAdPlacements($cerita, $request, count($isiCerita));

        return redirect()->route('cerita.index')->with('success', 'Cerita berhasil ditambahkan.');
    }
    public function show(Cerita $cerita)
    {
        $cerita->load('adPlacements.ad');
        return view('content.cerita.show', compact('cerita'));
    }
    public function edit(Cerita $cerita)
    {
        $kategoris = Kategori::orderBy('default_title')->get();
        $cerita->load('adPlacements');
        $selectedAdIds = $cerita->adPlacements->pluck('ad_id')->unique();
        $ads = Ad::where('status', true)
            ->orWhereIn('id', $selectedAdIds)
            ->orderBy('title')
            ->get();
        return view('content.cerita.edit', compact('cerita', 'kategoris', 'ads'));
    }
    public function update(Request $request, Cerita $cerita)
    {
        $request->validate([
            'judul'                   => 'required|string|max:255',
            'sinopsis'                => 'nullable|string',
            'id_kategori'             => 'nullable|exists:mst_kategori,id',
            'cover'                   => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'ads_after_chapters'      => 'nullable|array',
            'ads_after_chapters.*'    => 'nullable|array',
            'ads_after_chapters.*.*'  => 'integer|exists:mst_ads,id',
            'chapter_titles'          => 'nullable|array',
            'chapter_titles.*'        => 'nullable|string|max:255',
            'chapters'                => 'nullable|array',
            'chapters.*'              => 'nullable|string',
        ]);
        $isiCerita = [];
        $lock = [];
        $chapterTitles = $request->input('chapter_titles', []);
        foreach ($request->input('chapters', []) as $i => $content) {
            $key = 'chapter ' . ($i + 1);
            $isiCerita[$key] = [
                'title' => $this->normalizeChapterTitle($chapterTitles[$i] ?? 'Chapter ' . ($i + 1)),
                'content' => $this->normalizeChapterContent($content),
            ];
            $lock[$key]      = in_array((string)($i + 1), $request->input('locked_chapters', []));
        }
        $data = [
            'judul'         => $request->judul,
            'sinopsis'      => $request->sinopsis,
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
        $this->syncAdPlacements($cerita, $request, count($isiCerita));
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

    private function syncAdPlacements(Cerita $cerita, Request $request, int $chapterTotal): void
    {
        $cerita->adPlacements()->delete();

        $placements = [];
        $now = now();

        foreach ($request->input('ads_after_chapters', []) as $chapter => $adIds) {
            $chapterNumber = (int) $chapter;

            if ($chapterNumber < 1 || $chapterNumber > $chapterTotal || !is_array($adIds)) {
                continue;
            }

            foreach (array_unique($adIds) as $adId) {
                $placements[] = [
                    'cerita_id'      => $cerita->id,
                    'ad_id'          => (int) $adId,
                    'after_chapter'  => $chapterNumber,
                    'created_at'     => $now,
                    'updated_at'     => $now,
                ];
            }
        }

        if ($placements) {
            CeritaAd::insert($placements);
        }
    }

    private function normalizeChapterContent(?string $content): string
    {
        $content = str_replace(["\r\n", "\r"], "\n", $content ?? '');
        $content = str_replace("\xc2\xa0", ' ', $content);
        $content = str_replace('--', '', $content);
        $content = preg_replace('/[ \t]+/u', ' ', $content) ?? $content;

        $lines = array_map(static fn ($line) => trim($line), explode("\n", $content));
        $content = implode("\n", $lines);
        $content = preg_replace("/\n{3,}/", "\n\n", $content) ?? $content;

        return trim($content);
    }

    private function normalizeChapterTitle(?string $title): string
    {
        $title = str_replace("\xc2\xa0", ' ', $title ?? '');
        $title = str_replace('--', '', $title);
        $title = preg_replace('/\s+/u', ' ', $title) ?? $title;

        return trim($title);
    }
}
