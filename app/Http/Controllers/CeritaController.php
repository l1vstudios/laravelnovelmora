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
    private const MAX_UNSIGNED_INTEGER = 4294967295;

    public function index(Request $request)
    {
        if ($request->boolean('story_options')) {
            return $this->storyOptionsResponse($request);
        }

        $query = Cerita::query()
            ->select([
                'id',
                'judul',
                'cover',
                'id_kategori',
                'positions_index',
                'parts',
                'total_read',
                'total_vote',
                'status',
                'recomendation',
                'wajib_dibaca',
                'created_at',
            ])
            ->with('kategori:id,default_title');

        if ($request->filled('status')) {
            $query->where('status', $request->status === '1');
        }
        if ($request->filled('judul')) {
            $judul = mb_strtolower(trim($request->judul));
            $query->whereRaw('LOWER(judul) LIKE ?', ["%{$judul}%"]);
        }
        if ($request->filled('kategori_id')) {
            $query->where('id_kategori', $request->kategori_id);
        }
        if ($request->filled('recomendation')) {
            $query->where('recomendation', $request->recomendation === '1');
        }
        if ($request->filled('wajib_dibaca')) {
            $query->where('wajib_dibaca', $request->wajib_dibaca === '1');
        }

        $this->applyGridSort($query, $request, Cerita::class, 'created_at', 'desc', [
            'kategori' => 'id_kategori',
            'index' => 'positions_index',
            'read' => 'total_read',
            'vote' => 'total_vote',
            'rekomendasi' => 'recomendation',
            'wajib_baca' => 'wajib_dibaca',
        ]);

        $ceritas = $query->paginate(10)->withQueryString();
        $selectedCeritas = $this->selectedCeritaOptions($request->old('cerita_ids', []));
        $initialCeritas = $this->initialCeritaOptions($selectedCeritas->pluck('id')->all());
        $storyPickerTotal = Cerita::query()->count();
        $storyPickerLastPage = max(1, (int) ceil($storyPickerTotal / 5));
        $lockPickerCeritas = $request->old('form_type') === 'global-lock'
            ? $selectedCeritas->concat($initialCeritas)->unique('id')->values()
            : $initialCeritas;
        $bulkPickerCeritas = $request->old('form_type') === 'bulk-pilihan'
            ? $selectedCeritas->concat($initialCeritas)->unique('id')->values()
            : $initialCeritas;
        $hasCeritas = $storyPickerTotal > 0;
        $kategoris = Kategori::orderBy('default_title')->get(['id', 'default_title']);

        return view('content.cerita.index', compact('ceritas', 'lockPickerCeritas', 'bulkPickerCeritas', 'storyPickerLastPage', 'hasCeritas', 'kategoris'));
    }

    public function options(Request $request)
    {
        return $this->storyOptionsResponse($request);
    }

    private function storyOptionsResponse(Request $request)
    {
        $request->validate([
            'q' => 'nullable|string|max:255',
            'page' => 'nullable|integer|min:1',
            'per_page' => 'nullable|integer|min:1|max:5',
        ]);

        $query = Cerita::query()
            ->select(['id', 'judul', 'parts'])
            ->orderBy('judul');

        if ($request->filled('q')) {
            $judul = mb_strtolower(trim($request->q));
            $query->whereRaw('LOWER(judul) LIKE ?', ["%{$judul}%"]);
        }

        $ceritas = $query->paginate(
            $request->integer('per_page', 5),
            ['*'],
            'page',
            $request->integer('page', 1)
        );

        return response()->json([
            'data' => $ceritas->items(),
            'meta' => [
                'current_page' => $ceritas->currentPage(),
                'last_page' => $ceritas->lastPage(),
                'per_page' => $ceritas->perPage(),
                'total' => $ceritas->total(),
            ],
        ]);
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
            'judul' => 'required|string|max:255',
            'sinopsis' => 'nullable|string',
            'id_kategori' => 'nullable|exists:mst_kategori,id',
            'positions_index' => 'nullable|integer',
            'cover' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'ad_placements' => 'nullable|array',
            'ad_placements.*.ad_id' => 'nullable|integer|exists:mst_ads,id',
            'ad_placements.*.position' => 'nullable|in:before,after',
            'ad_placements.*.chapter' => 'nullable|integer|min:1|max:'.self::MAX_UNSIGNED_INTEGER,
            'ads_after_chapters' => 'nullable|array',
            'ads_after_chapters.*' => 'nullable|array',
            'ads_after_chapters.*.*' => 'integer|exists:mst_ads,id',
            'ads_before_chapters' => 'nullable|array',
            'ads_before_chapters.*' => 'nullable|array',
            'ads_before_chapters.*.*' => 'integer|exists:mst_ads,id',
            'chapter_titles' => 'nullable|array',
            'chapter_titles.*' => 'nullable|string|max:255',
            'chapters' => 'nullable|array',
            'chapters.*' => 'nullable|string',
        ]);
        $isiCerita = [];
        $lock = [];
        $chapterTitles = $request->input('chapter_titles', []);
        foreach ($request->input('chapters', []) as $i => $content) {
            $key = 'chapter '.($i + 1);
            $isiCerita[$key] = [
                'title' => $this->normalizeChapterTitle($chapterTitles[$i] ?? 'Chapter '.($i + 1)),
                'content' => $this->normalizeChapterContent($content),
            ];
            $lock[$key] = in_array((string) ($i + 1), $request->input('locked_chapters', []));
        }
        $coverPath = null;
        if ($request->hasFile('cover')) {
            $coverPath = $request->file('cover')->store('covers', 'public');
        }
        $cerita = Cerita::create([
            'judul' => $request->judul,
            'sinopsis' => $request->sinopsis,
            'cover' => $coverPath,
            'id_kategori' => $request->id_kategori,
            'positions_index' => $request->integer('positions_index', 0),
            'status' => $request->boolean('status'),
            'recomendation' => $request->boolean('recomendation'),
            'wajib_dibaca' => $request->boolean('wajib_dibaca'),
            'isi_cerita' => $isiCerita ?: null,
            'lock' => $lock ?: null,
            'parts' => count($isiCerita),
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
            'judul' => 'required|string|max:255',
            'sinopsis' => 'nullable|string',
            'id_kategori' => 'nullable|exists:mst_kategori,id',
            'positions_index' => 'nullable|integer',
            'cover' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'ad_placements' => 'nullable|array',
            'ad_placements.*.ad_id' => 'nullable|integer|exists:mst_ads,id',
            'ad_placements.*.position' => 'nullable|in:before,after',
            'ad_placements.*.chapter' => 'nullable|integer|min:1|max:'.self::MAX_UNSIGNED_INTEGER,
            'ads_after_chapters' => 'nullable|array',
            'ads_after_chapters.*' => 'nullable|array',
            'ads_after_chapters.*.*' => 'integer|exists:mst_ads,id',
            'ads_before_chapters' => 'nullable|array',
            'ads_before_chapters.*' => 'nullable|array',
            'ads_before_chapters.*.*' => 'integer|exists:mst_ads,id',
            'chapter_titles' => 'nullable|array',
            'chapter_titles.*' => 'nullable|string|max:255',
            'chapters' => 'nullable|array',
            'chapters.*' => 'nullable|string',
        ]);
        $isiCerita = [];
        $lock = [];
        $chapterTitles = $request->input('chapter_titles', []);
        foreach ($request->input('chapters', []) as $i => $content) {
            $key = 'chapter '.($i + 1);
            $isiCerita[$key] = [
                'title' => $this->normalizeChapterTitle($chapterTitles[$i] ?? 'Chapter '.($i + 1)),
                'content' => $this->normalizeChapterContent($content),
            ];
            $lock[$key] = in_array((string) ($i + 1), $request->input('locked_chapters', []));
        }
        $data = [
            'judul' => $request->judul,
            'sinopsis' => $request->sinopsis,
            'id_kategori' => $request->id_kategori,
            'positions_index' => $request->integer('positions_index', 0),
            'status' => $request->boolean('status'),
            'recomendation' => $request->boolean('recomendation'),
            'wajib_dibaca' => $request->boolean('wajib_dibaca'),
            'isi_cerita' => $isiCerita ?: null,
            'lock' => $lock ?: null,
            'parts' => count($isiCerita),
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

    public function globalLock(Request $request)
    {
        $data = $request->validate([
            'lock_scope' => 'required|in:all,selected',
            'form_type' => 'nullable|string',
            'lock_action' => 'required|in:lock,unlock',
            'cerita_ids' => 'required_if:lock_scope,selected|array',
            'cerita_ids.*' => 'integer|exists:mst_cerita,id',
            'chapter_start' => 'required|integer|min:1|max:'.self::MAX_UNSIGNED_INTEGER,
            'chapter_end' => 'required|integer|min:1|max:'.self::MAX_UNSIGNED_INTEGER,
        ], [
            'cerita_ids.required_if' => 'Pilih minimal satu judul atau aktifkan pilih semua judul.',
            'chapter_start.max' => 'Maaf, angka chapter awal terlalu besar.',
            'chapter_end.max' => 'Maaf, angka chapter akhir terlalu besar.',
        ]);

        $start = min((int) $data['chapter_start'], (int) $data['chapter_end']);
        $end = max((int) $data['chapter_start'], (int) $data['chapter_end']);
        $targetLockState = $data['lock_action'] === 'lock';

        $query = Cerita::query()
            ->select(['id', 'parts', 'lock']);

        if ($data['lock_scope'] === 'selected') {
            $query->whereIn('id', $data['cerita_ids']);
        }

        $updatedStories = 0;
        $updatedChapters = 0;

        $query->chunkById(100, function ($ceritas) use ($start, $end, $targetLockState, &$updatedStories, &$updatedChapters) {
            foreach ($ceritas as $cerita) {
                $chapterTotal = max((int) $cerita->parts, 0);

                if ($chapterTotal < 1) {
                    continue;
                }

                $lock = $cerita->lock ?? [];
                $storyChanged = false;

                for ($chapter = $start; $chapter <= min($end, $chapterTotal); $chapter++) {
                    $key = 'chapter '.$chapter;

                    if (($lock[$key] ?? false) === $targetLockState) {
                        continue;
                    }

                    $lock[$key] = $targetLockState;
                    $storyChanged = true;
                    $updatedChapters++;
                }

                if (! $storyChanged) {
                    continue;
                }

                $cerita->lock = $lock;
                $cerita->save();
                $updatedStories++;
            }
        });

        $actionLabel = $targetLockState ? 'Locked' : 'Unlocked';

        return redirect()
            ->route('cerita.index')
            ->with('success', "{$actionLabel} global berhasil diterapkan ke {$updatedStories} cerita ({$updatedChapters} chapter).");
    }

    public function bulkPilihan(Request $request)
    {
        $data = $request->validate([
            'form_type' => 'nullable|string',
            'bulk_scope' => 'required|in:all,selected',
            'bulk_fields' => 'required|array|min:1',
            'bulk_fields.*' => 'in:recomendation,wajib_dibaca',
            'bulk_action' => 'required|in:enable,disable',
            'cerita_ids' => 'required_if:bulk_scope,selected|array',
            'cerita_ids.*' => 'integer|exists:mst_cerita,id',
        ], [
            'bulk_fields.required' => 'Pilih minimal satu flag cerita.',
            'cerita_ids.required_if' => 'Pilih minimal satu judul atau aktifkan pilih semua judul.',
        ]);

        $query = Cerita::query();

        if ($data['bulk_scope'] === 'selected') {
            $query->whereIn('id', $data['cerita_ids']);
        }

        $updateData = collect($data['bulk_fields'])
            ->mapWithKeys(fn ($field) => [$field => $data['bulk_action'] === 'enable'])
            ->all();

        $updatedStories = $query->update($updateData);

        $fieldLabel = collect($data['bulk_fields'])
            ->map(fn ($field) => $field === 'recomendation' ? 'Rekomendasi' : 'Wajib Dibaca')
            ->join(' dan ');
        $actionLabel = $data['bulk_action'] === 'enable' ? 'diaktifkan' : 'dinonaktifkan';

        return redirect()
            ->route('cerita.index')
            ->with('success', "{$fieldLabel} berhasil {$actionLabel} untuk {$updatedStories} cerita.");
    }

    private function syncAdPlacements(Cerita $cerita, Request $request, int $chapterTotal): void
    {
        $globalPlacements = $cerita->adPlacements()
            ->get(['ad_id', 'after_chapter', 'placement_position', 'is_global'])
            ->groupBy(function ($placement) {
                $position = $placement->placement_position ?: 'after';

                return $position.':'.$placement->after_chapter.':'.$placement->ad_id;
            })
            ->map(fn ($items) => $items->contains(fn ($placement) => (bool) $placement->is_global));

        $cerita->adPlacements()->delete();

        $placements = [];
        $now = now();
        $sortOrder = 0;

        if (is_array($request->input('ad_placements'))) {
            foreach ($request->input('ad_placements', []) as $placement) {
                if (! is_array($placement)) {
                    continue;
                }

                $adId = (int) ($placement['ad_id'] ?? 0);
                $chapterNumber = (int) ($placement['chapter'] ?? 0);
                $position = $placement['position'] ?? 'after';

                if (! $adId || ! in_array($position, ['before', 'after'], true) || $chapterNumber < 1 || $chapterNumber > $chapterTotal) {
                    continue;
                }

                $globalKey = $position.':'.$chapterNumber.':'.$adId;

                $placements[] = [
                    'cerita_id' => $cerita->id,
                    'ad_id' => $adId,
                    'after_chapter' => $chapterNumber,
                    'placement_position' => $position,
                    'is_global' => (bool) ($globalPlacements[$globalKey] ?? false),
                    'sort_order' => ++$sortOrder,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            if ($placements) {
                CeritaAd::insert($placements);
            }

            return;
        }

        foreach ([
            'before' => 'ads_before_chapters',
            'after' => 'ads_after_chapters',
        ] as $position => $inputName) {
            foreach ($request->input($inputName, []) as $chapter => $adIds) {
                $chapterNumber = (int) $chapter;

                if ($chapterNumber < 1 || $chapterNumber > $chapterTotal || ! is_array($adIds)) {
                    continue;
                }

                foreach (array_unique($adIds) as $adId) {
                    $adId = (int) $adId;
                    $globalKey = $position.':'.$chapterNumber.':'.$adId;

                    $placements[] = [
                        'cerita_id' => $cerita->id,
                        'ad_id' => $adId,
                        'after_chapter' => $chapterNumber,
                        'placement_position' => $position,
                        'is_global' => (bool) ($globalPlacements[$globalKey] ?? false),
                        'sort_order' => ++$sortOrder,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
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
        $content = $this->removeDecorativeDashes($content);

        if ($content !== strip_tags($content)) {
            return $this->sanitizeChapterHtml($content);
        }

        return trim($content);
    }

    private function normalizeChapterTitle(?string $title): string
    {
        $title = str_replace("\xc2\xa0", ' ', $title ?? '');
        $title = $this->removeDecorativeDashes($title);
        $title = preg_replace('/\s+/u', ' ', $title) ?? $title;

        return trim($title);
    }

    private function removeDecorativeDashes(string $value): string
    {
        $value = preg_replace('/[‐‑‒–—―]+/u', ' ', $value) ?? $value;

        return preg_replace('/-{2,}/u', ' ', $value) ?? $value;
    }

    private function sanitizeChapterHtml(string $content): string
    {
        $allowedTags = '<p><br><strong><b><em><i><u><blockquote><ul><ol><li><h3><h4><pre><code>';
        $content = strip_tags($content, $allowedTags);
        $content = preg_replace('/<([a-z][a-z0-9]*)\b[^>]*>/i', '<$1>', $content) ?? $content;
        $content = preg_replace('/<p>\s*<\/p>/i', '', $content) ?? $content;

        return trim($content);
    }

    private function selectedCeritaOptions(mixed $ids)
    {
        $ids = collect((array) $ids)
            ->filter(fn ($id) => is_numeric($id))
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        if ($ids->isEmpty()) {
            return collect();
        }

        return Cerita::query()
            ->select(['id', 'judul', 'parts'])
            ->whereIn('id', $ids)
            ->orderBy('judul')
            ->get();
    }

    private function initialCeritaOptions(array $excludedIds)
    {
        return Cerita::query()
            ->select(['id', 'judul', 'parts'])
            ->when($excludedIds !== [], fn ($query) => $query->whereNotIn('id', $excludedIds))
            ->orderBy('judul')
            ->limit(5)
            ->get();
    }
}
